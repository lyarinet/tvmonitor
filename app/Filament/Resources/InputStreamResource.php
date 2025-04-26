<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InputStreamResource\Pages;
use App\Jobs\GenerateThumbnails;
use App\Jobs\MonitorStreamHealth;
use App\Models\InputStream;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Infolists;

class InputStreamResource extends Resource
{
    protected static ?string $model = InputStream::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-on-square';

    protected static ?string $navigationGroup = 'Streaming';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stream Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('protocol')
                            ->options(InputStream::getProtocols())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Components\Select $component) {
                                $container = $component->getContainer();
                                if ($container) {
                                    $advancedOptions = $container->getComponent('advancedUdpOptions');
                                    if ($advancedOptions) {
                                        $childContainer = $advancedOptions->getChildComponentContainer();
                                        if ($childContainer) {
                                            $childContainer->fill();
                                        }
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('url')
                            ->required()
                            ->maxLength(255)
                            ->placeholder(function (Forms\Get $get) {
                                return match ($get('protocol')) {
                                    'udp' => 'udp://@239.17.17.81:1234?localaddr=192.168.212.252',
                                    'rtsp' => 'rtsp://example.com/stream1',
                                    'hls' => 'https://example.com/playlist.m3u8',
                                    'dash' => 'https://example.com/manifest.mpd',
                                    'http' => 'http://example.com/video.mp4',
                                    default => 'rtmp://example.com/live/stream1',
                                };
                            }),
                        Forms\Components\TextInput::make('username')
                            ->maxLength(255)
                            ->helperText('Required only if the stream requires authentication')
                            ->visible(function (Forms\Get $get) {
                                // Username/password are typically used with RTSP, RTMP, and some HTTP streams
                                return in_array($get('protocol'), ['rtsp', 'rtmp', 'http', 'hls', 'dash']);
                            }),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->maxLength(255)
                            ->dehydrated(fn ($state) => filled($state))
                            ->helperText('Required only if the stream requires authentication')
                            ->visible(function (Forms\Get $get) {
                                // Username/password are typically used with RTSP, RTMP, and some HTTP streams
                                return in_array($get('protocol'), ['rtsp', 'rtmp', 'http', 'hls', 'dash']);
                            }),
                        Forms\Components\Select::make('status')
                            ->options(InputStream::getStatuses())
                            ->default('inactive')
                            ->required(),
                    ]),
                Forms\Components\Section::make('Advanced UDP Options')
                    ->schema([
                        Forms\Components\Tabs::make('udp_options_tabs')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('Options')
                                    ->schema([
                                        Forms\Components\TextInput::make('local_address')
                                            ->label('Local Address')
                                            ->placeholder('192.168.212.252')
                                            ->helperText('Specify the local interface IP address to bind to')
                                            ->maxLength(255),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('program_id')
                                                    ->label('Program ID')
                                                    ->placeholder('2017')
                                                    ->helperText('Specify the program ID to map (e.g., 0:p:2017)')
                                                    ->maxLength(255),
                                                Forms\Components\Actions::make([
                                                    Forms\Components\Actions\Action::make('scanPrograms')
                                                        ->label('Scan Programs')
                                                        ->icon('heroicon-m-magnifying-glass')
                                                        ->color('primary')
                                                        ->requiresConfirmation()
                                                        ->modalHeading('Scan for Programs')
                                                        ->modalDescription('This will scan the UDP stream for available programs. The scan may take a few moments to complete.')
                                                        ->modalSubmitActionLabel('Scan Now')
                                                        ->action(function (Forms\Get $get, Forms\Set $set, $livewire) {
                                                            $url = $get('url');
                                                            $localAddress = $get('local_address');
                                                            
                                                            if (empty($url)) {
                                                                Notification::make()
                                                                    ->title('Error')
                                                                    ->body('Stream URL is required to scan for programs')
                                                                    ->danger()
                                                                    ->send();
                                                                return;
                                                            }
                                                            
                                                            // Show loading notification
                                                            Notification::make()
                                                                ->title('Scanning')
                                                                ->body('Scanning for programs. This may take up to 60 seconds...')
                                                                ->info()
                                                                ->send();
                                                                
                                                            try {
                                                                // Use the FFmpegService to scan for programs
                                                                $ffmpegService = app(\App\Services\FFmpeg\FFmpegService::class);
                                                                $result = $ffmpegService->scanUdpPrograms($url, $localAddress);
                                                                
                                                                // Check for errors
                                                                if (isset($result['error'])) {
                                                                    Notification::make()
                                                                        ->title('Error')
                                                                        ->body($result['error'])
                                                                        ->danger()
                                                                        ->send();
                                                                    return;
                                                                }
                                                                
                                                                if (empty($result['programs'])) {
                                                                    Notification::make()
                                                                        ->title('No Programs Found')
                                                                        ->body('No programs were found in this UDP stream')
                                                                        ->warning()
                                                                        ->send();
                                                                    return;
                                                                }
                                                                
                                                                // Format program information for display
                                                                $programList = [];
                                                                $firstProgramId = null;
                                                                
                                                                foreach ($result['programs'] as $program) {
                                                                    $id = $program['program_id'];
                                                                    if ($firstProgramId === null) {
                                                                        $firstProgramId = $id;
                                                                    }
                                                                    
                                                                    $numStreams = $program['num_streams'];
                                                                    $programName = $program['program_name'] ?? null;
                                                                    
                                                                    // Add stream type counts
                                                                    $streamTypes = [];
                                                                    $videoCount = 0;
                                                                    $audioCount = 0;
                                                                    $dataCount = 0;
                                                                    
                                                                    if (!empty($program['streams'])) {
                                                                        foreach ($program['streams'] as $stream) {
                                                                            switch ($stream['type']) {
                                                                                case 'video':
                                                                                    $videoCount++;
                                                                                    break;
                                                                                case 'audio':
                                                                                    $audioCount++;
                                                                                    break;
                                                                                default:
                                                                                    $dataCount++;
                                                                                    break;
                                                                            }
                                                                        }
                                                                    }
                                                                    
                                                                    if ($videoCount > 0) {
                                                                        $streamTypes[] = "{$videoCount} video";
                                                                    }
                                                                    if ($audioCount > 0) {
                                                                        $streamTypes[] = "{$audioCount} audio";
                                                                    }
                                                                    if ($dataCount > 0) {
                                                                        $streamTypes[] = "{$dataCount} data";
                                                                    }
                                                                    
                                                                    $streamTypesStr = !empty($streamTypes) 
                                                                        ? ' (' . implode(', ', $streamTypes) . ')'
                                                                        : '';
                                                                    
                                                                    // Format the program description
                                                                    $description = "Program ID: {$id}";
                                                                    if (!empty($programName)) {
                                                                        $description = "{$programName} - " . $description;
                                                                    }
                                                                    $description .= $streamTypesStr;
                                                                    
                                                                    $entry = [
                                                                        'id' => $id,
                                                                        'description' => $description,
                                                                        'streamInfo' => $streamTypesStr,
                                                                        'name' => $programName
                                                                    ];
                                                                    
                                                                    $programList[] = $entry;
                                                                }
                                                                
                                                                // Store the programs list in the form state
                                                                $set('program_list', json_encode($programList));
                                                                
                                                                // For debugging
                                                                \Illuminate\Support\Facades\Log::info('Programs found: ', ['count' => count($programList), 'programs' => $programList]);
                                                                
                                                                // Switch to the Programs tab
                                                                $set('udp_options_tabs', 1);
                                                                
                                                                // Directly set the program ID if it's available
                                                                if ($firstProgramId !== null) {
                                                                    $set('program_id', $firstProgramId);
                                                                }
                                                                
                                                                // Show success notification
                                                                Notification::make()
                                                                    ->title('Programs Found')
                                                                    ->body(count($programList) . ' programs found! Check the Programs tab.')
                                                                    ->success()
                                                                    ->send();
                                                                
                                                                // Log the full program list for debugging
                                                                \Illuminate\Support\Facades\Log::debug('Full program list JSON: ' . json_encode($programList));
                                                                
                                                            } catch (\Exception $e) {
                                                                Notification::make()
                                                                    ->title('Error')
                                                                    ->body('Failed to scan for programs: ' . $e->getMessage())
                                                                    ->danger()
                                                                    ->send();
                                                            }
                                                        })
                                                ])
                                                ->alignRight(),
                                            ]),
                                        Forms\Components\Toggle::make('ignore_unknown')
                                            ->label('Ignore Unknown')
                                            ->helperText('Add -ignore_unknown option to FFmpeg command'),
                                        Forms\Components\Toggle::make('map_disable_data')
                                            ->label('Disable Data Streams')
                                            ->helperText('Add -map -d option to FFmpeg command'),
                                        Forms\Components\Toggle::make('map_disable_subtitles')
                                            ->label('Disable Subtitles')
                                            ->helperText('Add -map -s option to FFmpeg command'),
                                        Forms\Components\KeyValue::make('additional_options')
                                            ->label('Additional FFmpeg Options')
                                            ->keyLabel('Option')
                                            ->valueLabel('Value')
                                            ->helperText('Add any additional FFmpeg options as key-value pairs')
                                            ->addable()
                                            ->reorderable(),
                                    ]),
                                
                                Forms\Components\Tabs\Tab::make('Programs')
                                    ->schema([
                                        Forms\Components\Placeholder::make('programs_list_message')
                                            ->content('Scan for programs in the Options tab to see available programs here.')
                                            ->visible(fn (Forms\Get $get): bool => empty($get('program_list'))),
                                            
                                        Forms\Components\Hidden::make('program_list'),
                                        
                                        Forms\Components\Section::make('Found Programs')
                                            ->schema(function (Forms\Get $get) {
                                                $programList = $get('program_list');
                                                
                                                // Debug logging
                                                \Illuminate\Support\Facades\Log::debug('Program list from form state: ' . $programList);
                                                
                                                if (empty($programList)) {
                                                    return [
                                                        Forms\Components\Placeholder::make('no_programs')
                                                            ->content('No programs found yet. Use the "Scan Programs" button in the Options tab.')
                                                    ];
                                                }
                                                
                                                if (is_string($programList)) {
                                                    $programList = json_decode($programList, true);
                                                    // Debug logging
                                                    \Illuminate\Support\Facades\Log::debug('Decoded program list: ' . print_r($programList, true));
                                                }
                                                
                                                if (empty($programList)) {
                                                    return [
                                                        Forms\Components\Placeholder::make('no_programs')
                                                            ->content('No programs found yet. Use the "Scan Programs" button in the Options tab.')
                                                    ];
                                                }
                                                
                                                $components = [];
                                                $components[] = Forms\Components\Placeholder::make('programs_count')
                                                    ->content('Found ' . count($programList) . ' programs')
                                                    ->extraAttributes(['class' => 'text-sm text-gray-500 mb-4']);
                                                
                                                foreach ($programList as $index => $program) {
                                                    $components[] = Forms\Components\Card::make()
                                                        ->schema([
                                                            Forms\Components\Grid::make(12)
                                                                ->schema([
                                                                    Forms\Components\Grid::make(1)
                                                                        ->schema([
                                                                            Forms\Components\Placeholder::make("program_name_{$index}")
                                                                                ->content(function () use ($program) {
                                                                                    if (!empty($program['name'])) {
                                                                                        return new HtmlString(
                                                                                            '<span class="text-lg font-bold text-primary-600">' . 
                                                                                            htmlspecialchars($program['name']) . 
                                                                                            '</span>'
                                                                                        );
                                                                                    }
                                                                                    return '';
                                                                                })
                                                                                ->visible(!empty($program['name']))
                                                                                ->columnSpan(12),
                                                                            Forms\Components\Placeholder::make("program_display_{$index}")
                                                                                ->content($program['description'])
                                                                                ->columnSpan(12),
                                                                        ])
                                                                        ->columnSpan(8),
                                                                    Forms\Components\Actions::make([
                                                                        Forms\Components\Actions\Action::make("use_program_{$index}")
                                                                            ->label('Use This Program')
                                                                            ->icon('heroicon-m-check-circle')
                                                                            ->color('success')
                                                                            ->size('sm')
                                                                            ->action(function (Forms\Set $set) use ($program) {
                                                                                $programId = $program['id'];
                                                                                $set('program_id', $programId);
                                                                                
                                                                                $name = !empty($program['name']) 
                                                                                    ? $program['name'] . ' (ID: ' . $programId . ')'
                                                                                    : 'Program ID ' . $programId;
                                                                                
                                                                                Notification::make()
                                                                                    ->title('Program Set')
                                                                                    ->body("{$name} has been set")
                                                                                    ->success()
                                                                                    ->send();
                                                                            }),
                                                                    ])->alignment(\Filament\Support\Enums\Alignment::End)
                                                                    ->columnSpan(4),
                                                                ]),
                                                        ])
                                                        ->extraAttributes(['class' => 'mb-2']);
                                                }
                                                
                                                return $components;
                                            })
                                            ->collapsible(false),
                                    ]),
                            ])
                            ->activeTab(0)
                            ->persistTabInQueryString()
                    ])
                    ->visible(fn (Forms\Get $get) => $get('protocol') === 'udp')
                    ->key('advancedUdpOptions')
                    ->collapsible(),
                Forms\Components\Section::make('Stream Metadata')
                    ->schema([
                        Forms\Components\Placeholder::make('metadata_display')
                            ->content(function ($record) {
                                if (!$record || !$record->metadata) {
                                    return 'No metadata available';
                                }
                                
                                $html = '<div class="space-y-2">';
                                
                                if (isset($record->metadata['health'])) {
                                    $health = $record->metadata['health'];
                                    $html .= '<div class="font-medium">Health Information:</div>';
                                    $html .= '<div class="pl-4">';
                                    
                                    if (isset($health['width']) && isset($health['height'])) {
                                        $html .= "<div>Resolution: {$health['width']}x{$health['height']}</div>";
                                    }
                                    
                                    if (isset($health['codec'])) {
                                        $html .= "<div>Codec: {$health['codec']}</div>";
                                    }
                                    
                                    if (isset($health['bitrate'])) {
                                        $bitrate = number_format($health['bitrate'] / 1000) . ' Kbps';
                                        $html .= "<div>Bitrate: {$bitrate}</div>";
                                    }
                                    
                                    if (isset($health['checked_at'])) {
                                        $html .= "<div>Last Checked: {$health['checked_at']}</div>";
                                    }
                                    
                                    $html .= '</div>';
                                }
                                
                                if (isset($record->metadata['thumbnail'])) {
                                    $thumbnailUrl = asset('storage/' . $record->metadata['thumbnail']);
                                    $html .= '<div class="font-medium mt-4">Thumbnail:</div>';
                                    $html .= "<div class='pl-4'><img src='{$thumbnailUrl}' class='max-w-xs rounded border' /></div>";
                                }
                                
                                $html .= '</div>';
                                
                                return new HtmlString($html);
                            }),
                    ])
                    ->visible(fn ($record) => $record !== null),
                Forms\Components\Section::make('Error Log')
                    ->schema([
                        Forms\Components\Placeholder::make('error_log_display')
                            ->content(function ($record) {
                                if (!$record || !$record->error_log) {
                                    return 'No errors logged';
                                }
                                
                                $html = '<div class="space-y-4 text-red-600">';
                                
                                foreach ($record->error_log as $index => $error) {
                                    if (is_array($error) && isset($error['timestamp']) && isset($error['message'])) {
                                        $html .= "<div class='border-b pb-4 mb-4'>";
                                        $html .= "<div class='flex justify-between items-start'>";
                                        $html .= "<div class='text-xs text-gray-500'>{$error['timestamp']}</div>";
                                        
                                        // Add a button to clear this entry
                                        $html .= "<button 
                                                    type='button' 
                                                    class='text-xs text-gray-500 hover:text-red-500 transition-colors duration-200'
                                                    onclick='clearErrorLogEntry({$record->id}, {$index})'
                                                    title='Clear this error log entry'
                                                >
                                                    <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16' />
                                                    </svg>
                                                </button>";
                                        $html .= "</div>";
                                        
                                        $html .= "<div class='font-medium'>{$error['message']}</div>";
                                        
                                        // Display the ffprobe command if available
                                        if (isset($error['command'])) {
                                            $html .= "<div class='mt-2'>";
                                            $html .= "<div class='text-xs text-gray-500 font-medium'>FFprobe Command:</div>";
                                            $html .= "<div class='bg-gray-100 p-2 rounded text-gray-800 text-xs font-mono overflow-x-auto'>";
                                            $html .= htmlspecialchars($error['command']);
                                            $html .= "</div>";
                                            $html .= "</div>";
                                        }
                                        
                                        // Display the ffmpeg command if available
                                        if (isset($error['ffmpeg_command'])) {
                                            $html .= "<div class='mt-2'>";
                                            $html .= "<div class='text-xs text-gray-500 font-medium'>FFmpeg Equivalent Command:</div>";
                                            $html .= "<div class='bg-gray-100 p-2 rounded text-gray-800 text-xs font-mono overflow-x-auto'>";
                                            $html .= htmlspecialchars($error['ffmpeg_command']);
                                            $html .= "</div>";
                                            $html .= "</div>";
                                        }
                                        
                                        $html .= "</div>";
                                    }
                                }
                                
                                // Add JavaScript to handle clearing individual error log entries
                                $html .= "
                                <script>
                                    function clearErrorLogEntry(streamId, index) {
                                        if (confirm('Are you sure you want to clear this error log entry?')) {
                                            // Get the CSRF token from the meta tag
                                            const csrfToken = document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content');
                                            
                                            // Send AJAX request to clear the error log entry
                                            fetch('/admin/clear-error-log-entry', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': csrfToken
                                                },
                                                body: JSON.stringify({
                                                    stream_id: streamId,
                                                    index: index
                                                })
                                            })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {
                                                    // Reload the page to show the updated error logs
                                                    window.location.reload();
                                                } else {
                                                    alert('Failed to clear error log entry: ' + data.message);
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error:', error);
                                                alert('An error occurred while clearing the error log entry');
                                            });
                                        }
                                    }
                                </script>";
                                
                                $html .= '</div>';
                                
                                return new HtmlString($html);
                            }),
                    ])
                    ->visible(fn ($record) => $record !== null && !empty($record->error_log)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('protocol')
                    ->badge(),
                Tables\Columns\TextColumn::make('url')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'error' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(InputStream::getStatuses()),
                Tables\Filters\SelectFilter::make('protocol')
                    ->options(InputStream::getProtocols()),
            ])
            ->actions([
                Tables\Actions\Action::make('copy_to_list')
                    ->label('Copy to List')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Copy Stream to List')
                    ->modalDescription('This will create a copy of this stream with "(Copy)" added to the name.')
                    ->modalSubmitActionLabel('Copy')
                    ->action(function (InputStream $record) {
                        $newStream = $record->replicate();
                        $newStream->name = $record->name . ' (Copy)';
                        $newStream->save();
                        
                        Notification::make()
                            ->title('Stream Copied')
                            ->body('Stream has been duplicated successfully')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('check_health')
                    ->label('Check Health')
                    ->icon('heroicon-o-heart')
                    ->color('warning')
                    ->action(function (InputStream $record) {
                        MonitorStreamHealth::dispatch($record);
                        
                        return redirect()->back()
                            ->with('success', 'Stream health check initiated');
                    }),
                Tables\Actions\Action::make('generate_thumbnail')
                    ->label('Generate Thumbnail')
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->action(function (InputStream $record) {
                        GenerateThumbnails::dispatch($record);
                        
                        Notification::make()
                            ->title('Thumbnail Generation Started')
                            ->body('The thumbnail generation process has been started. This may take a few moments.')
                            ->info()
                            ->send();
                        
                        // Give it a couple seconds to generate before refreshing
                        sleep(2);
                        
                        // Check if the thumbnail exists
                        $metadata = $record->metadata ?? [];
                        $thumbnailExists = isset($metadata['thumbnail']) && 
                                           file_exists(storage_path('app/public/' . $metadata['thumbnail']));
                        
                        if ($thumbnailExists) {
                            Notification::make()
                                ->title('Thumbnail Generated')
                                ->body('The thumbnail has been successfully generated.')
                                ->success()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInputStreams::route('/'),
            'create' => Pages\CreateInputStream::route('/create'),
            'edit' => Pages\EditInputStream::route('/{record}/edit'),
        ];
    }
}
