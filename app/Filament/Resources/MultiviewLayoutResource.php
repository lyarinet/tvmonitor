<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MultiviewLayoutResource\Pages;
use App\Filament\Resources\MultiviewLayoutResource\RelationManagers;
use App\Models\MultiviewLayout;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class MultiviewLayoutResource extends Resource
{
    protected static ?string $model = MultiviewLayout::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Streaming';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Layout Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('rows')
                                    ->required()
                                    ->numeric()
                                    ->default(2)
                                    ->minValue(1)
                                    ->maxValue(10),
                                Forms\Components\TextInput::make('columns')
                                    ->required()
                                    ->numeric()
                                    ->default(2)
                                    ->minValue(1)
                                    ->maxValue(10),
                            ]),
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('width')
                                    ->required()
                                    ->numeric()
                                    ->default(1920)
                                    ->minValue(640)
                                    ->maxValue(3840),
                                Forms\Components\TextInput::make('height')
                                    ->required()
                                    ->numeric()
                                    ->default(1080)
                                    ->minValue(480)
                                    ->maxValue(2160),
                            ]),
                        Forms\Components\ColorPicker::make('background_color')
                            ->required()
                            ->default('#000000'),
                        Forms\Components\Select::make('status')
                            ->options(MultiviewLayout::getStatuses())
                            ->default('inactive')
                            ->required(),
                    ]),
                Forms\Components\Section::make('Layout Preview')
                    ->schema([
                        Forms\Components\Placeholder::make('layout_preview')
                            ->content(function ($record) {
                                if (!$record) {
                                    return 'Save the layout to see a preview';
                                }
                                
                                $html = "<div class='relative border border-gray-300 rounded' style='width: 100%; height: 0; padding-bottom: 56.25%; background-color: {$record->background_color};'>";
                                
                                foreach ($record->layoutPositions as $position) {
                                    $posX = ($position->position_x / $record->width) * 100;
                                    $posY = ($position->position_y / $record->height) * 100;
                                    $width = ($position->width / $record->width) * 100;
                                    $height = ($position->height / $record->height) * 100;
                                    
                                    $streamName = $position->inputStream ? $position->inputStream->name : 'No stream';
                                    
                                    $html .= "<div class='absolute border border-white' style='left: {$posX}%; top: {$posY}%; width: {$width}%; height: {$height}%; z-index: {$position->z_index};'>";
                                    
                                    if ($position->show_label) {
                                        $labelClass = 'absolute text-white text-xs bg-black bg-opacity-50 px-2 py-1';
                                        $labelStyle = '';
                                        
                                        switch ($position->label_position) {
                                            case 'top':
                                                $labelStyle = 'top: 0; left: 0; right: 0; text-align: center;';
                                                break;
                                            case 'bottom':
                                                $labelStyle = 'bottom: 0; left: 0; right: 0; text-align: center;';
                                                break;
                                            case 'left':
                                                $labelStyle = 'left: 0; top: 50%; transform: translateY(-50%);';
                                                break;
                                            case 'right':
                                                $labelStyle = 'right: 0; top: 50%; transform: translateY(-50%);';
                                                break;
                                        }
                                        
                                        $html .= "<div class='{$labelClass}' style='{$labelStyle}'>{$streamName}</div>";
                                    }
                                    
                                    $html .= "</div>";
                                }
                                
                                $html .= "</div>";
                                
                                return new HtmlString($html);
                            }),
                    ])
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rows')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('columns')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('resolution')
                    ->label('Resolution')
                    ->getStateUsing(fn ($record) => "{$record->width}x{$record->height}"),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
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
                    ->options(MultiviewLayout::getStatuses()),
            ])
            ->actions([
                Tables\Actions\Action::make('generate_grid')
                    ->label('Generate Grid')
                    ->icon('heroicon-o-squares-2x2')
                    ->color('success')
                    ->action(function (MultiviewLayout $record) {
                        $record->generateGridLayout();
                        
                        return redirect()->back()
                            ->with('success', 'Grid layout generated successfully');
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('manage_positions')
                    ->label('Manage Positions')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->url(fn (MultiviewLayout $record) => route('filament.admin.resources.layout-positions.index', ['multiview_layout_id' => $record->id])),
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
            RelationManagers\LayoutPositionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMultiviewLayouts::route('/'),
            'create' => Pages\CreateMultiviewLayout::route('/create'),
            'edit' => Pages\EditMultiviewLayout::route('/{record}/edit'),
        ];
    }
}
