<?php

namespace App\Filament\Resources\Enquiries\Pages;

use App\Filament\Resources\Enquiries\EnquiryResource;
use Filament\Resources\Pages\ListRecords;

/** Enquiries are created by the public forms only, so there is no "New" action. */
class ListEnquiries extends ListRecords
{
    protected static string $resource = EnquiryResource::class;
}
