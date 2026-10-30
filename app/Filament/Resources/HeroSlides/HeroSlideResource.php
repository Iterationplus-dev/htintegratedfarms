<?php

namespace App\Filament\Resources\HeroSlides;

use App\Filament\Resources\HeroSlides\Pages\ManageHeroSlides;
use App\Models\HeroSlide;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Homepage slides';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        // Site-relative paths ("/about", not protocol-relative "//host") or absolute https URLs only.
        $relativeOrHttps = ['nullable', 'string', 'max:255', 'regex:/^(\/(?!\/)[^\s]*|https:\/\/[^\s]+)$/'];

        return $schema
            ->columns(2)
            ->components([
                SpatieMediaLibraryFileUpload::make('image')->collection('image')->image()->required()->maxSize(10240)->columnSpanFull()
                    ->helperText('Landscape photo, at least 1600 px wide.'),
                TextInput::make('image_alt')->label('Image description (alt text)')->required()->maxLength(160),
                TextInput::make('image_position')->label('Focal point (CSS object-position)')->default('50% 50%')->maxLength(20)->regex('/^\d{1,3}% \d{1,3}%$/'),
                TextInput::make('eyebrow')->required()->maxLength(80),
                TextInput::make('mobile_eyebrow')->maxLength(80)->placeholder('Defaults to eyebrow'),
                TextInput::make('title')->required()->maxLength(120)->columnSpanFull(),
                Textarea::make('body')->required()->maxLength(300)->rows(3),
                Textarea::make('mobile_body')->maxLength(160)->rows(3)->placeholder('Shorter text for phones'),
                Section::make('Buttons')->columns(2)->columnSpanFull()->schema([
                    TextInput::make('primary_cta_label')->maxLength(40),
                    TextInput::make('primary_cta_url')->rules($relativeOrHttps)->placeholder('/about'),
                    TextInput::make('secondary_cta_label')->maxLength(40),
                    TextInput::make('secondary_cta_url')->rules($relativeOrHttps)->placeholder('/livestock'),
                ]),
                Toggle::make('is_active')->label('Active')->default(true),
                TextInput::make('sort')->numeric()->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->collection('image')->conversion('w400'),
                TextColumn::make('title')->limit(50),
                TextColumn::make('eyebrow'),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageHeroSlides::route('/')];
    }
}
