<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\SiteSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

/**
 * Contact details, WhatsApp, map and enquiry recipients (administrators only).
 *
 * @property-read Schema $form
 */
class SiteSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Site settings';

    protected static ?string $title = 'Site settings';

    protected static ?string $slug = 'site-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    /** Keys editable on this page (all others keep their config/site.php default). */
    public const KEYS = [
        'company_name', 'tagline', 'phone_primary', 'phone_secondary', 'phone_uk', 'email_info', 'email_partners',
        'whatsapp_number', 'address', 'working_hours', 'map_embed_url', 'map_directions_url', 'enquiry_recipients',
    ];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public function mount(SiteSettings $settings): void
    {
        $this->form->fill(collect(self::KEYS)->mapWithKeys(fn (string $key): array => [$key => $settings->get($key)])->all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Company')->columns(2)->schema([
                    TextInput::make('company_name')->required()->maxLength(120),
                    TextInput::make('tagline')->maxLength(200),
                ]),
                Section::make('Contact')->columns(2)->schema([
                    TextInput::make('phone_primary')->label('Primary phone')->tel()->required()->maxLength(30),
                    TextInput::make('phone_secondary')->label('Second phone')->tel()->maxLength(30),
                    TextInput::make('phone_uk')->label('UK phone')->tel()->maxLength(30),
                    TextInput::make('whatsapp_number')->label('WhatsApp number')->tel()->required()->maxLength(30)
                        ->helperText('International format, e.g. +234 810 780 7012.'),
                    TextInput::make('email_info')->label('General email')->email()->required(),
                    TextInput::make('email_partners')->label('Partnerships email')->email(),
                    Textarea::make('address')->label('Farm address')->rows(3)->required(),
                    Textarea::make('working_hours')->rows(3),
                ]),
                Section::make('Map')->columns(1)->schema([
                    TextInput::make('map_embed_url')->label('Google Maps embed URL')->url()->maxLength(1000)
                        ->rule('starts_with:https://www.google.com/maps/embed')
                        ->helperText('From Google Maps → Share → Embed a map → copy the src="…" URL. Visitors load it only on request.'),
                    TextInput::make('map_directions_url')->label('Directions link')->url()->maxLength(500)->rule('starts_with:https://'),
                ]),
                Section::make('Enquiry notifications')->schema([
                    TextInput::make('enquiry_recipients')->label('Send new enquiries to')->required()
                        ->rule('regex:/^\s*[^@\s,]+@[^@\s,]+\.[^@\s,]+(\s*,\s*[^@\s,]+@[^@\s,]+\.[^@\s,]+)*\s*$/')
                        ->helperText('One or more email addresses, separated by commas.'),
                ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([Actions::make([Action::make('save')->label('Save settings')->submit('save')])]),
        ]);
    }

    public function save(SiteSettings $settings): void
    {
        abort_unless(self::canAccess(), 403);

        $data = $this->form->getState();

        DB::transaction(function () use ($data): void {
            foreach (self::KEYS as $key) {
                Setting::updateOrCreate(['key' => $key], ['value' => isset($data[$key]) ? trim((string) $data[$key]) : null]);
            }
        });
        $settings->flush();

        Notification::make()->title('Settings saved')->success()->send();
    }
}
