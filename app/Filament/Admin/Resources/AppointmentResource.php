<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AppointmentResource\Pages;
use App\Filament\Admin\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Radio::make('status')
                    ->options([
                        'waiting' => 'Waiting',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                        'off' => 'Off',
                    ])
                    ->live()
                    ->default('waiting')
                    ->inline()
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->native(false)
                    ->visible(fn (Get $get) => $get('status') != 'off'),
                Forms\Components\Select::make('creator_id')
                    ->relationship('creator', 'name')
                    ->native(false)
                    ->required(),
                Forms\Components\Select::make('staff_id')
                    ->relationship('staff', 'name')
                    ->native(false)
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->default(today())
                    ->native(false)
                    ->afterOrEqual(today())
                    ->closeOnDateSelection()
                    ->required(),
                // Forms\Components\Toggle::make('all_day')
                //     ->live(),
                Forms\Components\TimePicker::make('start')
                    // ->default(now())
                    // ->native(false)
                    ->seconds(false)
                    ->minutesStep(30)
                    ->required()
                    ->visible(fn(Get $get) => !$get('all_day')),
                Forms\Components\TimePicker::make('end')
                    // ->default(now())
                    // ->native(false)
                    ->seconds(false)
                    ->minutesStep(30)
                    ->required()
                    ->visible(fn(Get $get) => !$get('all_day')),
                // Forms\Components\TextInput::make('duration')
                //     ->default(30)
                //     ->numeric()
                //     ->minValue(30)
                //     ->maxValue(120)
                //     ->step(30)
                //     ->required()
                //     ->visible(fn (Get $get) => !$get('all_day')),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('staff.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start'),
                Tables\Columns\TextColumn::make('end'),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
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
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
