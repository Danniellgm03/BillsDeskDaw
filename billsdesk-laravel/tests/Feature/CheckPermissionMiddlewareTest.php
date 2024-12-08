<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;


class CheckPermissionMiddlewareTest extends TestCase
{

//     use RefreshDatabase;


//     protected function setUp(): void
//     {
//         parent::setUp();

//         // Definir una ruta temporal para probar el middleware
//         Route::middleware(['auth:sanctum', \App\Http\Middleware\CheckPermission::class.':manage_users,view_users'])
//             ->get('/test-route', function () {
//                 return response()->json(['message' => 'Access granted']);
//             });
//     }

//     public function testAdminCanAccessRoute()
//     {
//         // Crear un usuario administrador
//         $role = Role::factory()->create(['isAdmin' => true, 'permissions' => []]);
//         $admin = User::factory()->create(['role_id' => $role->id]);

//         // Actuar como administrador
//         $this->actingAs($admin)
//             ->get('/test-route')
//             ->assertOk()
//             ->assertJson(['message' => 'Access granted']);
//     }

//     public function testUserWithPermissionCanAccessRoute()
//     {
//         // Crear un rol con los permisos necesarios
//         $role = Role::factory()->create(['isAdmin' => false, 'permissions' => ['manage_users', 'view_users']]);
//         $user = User::factory()->create(['role_id' => $role->id]);

//         // Actuar como usuario
//         $this->actingAs($user)
//             ->get('/test-route')
//             ->assertOk()
//             ->assertJson(['message' => 'Access granted']);
//     }

//     public function testUserWithoutPermissionCannotAccessRoute()
//     {
//         // Crear un rol sin los permisos necesarios
//         $role = Role::factory()->create(['isAdmin' => false, 'permissions' => ['view_invoices']]);
//         $user = User::factory()->create(['role_id' => $role->id]);

//         // Actuar como usuario
//         $this->actingAs($user)
//             ->get('/test-route')
//             ->assertStatus(403)
//             ->assertJson(['error' => 'No tienes permisos para realizar esta acción']);
//     }

//     public function testGuestCannotAccessRoute()
//     {
//         // Intentar acceder sin autenticar
//         $this->get('/test-route')
//             ->assertStatus(403)
//             ->assertJson(['error' => 'No tienes permisos para realizar esta acción']);
//     }
}
