<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Contact;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreateContact(): void
    {
        $contact = Contact::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Test Lane',
        ]);

        $this->assertDatabaseHas('contacts', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Test Lane',
        ]);

        $this->assertEquals('John Doe', $contact->name);
        $this->assertEquals('john@example.com', $contact->email);
        $this->assertEquals('1234567890', $contact->phone);
        $this->assertEquals('123 Test Lane', $contact->address);
    }

    public function testContactBelongsToCompany(): void
    {
        $company = Company::factory()->create(['name' => 'Test Company']);
        $contact = Contact::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(Company::class, $contact->company);
        $this->assertEquals('Test Company', $contact->company->name);
    }

    public function testContactHasManyInvoices(): void
    {
        $contact = Contact::factory()
            ->hasInvoices(3)
            ->create();

        $this->assertCount(3, $contact->invoices);
        $this->assertInstanceOf(Invoice::class, $contact->invoices->first());
    }

    public function testCreateContactWithoutEmail(): void
    {
        $contact = Contact::factory()->create(['email' => null]);

        $this->assertDatabaseHas('contacts', ['email' => null]);
        $this->assertNull($contact->email);
    }

    public function testCreateContactWithoutPhone(): void
    {
        $contact = Contact::factory()->create(['phone' => null]);

        $this->assertDatabaseHas('contacts', ['phone' => null]);
        $this->assertNull($contact->phone);
    }

    public function testCreateContactWithoutAddress(): void
    {
        $contact = Contact::factory()->create(['address' => null]);

        $this->assertDatabaseHas('contacts', ['address' => null]);
        $this->assertNull($contact->address);
    }

    public function testCreateContactWithoutCompany(): void
    {
        $contact = Contact::factory()->create(['company_id' => null]);

        $this->assertDatabaseHas('contacts', ['company_id' => null]);
        $this->assertNull($contact->company_id);
    }

    public function testContactNameIsRequired(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Contact::factory()->create(['name' => null]);
    }
}
