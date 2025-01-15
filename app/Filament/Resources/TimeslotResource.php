<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimeslotResource\Pages;
use App\Filament\Resources\TimeslotResource\RelationManagers;
use App\Models\Timeslot;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TimeslotResource extends Resource
{
    protected static ?string $model = Timeslot::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tennis_court_id')
                    ->relationship('tennisCourt', 'name')
                    ->required(),
                Forms\Components\DateTimePicker::make('start_time')
                    ->required()
                    ->displayFormat('Y-m-d H:00')
                    ->hoursStep(1)
                    // ->format('Y-m-d H:00:00')
                    ->afterOrEqual(today())
                    ->rules([
                        function () {
                            return function (string $attribute, $value, Closure $fail) {
                                $time = Carbon::parse($value);
                                // if ($time->format('i') !== '00') {
                                //     $fail('Start time must be at the start of an hour.');
                                // }
                            };
                        },
                    ]),
                Forms\Components\DateTimePicker::make('end_time')
                    ->required()
                    ->displayFormat('Y-m-d H:00')
                    ->hoursStep(1)
                    // ->format('Y-m-d H:00:00')
                    ->afterOrEqual('start_time')
                    ->rules([
                        function () {
                            return function (string $attribute, $value, Closure $fail) {
                                $time = Carbon::parse($value);
                                // if ($time->format('i') !== '00') {
                                //     $fail('End time must be at the start of an hour.');
                                // }

                                $startTime = Carbon::parse(request()->input('start_time'));
                                $endTime = Carbon::parse($value);

                                $diffInHours = $endTime->diffInHours($startTime);
                                $diffInMinutes = $endTime->diffInMinutes($startTime);
                                logger()->info(request()['start_time'] . ' ' .$startTime . ' ' . $endTime . ' ' . $diffInHours . ' ' . $diffInMinutes);
                                if ($diffInMinutes != ($diffInHours * 60)) {
                                    logger()->info($diffInHours . ' a ' . $diffInMinutes);

                                    $fail('Time difference must be in complete hours.');
                                }
                            };
                        },
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tennisCourt.name'),
                Tables\Columns\TextColumn::make('start_time')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('end_time')
                    ->dateTime(),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTimeslots::route('/'),
            'create' => Pages\CreateTimeslot::route('/create'),
            'edit' => Pages\EditTimeslot::route('/{record}/edit'),
        ];
    }
}
