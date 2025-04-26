<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutputStreamResource\Pages;
use App\Jobs\MonitorStreamHealth;
use App\Jobs\ProcessMultiviewStream;
use App\Models\OutputStream;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class OutputStreamResource extends Resource
{
    protected static ?string $model = OutputStream::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-on-square';

    protected static ?string $navigationGroup = 'Streaming';

    protected static ?int $navigationSort = 30;

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
                            ->options(OutputStream::getProtocols())
                            ->required(),
                        Forms\Components\TextInput::make('url')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('rtmp://example.com/live/stream1')
                            ->helperText('Use {storage_path} as a placeholder for the storage path. Example: {storage_path}/streams/{id}/output.m3u8'),
                        Forms\Components\Select::make('multiview_layout_id')
                            ->relationship('multiviewLayout', 'name')
                            ->required()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->options(OutputStream::getStatuses())
                            ->default('inactive')
                            ->required(),
                    ]),
                Forms\Components\Section::make('FFmpeg Options')
                    ->schema([
                        Forms\Components\KeyValue::make('ffmpeg_options')
                            ->keyLabel('Option')
                            ->valueLabel('Value')
                            ->addable()
                            ->deletable()
                            ->reorderable(),
                    ]),
                Forms\Components\Section::make('Stream Status')
                    ->schema([
                        Forms\Components\Placeholder::make('status_display')
                            ->content(function ($record) {
                                if (!$record || !$record->metadata) {
                                    return 'No status information available';
                                }
                                
                                $html = '<div class="space-y-2">';
                                
                                if (isset($record->metadata['process_id'])) {
                                    $html .= "<div>Process ID: {$record->metadata['process_id']}</div>";
                                }
                                
                                if (isset($record->metadata['is_running'])) {
                                    $isRunning = $record->metadata['is_running'] ? 'Yes' : 'No';
                                    $color = $record->metadata['is_running'] ? 'text-green-600' : 'text-red-600';
                                    $html .= "<div>Running: <span class='{$color}'>{$isRunning}</span></div>";
                                }
                                
                                if (isset($record->metadata['last_checked'])) {
                                    $html .= "<div>Last Checked: {$record->metadata['last_checked']}</div>";
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
                                
                                $html = '<div class="space-y-2 text-red-600">';
                                
                                foreach ($record->error_log as $error) {
                                    if (is_array($error) && isset($error['timestamp']) && isset($error['message'])) {
                                        $html .= "<div class='border-b pb-2'>";
                                        $html .= "<div class='text-xs text-gray-500'>{$error['timestamp']}</div>";
                                        $html .= "<div>{$error['message']}</div>";
                                        $html .= "</div>";
                                    }
                                }
                                
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
                Tables\Columns\TextColumn::make('multiviewLayout.name')
                    ->label('Multiview layout')
                    ->numeric()
                    ->sortable(),
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
                    ->options(OutputStream::getStatuses()),
                Tables\Filters\SelectFilter::make('protocol')
                    ->options(OutputStream::getProtocols()),
                Tables\Filters\SelectFilter::make('multiview_layout_id')
                    ->relationship('multiviewLayout', 'name')
                    ->label('Multiview Layout'),
            ])
            ->actions([
                Tables\Actions\Action::make('ffmpeg_command')
                    ->label('FFmpeg Command')
                    ->icon('heroicon-o-code-bracket')
                    ->color('gray')
                    ->modalContent(function (OutputStream $record): \Illuminate\Support\HtmlString {
                        // Get the FFmpeg service
                        $ffmpegService = app(\App\Services\FFmpeg\FFmpegService::class);
                        
                        try {
                            // Check if this is a single input stream
                            $metadata = $record->metadata;
                            if (!is_array($metadata)) {
                                $metadata = !empty($metadata) ? json_decode($metadata, true) : [];
                                if (json_last_error() !== JSON_ERROR_NONE) {
                                    $metadata = [];
                                }
                            }
                            
                            $isSingleInput = $metadata['single_input'] ?? false;
                            $inputStreamId = $metadata['input_stream_id'] ?? null;
                            $positionIndex = $metadata['position_index'] ?? null;
                            
                            // Generate the command based on the type
                            if ($isSingleInput && $inputStreamId && $positionIndex !== null) {
                                $inputStream = \App\Models\InputStream::find($inputStreamId);
                                if ($inputStream) {
                                    $command = $ffmpegService->generateSingleInputCommand($record->multiviewLayout, $record, $inputStream, $positionIndex);
                                } else {
                                    $command = $ffmpegService->generateMultiviewCommand($record->multiviewLayout, $record);
                                }
                            } else {
                                $command = $ffmpegService->generateMultiviewCommand($record->multiviewLayout, $record);
                            }
                            
                            // Create a unique ID for this modal
                            $modalId = 'ffmpeg-command-' . $record->id;
                            
                            // Format for display and return as HtmlString
                            return new \Illuminate\Support\HtmlString('
                            <div class="space-y-2">
                                <h3 class="text-lg font-medium">FFmpeg Command for ' . htmlspecialchars($record->name) . '</h3>
                                <div class="flex justify-end mb-2">
                                    <button 
                                        type="button" 
                                        class="text-sm text-primary-600 hover:underline flex items-center gap-1"
                                        id="copy-button-' . $modalId . '"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                            <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                                        </svg>
                                        Copy to clipboard
                                    </button>
                                </div>
                                <div class="bg-gray-200 p-4 rounded-lg overflow-auto text-xs font-mono max-h-96 border border-gray-300">
                                    <pre class="text-gray-900" id="command-text-' . $modalId . '">' . htmlspecialchars($command) . '</pre>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">This command can be used to start the stream manually or for debugging.</p>
                                <script>
                                    // Wait for the document to be ready
                                    document.addEventListener("DOMContentLoaded", function() {
                                        // Set up the copy button when the modal opens
                                        setupCopyButton_' . $modalId . '();
                                    });
                                    
                                    // Also try to set it up immediately (if the DOM is already loaded)
                                    setupCopyButton_' . $modalId . '();
                                    
                                    // Function to set up the copy button
                                    function setupCopyButton_' . $modalId . '() {
                                        const copyButton = document.getElementById("copy-button-' . $modalId . '");
                                        const commandText = document.getElementById("command-text-' . $modalId . '");
                                        
                                        if (copyButton && commandText) {
                                            copyButton.addEventListener("click", function() {
                                                navigator.clipboard.writeText(commandText.textContent).then(() => {
                                                    copyButton.innerHTML = `
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        Copied!
                                                    `;
                                                    setTimeout(() => {
                                                        copyButton.innerHTML = `
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                                <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                                                <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                                                            </svg>
                                                            Copy to clipboard
                                                        `;
                                                    }, 2000);
                                                }).catch(err => {
                                                    console.error("Error copying to clipboard: ", err);
                                                });
                                            });
                                        }
                                    }
                                </script>
                            </div>');
                        } catch (\Exception $e) {
                            return new \Illuminate\Support\HtmlString('
                            <div class="text-red-600">
                                <h3 class="text-lg font-medium">Error Generating Command</h3>
                                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                            </div>');
                        }
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Tables\Actions\Action::make('copy_to_list')
                    ->label('Copy to List')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Copy Stream to List')
                    ->modalDescription('This will create a copy of this stream with "(Copy)" added to the name.')
                    ->modalSubmitActionLabel('Copy')
                    ->action(function (OutputStream $record) {
                        $newStream = $record->replicate();
                        $newStream->name = $record->name . ' (Copy)';
                        $newStream->status = 'inactive'; // Always set copied streams to inactive
                        $newStream->save();
                        
                        // Use notification instead of redirect
                        \Filament\Notifications\Notification::make()
                            ->title('Stream Copied')
                            ->body('Stream has been duplicated successfully')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('start_stream')
                    ->label('Single Input Start')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->modalHeading('Single Input Start')
                    ->visible(function (OutputStream $record) {
                        // Only show this button if the stream is inactive and has a layout
                        if ($record->status === 'active' || $record->multiview_layout_id === null) {
                            return false;
                        }
                        
                        // Get all input streams from the layout positions
                        $layout = \App\Models\MultiviewLayout::findOrFail($record->multiview_layout_id);
                        $inputStreams = [];
                        $positions = [];
                        
                        foreach ($layout->layoutPositions as $index => $position) {
                            if ($position->inputStream) {
                                $inputStreams[$position->inputStream->id] = $position->inputStream;
                                $positions[$index] = $position;
                            }
                        }
                        
                        // Only show this button when there are multiple input options
                        return count($inputStreams) > 1 || count($positions) > 1;
                    })
                    ->form(function (OutputStream $record) {
                        // Get all input streams from the layout positions
                        $layout = \App\Models\MultiviewLayout::findOrFail($record->multiview_layout_id);
                        $inputStreamOptions = [];
                        $positionOptions = [];
                        
                        foreach ($layout->layoutPositions as $index => $position) {
                            if ($position->inputStream) {
                                $inputStreamOptions[$position->inputStream->id] = $position->inputStream->name . 
                                    ' (' . $position->inputStream->protocol . ')';
                                
                                $inputName = $position->inputStream->name;
                                $positionOptions[$index] = "Position " . ($index + 1) . " - " . $inputName;
                            }
                        }
                        
                        return [
                            Forms\Components\Select::make('input_stream_id')
                                ->label('Select Input Stream')
                                ->options($inputStreamOptions)
                                ->required(),
                            Forms\Components\Select::make('position_index')
                                ->label('Position in Layout')
                                ->options($positionOptions)
                                ->required(),
                        ];
                    })
                    ->requiresConfirmation()
                    ->action(function (OutputStream $record, array $data) {
                        try {
                            $ffmpegService = app(\App\Services\FFmpeg\FFmpegService::class);
                            $layout = \App\Models\MultiviewLayout::findOrFail($record->multiview_layout_id);
                            
                            // Get the selected options from the form
                            if (empty($data['input_stream_id']) || empty($data['position_index'])) {
                                throw new \Exception("Please select an input stream and position");
                            }
                            $inputStream = \App\Models\InputStream::findOrFail($data['input_stream_id']);
                            $positionIndex = $data['position_index'];
                            
                            // Start the stream with a single input
                            $result = $ffmpegService->startSingleInput($layout, $record, $inputStream, $positionIndex);
                            
                            // Ensure the status is set to active
                            $record->refresh();
                            if ($result) {
                                $record->update(['status' => 'active']);
                                Notification::make()
                                    ->title('Stream Started')
                                    ->body('The stream has been started with a single input')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Stream Start Failed')
                                    ->body('There was an error starting the stream. Check the logs for more information.')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error Starting Stream')
                                ->body('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                            
                            // Log the error for debugging
                            \Illuminate\Support\Facades\Log::error('Error starting stream: ' . $e->getMessage(), [
                                'stream_id' => $record->id,
                                'multiview_layout_id' => $record->multiview_layout_id ?? null,
                                'input_stream_id' => $data['input_stream_id'] ?? null,
                                'position_index' => $data['position_index'] ?? null,
                                'exception' => $e
                            ]);
                        }
                    }),
                Tables\Actions\Action::make('auto_start_stream')
                    ->label('Single Input Start')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(function (OutputStream $record) {
                        // Only show this button if the stream is inactive and has a layout
                        if ($record->status === 'active' || $record->multiview_layout_id === null) {
                            return false;
                        }
                        
                        // Get all input streams from the layout positions
                        $layout = \App\Models\MultiviewLayout::findOrFail($record->multiview_layout_id);
                        $inputStreams = [];
                        $positions = [];
                        
                        foreach ($layout->layoutPositions as $index => $position) {
                            if ($position->inputStream) {
                                $inputStreams[$position->inputStream->id] = $position->inputStream;
                                $positions[$index] = $position;
                            }
                        }
                        
                        // Only show this button when there's exactly one input option
                        return count($inputStreams) === 1 && count($positions) === 1;
                    })
                    ->action(function (OutputStream $record) {
                        try {
                            $ffmpegService = app(\App\Services\FFmpeg\FFmpegService::class);
                            $layout = \App\Models\MultiviewLayout::findOrFail($record->multiview_layout_id);
                            
                            // Get all input streams from the layout positions
                            $inputStreams = [];
                            $positions = [];
                            
                            foreach ($layout->layoutPositions as $index => $position) {
                                if ($position->inputStream) {
                                    $inputStreams[$position->inputStream->id] = $position->inputStream;
                                    $positions[$index] = $position;
                                }
                            }
                            
                            // Verify that there's only one input stream and position
                            if (count($inputStreams) !== 1 || count($positions) !== 1) {
                                throw new \Exception("Multiple input streams available");
                            }
                            
                            // Get the only available input stream and position
                            $inputStream = reset($inputStreams);
                            $positionIndex = array_key_first($positions);
                            
                            // Start the stream with a single input
                            $result = $ffmpegService->startSingleInput($layout, $record, $inputStream, $positionIndex);
                            
                            // Ensure the status is set to active
                            $record->refresh();
                            if ($result) {
                                $record->update(['status' => 'active']);
                                Notification::make()
                                    ->title('Stream Started')
                                    ->body('The stream has been started with a single input')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Stream Start Failed')
                                    ->body('There was an error starting the stream. Check the logs for more information.')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error Starting Stream')
                                ->body('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                            
                            // Log the error for debugging
                            \Illuminate\Support\Facades\Log::error('Error starting stream: ' . $e->getMessage(), [
                                'stream_id' => $record->id,
                                'multiview_layout_id' => $record->multiview_layout_id ?? null,
                                'exception' => $e
                            ]);
                        }
                    }),
                Tables\Actions\Action::make('stop_stream')
                    ->label('Stop Stream')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Stop Stream')
                    ->modalDescription('Are you sure you want to stop this stream? This will terminate the FFmpeg process and clean up any stream files.')
                    ->modalSubmitActionLabel('Stop Stream')
                    ->visible(fn (OutputStream $record): bool => $record->status === 'active')
                    ->action(function (OutputStream $record) {
                        try {
                            // Get the process ID from metadata
                            $metadata = $record->metadata;
                            if (!is_array($metadata)) {
                                $metadata = !empty($metadata) ? json_decode($metadata, true) : [];
                            }
                            
                            $processId = $metadata['process_id'] ?? null;
                            
                            if ($processId) {
                                // First try graceful shutdown
                                exec("kill {$processId} 2>/dev/null || true");
                                sleep(1);
                                
                                // Force kill if still running
                                exec("kill -9 {$processId} 2>/dev/null || true");
                                sleep(1);
                                
                                // Kill any remaining ffmpeg processes for this stream
                                $streamPath = storage_path("app/public/streams/{$record->id}");
                                exec("ps aux | grep ffmpeg | grep '{$streamPath}' | awk '{print \$2}' | xargs kill -9 2>/dev/null || true");
                            }
                            
                            // Clean up stream files for this specific stream
                            $streamPath = storage_path("app/public/streams/{$record->id}");
                            $filesDeleted = 0;
                            if (file_exists($streamPath) && is_dir($streamPath)) {
                                // Stop any ongoing file operations
                                exec("lsof +D {$streamPath} | awk 'NR>1 {print \$2}' | xargs kill -9 2>/dev/null || true");
                                
                                // Delete all files in the directory
                                $files = glob("{$streamPath}/*");
                                foreach ($files as $file) {
                                    if (is_file($file)) {
                                        unlink($file);
                                        $filesDeleted++;
                                    }
                                }
                                
                                // Remove the directory itself
                                rmdir($streamPath);
                            }
                            
                            // Update stream status and clear metadata
                            $record->status = 'inactive';
                            $record->metadata = null;
                            $record->save();
                            
                            // Clear any queued jobs for this stream
                            \Illuminate\Support\Facades\DB::table('jobs')
                                ->where('payload', 'like', '%"id":' . $record->id . '%')
                                ->delete();

                            Notification::make()
                                ->title('Stream Stopped')
                                ->body("Stream {$record->name} has been stopped and {$filesDeleted} files were cleaned up.")
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            // Even if there's an error, try to ensure the stream is marked as inactive
                            try {
                                $record->status = 'inactive';
                                $record->metadata = null;
                                $record->save();
                            } catch (\Exception $e2) {
                                \Illuminate\Support\Facades\Log::error('Error updating stream status: ' . $e2->getMessage());
                            }

                            Notification::make()
                                ->title('Error Stopping Stream')
                                ->body('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();

                            // Log the error
                            \Illuminate\Support\Facades\Log::error('Error stopping stream: ' . $e->getMessage(), [
                                'stream_id' => $record->id,
                                'exception' => $e
                            ]);
                        }
                    }),
                Tables\Actions\Action::make('check_status')
                    ->label('Check Status')
                    ->icon('heroicon-o-heart')
                    ->color('warning')
                    ->action(function (OutputStream $record) {
                        MonitorStreamHealth::dispatch(null, $record);
                        
                        return redirect()->back()
                            ->with('success', 'Stream status check initiated');
                    }),
                Tables\Actions\Action::make('view_url')
                    ->label('View URL')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->modalContent(function (OutputStream $record): \Illuminate\Support\HtmlString {
                        $modalId = 'view-url-' . $record->id;
                        $url = $record->url;
                        
                        // For HLS streams, generate a proper playable URL
                        $playableUrl = $url;
                        if ($record->protocol === 'hls') {
                            // Generate the proxy URL for HLS streams
                            $playableUrl = url("/stream-proxy/{$record->id}/playlist.m3u8");
                        } else if ($record->protocol === 'direct') {
                            // For direct protocol, use the URL as is
                            $playableUrl = $url;
                        }
                        
                        // Generate a proxy URL for external streams by creating a temporary input stream
                        $proxyExternalUrl = '';
                        try {
                            if ($record->protocol === 'direct' && filter_var($url, FILTER_VALIDATE_URL)) {
                                // Check if we already have a temporary input stream for this output
                                $inputStream = \App\Models\InputStream::where('name', 'Temp: ' . $record->name)
                                    ->where('url', $url)
                                    ->first();
                                
                                // If not, create a temporary input stream that will be used to proxy the URL
                                if (!$inputStream) {
                                    $inputStream = new \App\Models\InputStream();
                                    $inputStream->name = 'Temp: ' . $record->name;
                                    $inputStream->url = $url;
                                    $inputStream->protocol = 'hls'; // Assuming HLS for direct URLs ending with m3u8
                                    $inputStream->status = 'active';
                                    $inputStream->save();
                                }
                                
                                // Generate a proxy URL for this input stream
                                $proxyExternalUrl = url("/stream-proxy/{$inputStream->id}/playlist.m3u8");
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error generating proxy URL: ' . $e->getMessage());
                            $proxyExternalUrl = '';
                        }
                        
                        $html = '
                        <div class="space-y-4">
                            <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg">
                                <h3 class="text-lg font-medium mb-2">Output Stream URL</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">This is the URL where your stream is being output:</p>
                                <div class="bg-white dark:bg-gray-700 p-3 rounded border border-gray-300 dark:border-gray-600">
                                    <code class="text-sm break-all" id="url-text-' . $modalId . '">' . htmlspecialchars($playableUrl) . '</code>
                                </div>
                            </div>';
                            
                        // Add proxy URL option if available
                        if (!empty($proxyExternalUrl)) {
                            $html .= '
                            <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg">
                                <h3 class="text-lg font-medium mb-2">Proxy URL</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">You can also access this stream through our local proxy system:</p>
                                <div class="bg-white dark:bg-gray-700 p-3 rounded border border-gray-300 dark:border-gray-600">
                                    <code class="text-sm break-all" id="proxy-url-text-' . $modalId . '">' . htmlspecialchars($proxyExternalUrl) . '</code>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Using the proxy may improve compatibility or help with CORS issues.</p>
                            </div>';
                        }
                        
                        $html .= '
                            <div class="flex justify-end">
                                <button 
                                    type="button" 
                                    id="copy-button-' . $modalId . '"
                                    class="inline-flex items-center justify-center gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset filament-button px-3 py-2 bg-primary-600 text-white shadow hover:bg-primary-500 focus:ring-primary-500"
                                >
                                    <span class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                            <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                                        </svg>
                                        Copy URL
                                    </span>
                                </button>';
                                
                        if (!empty($proxyExternalUrl)) {
                            $html .= '
                                <button 
                                    type="button" 
                                    id="copy-proxy-button-' . $modalId . '"
                                    class="ml-2 inline-flex items-center justify-center gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset filament-button px-3 py-2 bg-gray-600 text-white shadow hover:bg-gray-500 focus:ring-gray-500"
                                >
                                    <span class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                            <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                                        </svg>
                                        Copy Proxy URL
                                    </span>
                                </button>';
                        }
                        
                        $html .= '
                            </div>
                            
                            <script>
                                // Add this code to copy the URL to the clipboard
                                const copyButton = document.getElementById("copy-button-' . $modalId . '");
                                const urlText = document.getElementById("url-text-' . $modalId . '");
                                
                                if (copyButton && urlText) {
                                    copyButton.addEventListener("click", function() {
                                        const textToCopy = urlText.textContent;
                                        navigator.clipboard.writeText(textToCopy).then(() => {
                                            copyButton.innerHTML = `
                                                <span class="flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                    Copied!
                                                </span>
                                            `;
                                            
                                            setTimeout(() => {
                                                copyButton.innerHTML = `
                                                    <span class="flex items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                                            <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                                                        </svg>
                                                        Copy URL
                                                    </span>
                                                `;
                                            }, 2000);
                                        }).catch(err => {
                                            console.error("Error copying to clipboard:", err);
                                            alert("Failed to copy URL: " + err);
                                        });
                                    });
                                }';
                        
                        if (!empty($proxyExternalUrl)) {
                            $html .= '
                                // Add code to copy the proxy URL
                                const copyProxyButton = document.getElementById("copy-proxy-button-' . $modalId . '");
                                const proxyUrlText = document.getElementById("proxy-url-text-' . $modalId . '");
                                
                                if (copyProxyButton && proxyUrlText) {
                                    copyProxyButton.addEventListener("click", function() {
                                        const textToCopy = proxyUrlText.textContent;
                                        navigator.clipboard.writeText(textToCopy).then(() => {
                                            copyProxyButton.innerHTML = `
                                                <span class="flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                    Copied!
                                                </span>
                                            `;
                                            
                                            setTimeout(() => {
                                                copyProxyButton.innerHTML = `
                                                    <span class="flex items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                                            <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                                                        </svg>
                                                        Copy Proxy URL
                                                    </span>
                                                `;
                                            }, 2000);
                                        }).catch(err => {
                                            console.error("Error copying to clipboard:", err);
                                            alert("Failed to copy proxy URL: " + err);
                                        });
                                    });
                                }';
                        }
                        
                        $html .= '
                            </script>
                        </div>';
                        
                        return new \Illuminate\Support\HtmlString($html);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn (OutputStream $record): string => $record->status === 'active' ? 'Deactivate' : 'Activate')
                    ->icon(fn (OutputStream $record): string => $record->status === 'active' ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn (OutputStream $record): string => $record->status === 'active' ? 'gray' : 'success')
                    ->disabled(fn (OutputStream $record): bool => $record->status === 'error')
                    ->action(function (OutputStream $record) {
                        if ($record->status === 'active') {
                            // Deactivate stream
                            $record->status = 'inactive';
                            $record->save();
                            ProcessMultiviewStream::dispatch($record->multiviewLayout, $record, false);
                            
                            // Delete all files in the stream directory
                            $streamPath = storage_path("app/public/streams/{$record->id}");
                            $filesDeleted = 0;
                            if (file_exists($streamPath) && is_dir($streamPath)) {
                                // Get all files in directory
                                $files = glob("{$streamPath}/*");
                                
                                // Delete each file
                                foreach ($files as $file) {
                                    if (is_file($file)) {
                                        unlink($file);
                                        $filesDeleted++;
                                    }
                                }
                            }
                            
                            Notification::make()
                                ->title('Stream Deactivated')
                                ->body("The stream {$record->name} is being shut down. {$filesDeleted} files were removed.")
                                ->info()
                                ->send();
                        } else {
                            // Activate stream
                            $record->status = 'active';
                            $record->save();
                            ProcessMultiviewStream::dispatch($record->multiviewLayout, $record, true);
                            Notification::make()
                                ->title('Stream Activated')
                                ->body("The stream {$record->name} is being activated. This may take a moment.")
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Streams')
                        ->icon('heroicon-o-pause')
                        ->color('gray')
                        ->modalDescription('This will deactivate selected streams and delete all their associated files.')
                        ->action(function (Collection $records) {
                            $count = 0;
                            $filesDeleted = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'active') {
                                    $record->status = 'inactive';
                                    $record->save();
                                    ProcessMultiviewStream::dispatch($record->multiviewLayout, $record, false);
                                    $count++;
                                    
                                    // Delete all files in the stream directory
                                    $streamPath = storage_path("app/public/streams/{$record->id}");
                                    if (file_exists($streamPath) && is_dir($streamPath)) {
                                        // Get all files in directory
                                        $files = glob("{$streamPath}/*");
                                        
                                        // Delete each file
                                        foreach ($files as $file) {
                                            if (is_file($file)) {
                                                unlink($file);
                                                $filesDeleted++;
                                            }
                                        }
                                    }
                                }
                            }
                            
                            Notification::make()
                                ->title("{$count} Streams Deactivated")
                                ->body("The selected streams are being shut down. {$filesDeleted} files were removed.")
                                ->info()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListOutputStreams::route('/'),
            'create' => Pages\CreateOutputStream::route('/create'),
            'edit' => Pages\EditOutputStream::route('/{record}/edit'),
        ];
    }
}
