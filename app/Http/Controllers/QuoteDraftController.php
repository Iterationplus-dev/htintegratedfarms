<?php

namespace App\Http\Controllers;

use App\Models\QuoteDraft;
use Illuminate\Http\RedirectResponse;

/** Opens a saved quote draft from the signed link emailed by "Save and continue later". */
class QuoteDraftController extends Controller
{
    public function __invoke(QuoteDraft $draft): RedirectResponse
    {
        if ($draft->isExpired()) {
            return redirect()->route('quote')->with('status', 'That saved quote request has expired. Please start a new one.');
        }

        $draft->forceFill(['resumed_at' => now()])->save();

        return redirect()->route('quote')
            ->with('quote_draft', $draft->uuid)
            ->with('status', 'Welcome back. Your saved quote request has been restored.');
    }
}
