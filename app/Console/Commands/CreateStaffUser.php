<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

#[Signature('app:create-user {email? : Email address} {--name= : Display name} {--role= : admin, sales or editor}')]
#[Description('Create (or re-activate) a staff account for the admin panel')]
class CreateStaffUser extends Command
{
    public function handle(): int
    {
        $email = $this->argument('email') ?? text('Email address', required: true);
        $name = $this->option('name') ?? text('Name', required: true);
        $role = $this->option('role') ?? select('Role', collect(UserRole::cases())->mapWithKeys(fn (UserRole $r) => [$r->value => $r->getLabel()])->all(), default: UserRole::Admin->value);
        $secret = password('Password (min. 12 characters, mixed case and numbers)', required: true);

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'role' => $role, 'password' => $secret],
            [
                'email' => ['required', 'email:rfc', 'max:190'],
                'name' => ['required', 'string', 'max:120'],
                'role' => ['required', Rule::enum(UserRole::class)],
                'password' => ['required', Password::min(12)->mixedCase()->numbers()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::updateOrCreate(['email' => mb_strtolower($email)], [
            'name' => $name,
            'role' => UserRole::from($role),
            'password' => $secret,
            'is_active' => true,
        ]);

        $this->info(($user->wasRecentlyCreated ? 'Created' : 'Updated')." {$user->role->getLabel()} account for {$user->email}.");

        return self::SUCCESS;
    }
}
