<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Website content (products, gallery, posts, hero slides…): administrators and content editors.
 * Sales staff have no access to content.
 */
abstract class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageContent();
    }

    public function view(User $user, Model $model): bool
    {
        return $user->canManageContent();
    }

    public function create(User $user): bool
    {
        return $user->canManageContent();
    }

    public function update(User $user, Model $model): bool
    {
        return $user->canManageContent();
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->canManageContent();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageContent();
    }

    public function reorder(User $user): bool
    {
        return $user->canManageContent();
    }

    public function restore(User $user, Model $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return false;
    }
}
