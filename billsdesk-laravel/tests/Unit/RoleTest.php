<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreateRole(): void
    {
        $role = Role::factory()->create([
            'name' => 'Admin',
            'permissions' => ['create', 'read', 'update', 'delete'],
            'isAdmin' => true,
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Admin',
            'isAdmin' => true,
        ]);

        $this->assertEquals('Admin', $role->name);
        $this->assertEquals(['create', 'read', 'update', 'delete'], $role->permissions);
        $this->assertTrue($role->isAdmin);
    }

    public function testRoleHasManyUsers(): void
    {
        $role = Role::factory()
            ->hasUsers(3) // Esto requiere la relación definida en el modelo
            ->create();

        $this->assertCount(3, $role->users);
        $this->assertInstanceOf(User::class, $role->users->first());
    }

    public function testCanCastPermissionsAsArray(): void
    {
        $role = Role::factory()->create([
            'permissions' => ['create', 'read'],
        ]);

        $this->assertIsArray($role->permissions);
        $this->assertEquals(['create', 'read'], $role->permissions);
    }

    public function testCantCreateRoleWithoutName(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Role::factory()->create([
            'name' => null,
        ]);
    }

    public function testCantCreateRoleWithoutPermissions(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Role::factory()->create([
            'permissions' => null,
        ]);
    }

    public function testRoleHasTimestamps(): void
    {
        $role = Role::factory()->create();

        $this->assertNotNull($role->created_at);
        $this->assertNotNull($role->updated_at);
    }
}
