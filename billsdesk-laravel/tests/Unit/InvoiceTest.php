<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Company;
use App\Models\User;
use App\Models\File;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\InvoiceTemplate;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreateInvoice(): void
    {
        $invoice = Invoice::factory()->create([
            'name_invoice' => 'Test Invoice',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('invoices', [
            'name_invoice' => 'Test Invoice',
            'status' => 'pending',
        ]);

        $this->assertEquals('Test Invoice', $invoice->name_invoice);
        $this->assertEquals('pending', $invoice->status);
    }

    public function testInvoiceBelongsToCompany(): void
    {
        $company = Company::factory()->create(['name' => 'Test Company']);
        $invoice = Invoice::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(Company::class, $invoice->company);
        $this->assertEquals('Test Company', $invoice->company->name);
    }

    public function testInvoiceBelongsToUser(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $invoice->user);
        $this->assertEquals('Test User', $invoice->user->name);
    }

    public function testInvoiceBelongsToFile(): void
    {
        $file = File::factory()->create(['file_name' => 'test-file.txt']);
        $invoice = Invoice::factory()->create(['file_id' => $file->id]);

        $this->assertInstanceOf(File::class, $invoice->file);
        $this->assertEquals('test-file.txt', $invoice->file->file_name);
    }

    public function testInvoiceBelongsToContact(): void
    {
        $contact = Contact::factory()->create(['name' => 'Test Contact']);
        $invoice = Invoice::factory()->create(['contact_id' => $contact->id]);

        $this->assertInstanceOf(Contact::class, $invoice->contact);
        $this->assertEquals('Test Contact', $invoice->contact->name);
    }

    public function testInvoiceWithAllData(): void
    {
        $company = Company::factory()->create(['name' => 'Test Company']);
        $user = User::factory()->create(['name' => 'Test User']);
        $file = File::factory()->create(['file_name' => 'test-file.txt']);
        $contact = Contact::factory()->create(['name' => 'Test Contact']);
        $template = InvoiceTemplate::factory()->create();

        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'file_id' => $file->id,
            'contact_id' => $contact->id,
            'name_invoice' => 'Test Invoice',
            'status' => 'pending',
            'template_id' => $template->_id,
        ]);

        $this->assertInstanceOf(Company::class, $invoice->company);
        $this->assertEquals('Test Company', $invoice->company->name);

        $this->assertInstanceOf(User::class, $invoice->user);
        $this->assertEquals('Test User', $invoice->user->name);

        $this->assertInstanceOf(File::class, $invoice->file);
        $this->assertEquals('test-file.txt', $invoice->file->file_name);

        $this->assertInstanceOf(Contact::class, $invoice->contact);
        $this->assertEquals('Test Contact', $invoice->contact->name);

        $this->assertEquals('Test Invoice', $invoice->name_invoice);
        $this->assertEquals('pending', $invoice->status);

        $this->assertEquals($template->_id, $invoice->template_id);
    }

    public function testInvoiceDateToPayIsNullable(): void
    {
        $invoice = Invoice::factory()->create(['date_to_pay' => null]);

        $this->assertNull($invoice->date_to_pay);
    }

    public function testInvoiceStatusIsPendingByDefault(): void
    {
        $invoice = Invoice::factory()->create();

        $this->assertEquals('pending', $invoice->status);
    }

    public function testOnCascadeDeleteCompany(): void
    {
        $company = Company::factory()->create();
        $invoice = Invoice::factory()->create(['company_id' => $company->id]);

        $company->delete();

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function testOnCascadeDeleteUser(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function testHasTimestamps(): void
    {
        $invoice = Invoice::factory()->create();

        $this->assertNotNull($invoice->created_at);
        $this->assertNotNull($invoice->updated_at);
    }
}
