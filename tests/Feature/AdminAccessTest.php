<?php

namespace Tests\Feature;

use App\Enums\EnquiryStatus;
use App\Filament\Pages\SiteSettingsPage;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Enquiries\Pages\ListEnquiries;
use App\Filament\Resources\Enquiries\Pages\ViewEnquiry;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Enquiry;
use App\Models\Setting;
use App\Models\User;
use App\Services\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get(EnquiryResource::getUrl('index'))->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk();
    }

    public function test_inactive_staff_cannot_use_the_panel(): void
    {
        $this->actingAs(User::factory()->admin()->inactive()->create())->get('/admin')->assertForbidden();
    }

    /**
     * Role matrix: [role, enquiries, products, users, settings].
     *
     * @return array<string, array{string, int, int, int, int}>
     */
    public static function roleMatrix(): array
    {
        return [
            'admin' => ['admin', 200, 200, 200, 200],
            'sales' => ['sales', 200, 403, 403, 403],
            'editor' => ['editor', 403, 200, 403, 403],
        ];
    }

    #[DataProvider('roleMatrix')]
    public function test_each_role_reaches_only_its_sections(string $role, int $enquiries, int $products, int $users, int $settings): void
    {
        $this->actingAs(User::factory()->{$role}()->create());

        $this->get('/admin')->assertOk();
        $this->get(EnquiryResource::getUrl('index'))->assertStatus($enquiries);
        $this->get(ProductResource::getUrl('index'))->assertStatus($products);
        $this->get(UserResource::getUrl('index'))->assertStatus($users);
        $this->get(SiteSettingsPage::getUrl())->assertStatus($settings);
    }

    public function test_sales_can_view_and_progress_an_enquiry(): void
    {
        $sales = User::factory()->sales()->create();
        $enquiry = Enquiry::factory()->quote()->create();
        $this->actingAs($sales);

        Livewire::test(ListEnquiries::class)->assertCanSeeTableRecords([$enquiry]);

        Livewire::test(ViewEnquiry::class, ['record' => $enquiry->getRouteKey()])
            ->assertSee($enquiry->reference)
            ->callAction('assign_to_me');

        $enquiry->refresh();
        $this->assertTrue($enquiry->assignee->is($sales));
        $this->assertSame(EnquiryStatus::InProgress, $enquiry->status);

        Livewire::test(ViewEnquiry::class, ['record' => $enquiry->getRouteKey()])
            ->callAction('update_status', data: ['status' => EnquiryStatus::Responded->value, 'assigned_to' => $sales->id]);

        $this->assertSame(EnquiryStatus::Responded, $enquiry->fresh()->status);
        $this->assertNotNull($enquiry->fresh()->responded_at);
    }

    public function test_only_admins_can_delete_enquiries_and_nobody_creates_them(): void
    {
        $enquiry = Enquiry::factory()->create();

        $this->assertFalse(User::factory()->sales()->create()->can('delete', $enquiry));
        $this->assertTrue(User::factory()->admin()->create()->can('delete', $enquiry));
        $this->assertFalse(User::factory()->admin()->create()->can('create', Enquiry::class));
    }

    public function test_admins_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertFalse($admin->can('delete', $admin));
        $this->assertTrue($admin->can('delete', User::factory()->create()));
    }

    public function test_editor_can_update_a_product(): void
    {
        $product = $this->makeProduct();
        $this->actingAs(User::factory()->editor()->create());

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm(['summary' => 'Graded and crated every morning.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Graded and crated every morning.', $product->fresh()->summary);
    }

    public function test_admin_saves_site_settings_and_the_site_uses_them(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(SiteSettingsPage::class)
            ->fillForm(['phone_primary' => '+234 800 111 2222', 'enquiry_recipients' => 'sales@htfarms.example'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('+234 800 111 2222', Setting::where('key', 'phone_primary')->value('value'));
        $this->assertSame(['sales@htfarms.example'], app(SiteSettings::class)->enquiryRecipients());
        $this->get('/contact')->assertSee('+234 800 111 2222');
    }

    public function test_settings_page_rejects_non_admins_even_when_called_directly(): void
    {
        $this->actingAs(User::factory()->sales()->create());

        Livewire::test(SiteSettingsPage::class)->assertForbidden();
    }

    public function test_create_user_command_creates_an_admin(): void
    {
        $this->artisan('app:create-user', ['email' => 'Owner@HTFarms.example', '--name' => 'Owner', '--role' => 'admin'])
            ->expectsQuestion('Password (min. 12 characters, mixed case and numbers)', 'Str0ngPassword!')
            ->assertSuccessful();

        $user = User::where('email', 'owner@htfarms.example')->sole();
        $this->assertTrue($user->isAdmin());

        $this->artisan('app:create-user', ['email' => 'weak@htfarms.example', '--name' => 'W', '--role' => 'sales'])
            ->expectsQuestion('Password (min. 12 characters, mixed case and numbers)', 'short')
            ->assertFailed();
    }
}
