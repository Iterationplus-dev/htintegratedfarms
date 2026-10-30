<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property UserRole $role
 * @property bool $is_active
 */
#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Only active staff accounts may sign in to the admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    public function isAdmin(): bool
    {
        return $this->is_active && $this->role === UserRole::Admin;
    }

    public function canManageEnquiries(): bool
    {
        return $this->is_active && $this->role->canManageEnquiries();
    }

    public function canManageContent(): bool
    {
        return $this->is_active && $this->role->canManageContent();
    }

    /**
     * @return HasMany<Enquiry, $this>
     */
    public function assignedEnquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class, 'assigned_to');
    }
}
