<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreateInvitation(): void
    {
        $invitation = Invitation::factory()->create([
            'email' => 'test@example.com',
            'token' => 'sample-token',
            'accepted' => false,
        ]);

        $this->assertDatabaseHas('invitations', [
            'email' => 'test@example.com',
            'token' => 'sample-token',
            'accepted' => false,
        ]);

        $this->assertEquals('test@example.com', $invitation->email);
        $this->assertEquals('sample-token', $invitation->token);
        $this->assertFalse($invitation->isAccepted());
    }

    public function testInvitationBelongsToRole(): void
    {
        $role = Role::factory()->create(['name' => 'Admin']);
        $invitation = Invitation::factory()->create(['role_id' => $role->id]);

        $this->assertInstanceOf(Role::class, $invitation->role);
        $this->assertEquals('Admin', $invitation->role->name);
    }

    public function testInvitationBelongsToCompany(): void
    {
        $company = Company::factory()->create(['name' => 'Test Company']);
        $invitation = Invitation::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(Company::class, $invitation->company);
        $this->assertEquals('Test Company', $invitation->company->name);
    }

    public function testCanVerifyIfInvitationIsAccepted(): void
    {
        $invitation = Invitation::factory()->create(['accepted' => true]);

        $this->assertTrue($invitation->isAccepted());

        $invitation = Invitation::factory()->create(['accepted' => false]);

        $this->assertFalse($invitation->isAccepted());
    }

    public function testCanUpdateInvitation(): void
    {
        $invitation = Invitation::factory()->create(['accepted' => false]);

        $invitation->update(['accepted' => true]);

        $this->assertTrue($invitation->isAccepted());
    }

    public function testCanDeleteInvitation(): void
    {
        $invitation = Invitation::factory()->create();

        $invitation->delete();

        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
    }

    public function testCreateInvitationWithAllData(): void
    {
        $role = Role::factory()->create();
        $company = Company::factory()->create();

        $invitation = Invitation::factory()->create([
            'email' => 'test@test.com',
            'token' => 'sample',
            'role_id' => $role->id,
            'company_id' => $company->id,
            'accepted' => false,
        ]);

        $this->assertDatabaseHas('invitations', [
            'email' => 'test@test.com',
            'token' => 'sample',
            'role_id' => $role->id,
            'company_id' => $company->id,
            'accepted' => false,
        ]);

        $this->assertEquals('test@test.com' , $invitation->email);
        $this->assertEquals('sample', $invitation->token);
        $this->assertEquals($role->id, $invitation->role_id);
        $this->assertEquals($company->id, $invitation->company_id);
        $this->assertFalse($invitation->isAccepted());
    }

    public function testCreateInvitationEmailUnique(): void
    {
        Invitation::factory()->create(['email' => 'test@test.com']);

        $this->expectException(\Exception::class);


        Invitation::factory()->create(['email' => 'test@test.com']);

        $this->assertDatabaseCount('invitations', 1);
    }

}
