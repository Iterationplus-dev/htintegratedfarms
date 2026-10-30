<?php

namespace App\Filament\Widgets;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Models\Enquiry;
use App\Models\StockAlert;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Dashboard summary for administrators and sales staff. */
class EnquiryStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->canManageEnquiries();
    }

    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth();

        return [
            Stat::make('New enquiries', Enquiry::query()->where('status', EnquiryStatus::New)->count())
                ->description('Waiting for a first response')
                ->color('warning'),
            Stat::make('Open', Enquiry::query()->open()->count())
                ->description('New or in progress'),
            Stat::make('Quote requests this month', Enquiry::query()->where('type', EnquiryType::Quote)->where('created_at', '>=', $monthStart)->count()),
            Stat::make('Stock alert sign-ups', StockAlert::query()->pending()->count())
                ->description('Waiting for products to return'),
        ];
    }
}
