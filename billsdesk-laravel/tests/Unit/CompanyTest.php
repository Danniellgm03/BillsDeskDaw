<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Company;
use App\Models\User;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreateCompany(): void
    {
        $company = Company::factory()->create([
            'name' => 'Test Company',
            'address' => '123 Test Street',
        ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'Test Company',
            'address' => '123 Test Street',
        ]);

        $this->assertEquals('Test Company', $company->name);
        $this->assertEquals('123 Test Street', $company->address);
    }

    public function testCompanyHasManyUsers(): void
    {
        $company = Company::factory()
            ->hasUsers(3)
            ->create();

        $this->assertCount(3, $company->users);
        $this->assertInstanceOf(User::class, $company->users->first());
    }

    public function testCompanyHasManyContacts(): void
    {
        $company = Company::factory()
            ->hasContacts(2)
            ->create();

        $this->assertCount(2, $company->contacts);
        $this->assertInstanceOf(Contact::class, $company->contacts->first());
    }

    public function testCreateCompanyWithAddressNull(): void
    {
        $company = Company::factory()->create([
            'name' => 'Test Company',
            'address' => null,
        ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'Test Company',
            'address' => null,
        ]);

        $this->assertEquals('Test Company', $company->name);
        $this->assertNull($company->address);
    }

    public function testCreateWithoutName(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Company::factory()->create([
            'name' => null,
        ]);
    }
}
