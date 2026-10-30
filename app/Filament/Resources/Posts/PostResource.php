<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Farm updates';

    protected static ?string $modelLabel = 'farm update';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Article')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(180)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set, string $operation): void {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')->required()->maxLength(200)->alphaDash()->unique(ignoreRecord: true),
                        Textarea::make('excerpt')->rows(2)->maxLength(300),
                        RichEditor::make('body')
                            ->required()
                            ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList', 'blockquote'], ['undo', 'redo']])
                            ->helperText('Formatting is limited to a safe set; anything else is removed when the page is shown.'),
                    ]),
                Section::make('Publishing')
                    ->columnSpan(1)
                    ->schema([
                        DateTimePicker::make('published_at')->label('Publish at')->helperText('Leave empty to keep as a draft. Future dates are scheduled.'),
                        TextInput::make('category_label')->label('Category')->required()->maxLength(60)->placeholder('Poultry'),
                        TextInput::make('topic')->maxLength(60)->placeholder('Farm Management'),
                        TextInput::make('reading_minutes')->numeric()->minValue(1)->maxValue(60)->default(3)->required(),
                        SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image()->maxSize(8192),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->conversion('w400')->square(),
                TextColumn::make('title')->searchable()->limit(60),
                TextColumn::make('category_label')->label('Category'),
                TextColumn::make('published_at')->label('Published')->dateTime('j M Y')->placeholder('Draft')->sortable(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
