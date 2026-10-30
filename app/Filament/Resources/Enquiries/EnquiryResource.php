<?php

namespace App\Filament\Resources\Enquiries;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Filament\Resources\Enquiries\Pages\ListEnquiries;
use App\Filament\Resources\Enquiries\Pages\ViewEnquiry;
use App\Models\Enquiry;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class EnquiryResource extends Resource
{
    protected static ?string $model = Enquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getNavigationBadge(): ?string
    {
        $count = Enquiry::query()->where('status', EnquiryStatus::New)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    /**
     * @return list<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['reference', 'name', 'email', 'organisation'];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Enquiry')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('reference')->copyable()->weight('bold'),
                        TextEntry::make('type')->badge(),
                        TextEntry::make('name'),
                        TextEntry::make('organisation')->placeholder('—'),
                        TextEntry::make('email')->placeholder('—')->copyable()->url(fn (Enquiry $record): ?string => $record->email ? "mailto:{$record->email}" : null),
                        TextEntry::make('phone')->placeholder('—')->copyable()->url(fn (Enquiry $record): ?string => $record->phone ? 'tel:'.$record->phone : null),
                        TextEntry::make('subject')->label('Subject')->formatStateUsing(fn (Enquiry $record): ?string => $record->subjectLabel())->placeholder('—'),
                        TextEntry::make('payload.delivery_location')->label('Delivery location')->placeholder('—')->visible(fn (Enquiry $record): bool => $record->type === EnquiryType::Quote),
                        TextEntry::make('payload.required_by')->label('Required by')->date('j F Y')->placeholder('—')->visible(fn (Enquiry $record): bool => $record->type === EnquiryType::Quote),
                        TextEntry::make('message')->columnSpanFull()->placeholder('—')->prose(),
                    ]),
                Section::make('Handling')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('status')->badge(),
                        TextEntry::make('assignee.name')->label('Assigned to')->placeholder('Unassigned'),
                        TextEntry::make('created_at')->label('Received')->dateTime('j M Y, H:i'),
                        TextEntry::make('responded_at')->label('Responded')->dateTime('j M Y, H:i')->placeholder('—'),
                    ]),
                Section::make('Products requested')
                    ->columnSpanFull()
                    ->visible(fn (Enquiry $record): bool => $record->type === EnquiryType::Quote)
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('product_name')->label('Product'),
                                TextEntry::make('quantity')->numeric(),
                                TextEntry::make('unit')->placeholder('—'),
                                TextEntry::make('frequency')->badge(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')->searchable()->weight('bold')->copyable(),
                TextColumn::make('type')->badge()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('name')->searchable()->description(fn (Enquiry $record): ?string => $record->organisation),
                TextColumn::make('email')->searchable()->toggleable(),
                TextColumn::make('subject')->label('Subject')->formatStateUsing(fn (Enquiry $record): ?string => $record->subjectLabel())->toggleable(),
                TextColumn::make('items_count')->counts('items')->label('Lines')->toggleable(),
                TextColumn::make('assignee.name')->label('Assigned')->placeholder('—'),
                TextColumn::make('created_at')->label('Received')->since()->sortable()->dateTimeTooltip(),
            ])
            ->filters([
                Filter::make('open')->label('Open only')
                    ->query(fn (Builder $query): Builder => $query->whereIn('status', [EnquiryStatus::New, EnquiryStatus::InProgress]))
                    ->default(),
                SelectFilter::make('type')->options(EnquiryType::class),
                SelectFilter::make('status')->options(EnquiryStatus::class)->multiple(),
                SelectFilter::make('assigned_to')->label('Assigned to')->relationship('assignee', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),
                self::assignToMeAction(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_spam')
                        ->label('Mark as spam')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->authorize(fn (): bool => (bool) auth()->user()?->canManageEnquiries())
                        ->action(function (Collection $records): void {
                            foreach ($records as $enquiry) {
                                $enquiry->update(['status' => EnquiryStatus::Spam]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function assignToMeAction(): Action
    {
        return Action::make('assign_to_me')
            ->label('Assign to me')
            ->icon(Heroicon::OutlinedUserPlus)
            ->visible(fn (Enquiry $record): bool => $record->assigned_to !== auth()->id())
            ->authorize(fn (Enquiry $record): bool => (bool) auth()->user()?->can('update', $record))
            ->action(function (Enquiry $record): void {
                $record->update([
                    'assigned_to' => auth()->id(),
                    'status' => $record->status === EnquiryStatus::New ? EnquiryStatus::InProgress : $record->status,
                ]);
            });
    }

    public static function updateStatusAction(): Action
    {
        return Action::make('update_status')
            ->label('Update status')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->authorize(fn (Enquiry $record): bool => (bool) auth()->user()?->can('update', $record))
            ->fillForm(fn (Enquiry $record): array => ['status' => $record->status, 'assigned_to' => $record->assigned_to])
            ->schema([
                Select::make('status')->options(EnquiryStatus::class)->required(),
                Select::make('assigned_to')
                    ->label('Assigned to')
                    ->options(fn (): array => User::query()->where('is_active', true)->orderBy('name')->get()
                        ->filter(fn (User $user): bool => $user->canManageEnquiries())
                        ->pluck('name', 'id')->all())
                    ->placeholder('Unassigned'),
            ])
            ->action(function (Enquiry $record, array $data): void {
                $status = EnquiryStatus::from($data['status'] instanceof EnquiryStatus ? $data['status']->value : $data['status']);
                $record->update([
                    'status' => $status,
                    'assigned_to' => $data['assigned_to'] ?: null,
                    'responded_at' => $status === EnquiryStatus::Responded ? ($record->responded_at ?? now()) : $record->responded_at,
                ]);
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnquiries::route('/'),
            'view' => ViewEnquiry::route('/{record}'),
        ];
    }
}
