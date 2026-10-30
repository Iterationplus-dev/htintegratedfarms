<?php

namespace App\Filament\Resources\Stats;

use App\Filament\Resources\Stats\Pages\ManageStats;
use App\Models\Stat;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class StatResource extends Resource
{
    protected static ?string $model = Stat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Headline figures';

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->required()->maxLength(60)->alphaDash()->unique(ignoreRecord: true)
                ->disabledOn('edit')->helperText('Internal identifier, e.g. home.acres.'),
            Select::make('page')->options(Stat::PAGES)->required()->disabledOn('edit'),
            TextInput::make('value')->required()->maxLength(20)->placeholder('50,000'),
            TextInput::make('label')->required()->maxLength(120),
            TextInput::make('short_label')->label('Short label (mobile)')->maxLength(60),
            TextInput::make('sort')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('page')->formatStateUsing(fn (string $state): string => Stat::PAGES[$state] ?? $state)->badge(),
                TextColumn::make('value')->weight('bold'),
                TextColumn::make('label'),
                TextColumn::make('short_label')->toggleable(),
            ])
            ->filters([SelectFilter::make('page')->options(Stat::PAGES)])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageStats::route('/')];
    }
}
