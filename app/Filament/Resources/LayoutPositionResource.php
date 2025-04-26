<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LayoutPositionResource\Pages;
use App\Filament\Resources\LayoutPositionResource\RelationManagers;
use App\Models\LayoutPosition;
use App\Models\MultiviewLayout;
use App\Models\InputStream;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LayoutPositionResource extends Resource
{
    protected static ?string $model = LayoutPosition::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('multiview_layout_id')
                    ->label('Multiview Layout')
                    ->options(MultiviewLayout::pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('input_stream_id')
                    ->label('Input Stream')
                    ->options(InputStream::pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('position_x')
                            ->label('X Position')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('position_y')
                            ->label('Y Position')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ]),
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('width')
                            ->required()
                            ->numeric()
                            ->default(320),
                        Forms\Components\TextInput::make('height')
                            ->required()
                            ->numeric()
                            ->default(180),
                    ]),
                Forms\Components\TextInput::make('z_index')
                    ->label('Z-Index')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('show_label')
                    ->label('Show Label')
                    ->default(true),
                Forms\Components\Select::make('label_position')
                    ->label('Label Position')
                    ->options(LayoutPosition::getLabelPositions())
                    ->default('bottom')
                    ->visible(fn (Forms\Get $get) => $get('show_label')),
                Forms\Components\KeyValue::make('overlay_options')
                    ->label('Overlay Options')
                    ->keyLabel('Option')
                    ->valueLabel('Value')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('multiviewLayout.name')
                    ->label('Layout')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('inputStream.name')
                    ->label('Stream')
                    ->searchable()
                    ->sortable()
                    ->placeholder('None'),
                Tables\Columns\TextColumn::make('position_x')
                    ->label('X')
                    ->sortable(),
                Tables\Columns\TextColumn::make('position_y')
                    ->label('Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('width')
                    ->sortable(),
                Tables\Columns\TextColumn::make('height')
                    ->sortable(),
                Tables\Columns\IconColumn::make('show_label')
                    ->boolean(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListLayoutPositions::route('/'),
            'create' => Pages\CreateLayoutPosition::route('/create'),
            'edit' => Pages\EditLayoutPosition::route('/{record}/edit'),
        ];
    }
}
