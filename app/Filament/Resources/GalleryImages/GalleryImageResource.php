<?php

namespace App\Filament\Resources\GalleryImages;

use App\Enums\GalleryCategory;
use App\Enums\GallerySpan;
use App\Filament\Resources\GalleryImages\Pages\ManageGalleryImages;
use App\Models\GalleryImage;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class GalleryImageResource extends Resource
{
    protected static ?string $model = GalleryImage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Gallery';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                SpatieMediaLibraryFileUpload::make('image')->collection('image')->image()->required()->maxSize(8192)->columnSpanFull(),
                TextInput::make('title')->label('Caption / alt text')->required()->maxLength(160)->columnSpanFull()
                    ->helperText('Describe what the photo shows; used as the caption and for screen readers.'),
                Select::make('category')->options(GalleryCategory::class)->required(),
                Select::make('span')->options(GallerySpan::class)->placeholder('Standard (1 × 1)'),
                Toggle::make('is_featured')->label('Show in homepage teaser'),
                Toggle::make('is_published')->label('Published')->default(true),
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
                TextColumn::make('title')->searchable()->limit(50),
                TextColumn::make('category')->badge(),
                TextColumn::make('span')->placeholder('Standard'),
                IconColumn::make('is_featured')->label('Teaser')->boolean(),
                IconColumn::make('is_published')->label('Published')->boolean(),
            ])
            ->filters([SelectFilter::make('category')->options(GalleryCategory::class)])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageGalleryImages::route('/')];
    }
}
