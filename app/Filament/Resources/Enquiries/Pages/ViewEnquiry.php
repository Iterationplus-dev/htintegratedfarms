<?php

namespace App\Filament\Resources\Enquiries\Pages;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Models\Enquiry;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewEnquiry extends ViewRecord
{
    protected static string $resource = EnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label('Reply by email')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('gray')
                ->visible(fn (Enquiry $record): bool => (bool) $record->email)
                ->url(fn (Enquiry $record): string => 'mailto:'.$record->email.'?subject='.rawurlencode("Re: your enquiry {$record->reference}")),
            EnquiryResource::assignToMeAction(),
            EnquiryResource::updateStatusAction(),
            DeleteAction::make(),
        ];
    }
}
