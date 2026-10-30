<?php

namespace App\Policies;

use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProductCategoryPolicy extends ContentPolicy
{
    /** A category that still has products cannot be deleted (also enforced by the foreign key). */
    public function delete(User $user, Model $model): bool
    {
        /** @var ProductCategory $model */
        return $user->canManageContent() && ! $model->products()->exists();
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
