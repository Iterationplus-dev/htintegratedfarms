<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** Editors may change figures; only administrators add or remove stat slots (pages expect them). */
class StatPolicy extends ContentPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
