<?php

namespace App\Filament\Resources\MultiviewLayoutResource\RelationManagers;

use App\Models\InputStream;
use App\Models\LayoutPosition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LayoutPositionsRelationManager extends RelationManager
{
    protected static string $relationship = 'layoutPositions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
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
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
} 