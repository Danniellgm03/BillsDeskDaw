<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreateUser(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('testuser@example.com', $user->email);
    }

    public function testUserBelongsToRole(): void
    {
        $role = Role::factory()->create(['name' => 'Admin']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertEquals('Admin', $user->role->name);
    }

    public function testUserBelongsToCompany(): void
    {
        $company = Company::factory()->create(['name' => 'Test Company']);
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(Company::class, $user->company);
        $this->assertEquals('Test Company', $user->company->name);
    }

    public function testUserToArrayIncludesRoleAndCompany(): void
    {
        $role = Role::factory()->create(['name' => 'Manager']);
        $company = Company::factory()->create(['name' => 'Test Inc.']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'company_id' => $company->id,
        ]);

        $userArray = $user->toArray();
        $this->assertArrayHasKey('role', $userArray);
        $this->assertArrayHasKey('company', $userArray);
        $this->assertEquals('Manager', $userArray['role']);
        $this->assertEquals('Test Inc.', $userArray['company']);
    }

    public function testUserWithAllData(): void
    {
        $role = Role::factory()->create(['name' => 'Admin']);
        $company = Company::factory()->create(['name' => 'Test Company']);
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'role_id' => $role->id,
            'company_id' => $company->id,
            'phone' => '1234567890',
            'Address' => '123 Test St',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'role_id' => $role->id,
            'company_id' => $company->id,
            'phone' => '1234567890',
            'Address' => '123 Test St',
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('testuser@example.com', $user->email);
        $this->assertEquals('1234567890', $user->phone);
        $this->assertEquals('123 Test St', $user->Address);
        $this->assertInstanceOf(Role::class, $user->role);

        $this->assertEquals('Admin', $user->role->name);
        $this->assertInstanceOf(Company::class, $user->company);
        $this->assertEquals('Test Company', $user->company->name);

        $userArray = $user->toArray();
        $this->assertArrayHasKey('role', $userArray);
        $this->assertArrayHasKey('company', $userArray);
        $this->assertEquals('Admin', $userArray['role']);
        $this->assertEquals('Test Company', $userArray['company']);

        $this->assertArrayHasKey('phone', $userArray);
    }

    public function testCantCreateUserWithoutName(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['name' => null]);
    }

    public function testCantCreateUserWithoutEmail(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => null]);
    }

    public function testCanCreateUserEmailUnique(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => 'testuser@example.com']);

        User::factory()->create(['email' => 'testuser@example.com']);

        $this->assertDatabaseCount('users', 1);
    }

    public function testUserHasTimestamps(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }

    public function testCantCreateUserWithoutPassword(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['password' => null]);
    }
}
