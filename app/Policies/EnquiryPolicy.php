<?php

namespace App\Policies;

use App\Models\Enquiry;
use App\Models\User;

/**
 * Enquiries: administrators and sales staff. Enquiries only originate from the public forms,
 * so nobody can create them in the admin panel; only administrators can delete them.
 */
class EnquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageEnquiries();
    }

    public function view(User $user, Enquiry $enquiry): bool
    {
        return $user->canManageEnquiries();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Enquiry $enquiry): bool
    {
        return $user->canManageEnquiries();
    }

    public function delete(User $user, Enquiry $enquiry): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Enquiry $enquiry): bool
    {
        return false;
    }

    public function forceDelete(User $user, Enquiry $enquiry): bool
    {
        return false;
    }
}
