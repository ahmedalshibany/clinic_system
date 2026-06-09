<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->patient = Patient::factory()->create();
        Service::factory()->create(['name' => 'General Checkup', 'price' => 150.00, 'is_active' => true]);
    }

    public function test_english_invoice_create_page_contains_english_keys(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/invoices/create?lang=en');

        $response->assertStatus(200);
        $response->assertSee(__('messages.invoicesCreate', [], 'en'));
        $response->assertSee(__('messages.addItem', [], 'en'));
        $response->assertSee(__('messages.saveInvoice', [], 'en'));
        $response->assertSee(__('messages.selectPatient', [], 'en'));
        $response->assertSee(__('messages.invoiceItems', [], 'en'));
        $response->assertSee(__('messages.discountPercent', [], 'en'));
        $response->assertSee(__('messages.taxPercent', [], 'en'));
        $response->assertSee(__('messages.notesPlaceholder', [], 'en'));
        $response->assertSee(__('messages.serviceDescription', [], 'en'));
    }

    public function test_invoice_create_page_contains_currency_symbol(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/invoices/create?lang=en');

        $response->assertStatus(200);
        $response->assertSee("currencySymbol = '﷼'", false);
    }

    public function test_arabic_invoice_create_page_contains_arabic_keys(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/invoices/create?lang=ar');

        $response->assertStatus(200);
        $response->assertSee(__('messages.invoicesCreate', [], 'ar'));
        $response->assertSee(__('messages.addItem', [], 'ar'));
        $response->assertSee(__('messages.saveInvoice', [], 'ar'));
        $response->assertSee(__('messages.selectPatient', [], 'ar'));
        $response->assertSee(__('messages.invoiceItems', [], 'ar'));
        $response->assertSee(__('messages.discountPercent', [], 'ar'));
        $response->assertSee(__('messages.taxPercent', [], 'ar'));
        $response->assertSee(__('messages.notesPlaceholder', [], 'ar'));
        $response->assertSee(__('messages.serviceDescription', [], 'ar'));
    }

    public function test_arabic_invoice_create_page_contains_same_currency_symbol(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/invoices/create?lang=ar');

        $response->assertStatus(200);
        $response->assertSee("currencySymbol = '﷼'", false);
    }

    public function test_english_page_has_no_arabic_text(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/invoices/create?lang=en');

        $response->assertStatus(200);
        $response->assertDontSee('الفواتير / إنشاء');
        $response->assertDontSee('إضافة بند');
        $response->assertDontSee('حفظ الفاتورة');
    }

    public function test_arabic_page_has_no_english_text_for_key_strings(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/invoices/create?lang=ar');

        $response->assertStatus(200);
        $response->assertDontSee('Invoices / Create');
        $response->assertDontSee('Add Item');
        $response->assertDontSee('Save Invoice');
    }

    public function test_default_locale_is_english_without_lang_param(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/invoices/create');

        $response->assertStatus(200);
        $response->assertSee(__('messages.invoicesCreate', [], 'en'));
        $response->assertSee("currencySymbol = '﷼'", false);
    }

    public function test_receptionist_can_see_localized_invoice_create(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);

        $response = $this->actingAs($receptionist)
            ->get('/invoices/create?lang=en');

        $response->assertStatus(200);
        $response->assertSee(__('messages.invoicesCreate', [], 'en'));
    }

    public function test_doctor_cannot_access_invoice_create(): void
    {
        $doctor = User::factory()->create(['role' => 'doctor']);

        $response = $this->actingAs($doctor)
            ->get('/invoices/create?lang=en');

        $response->assertStatus(403);
    }
}
