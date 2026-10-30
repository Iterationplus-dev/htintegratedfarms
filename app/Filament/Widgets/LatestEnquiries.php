<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Models\Enquiry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestEnquiries extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Latest open enquiries';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->canManageEnquiries();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Enquiry::query()->open()->with('assignee')->latest())
            ->paginated([5])
            ->columns([
                TextColumn::make('reference')->weight('bold'),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('name')->description(fn (Enquiry $record): ?string => $record->organisation),
                TextColumn::make('assignee.name')->label('Assigned')->placeholder('—'),
                TextColumn::make('created_at')->label('Received')->since(),
            ])
            ->recordUrl(fn (Enquiry $record): string => EnquiryResource::getUrl('view', ['record' => $record]));
    }
}
