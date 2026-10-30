<?php

namespace App\Filament\Resources\Products;

use App\Enums\ProductAvailability;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Product')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(120)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set, string $operation): void {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(140)
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->helperText('Used in the address: /products/{slug}. Changing it breaks existing links.'),
                        TextInput::make('summary')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText('Short line on catalogue cards.'),
                        TextInput::make('featured_summary')
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText('Optional longer line used on the homepage “Featured” cards.'),
                        Textarea::make('description')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                        Repeater::make('specs')
                            ->label('Specifications')
                            ->schema([
                                TextInput::make('label')->required()->maxLength(60),
                                TextInput::make('value')->required()->maxLength(120),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->defaultItems(0)
                            ->addActionLabel('Add specification')
                            ->columnSpanFull(),
                    ]),
                Section::make('Availability')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('product_category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->required()
                            ->preload(),
                        Select::make('availability')
                            ->options(ProductAvailability::class)
                            ->default(ProductAvailability::InStock)
                            ->required()
                            ->helperText('“Out of season” replaces Request quote with Notify me. Moving it back notifies subscribers.'),
                        TextInput::make('unit')
                            ->maxLength(40)
                            ->placeholder('crates, birds, litres…')
                            ->helperText('Shown beside quantities in quote requests.'),
                        Toggle::make('is_published')->label('Published')->default(true),
                        Toggle::make('is_featured')->label('Feature on homepage'),
                        TextInput::make('sort')->numeric()->default(0)->minValue(0),
                    ]),
                Section::make('Images')
                    ->columnSpanFull()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('images')
                            ->collection('images')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->maxFiles(6)
                            ->maxSize(8192)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('First image is the cover. JPEG/PNG/WebP up to 8 MB; web sizes are generated automatically.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('images')->conversion('w400')->limit(1)->square(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Category')->sortable(),
                TextColumn::make('availability')->badge()->sortable(),
                IconColumn::make('is_featured')->label('Featured')->boolean(),
                IconColumn::make('is_published')->label('Published')->boolean(),
                TextColumn::make('stock_alerts_count')->counts(['stockAlerts' => fn ($query) => $query->whereNull('notified_at')])->label('Waiting alerts')->toggleable(),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_category_id')->label('Category')->relationship('category', 'name'),
                SelectFilter::make('availability')->options(ProductAvailability::class),
                TernaryFilter::make('is_published')->label('Published'),
            ])
            ->recordActions([
                Action::make('view_on_site')
                    ->label('View')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Product $record): string => route('products.show', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Product $record): bool => $record->is_published),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
