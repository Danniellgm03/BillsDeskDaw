<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Role;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsAllRoles()
    {
        // Crear roles de prueba en la base de datos
        Role::factory()->create([
            'name' => 'Admin',
            'permissions' => ['create', 'read', 'update', 'delete'],
            'isAdmin' => true,
        ]);

        Role::factory()->create([
            'name' => 'User',
            'permissions' => ['read'],
            'isAdmin' => false,
        ]);

        // Crear el controlador
        $controller = new RoleController();

        // Llamar al método index
        $response = $controller->index();

        // Validar la respuesta
        $this->assertEquals(200, $response->status());
        $this->assertCount(2, $response->getData());
    }

    public function testIndexEmptyArray(){
        // Crear el controlador
        $controller = new RoleController();

        // Llamar al método index
        $response = $controller->index();

        // Validar la respuesta
        $this->assertEquals(200, $response->status());
        $this->assertCount(0, $response->getData());
    }

    public function testStoreRole(){
        // Crear el controlador
        $controller = new RoleController();

        // Llamar al método store
        $response = $controller->store(new Request([
            'name' => 'Admin',
            'permissions' => ['create', 'read', 'update', 'delete'],
            'isAdmin' => true,
        ]));

        // Validar la respuesta
        $this->assertEquals(201, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('Admin', $responseData['name']);
        $this->assertEquals(['create', 'read', 'update', 'delete'], $responseData['permissions']);
    }

    public function testStoreRoleWithoutName(){
        // Crear el controlador
        $controller = new RoleController();

        // Llamar al método store
        $response = $controller->store(new Request([
            'permissions' => ['create', 'read', 'update', 'delete'],
            'isAdmin' => true,
        ]));

        // Validar la respuesta
        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testStoreRoleWithoutPermissions(){
        // Crear el controlador
        $controller = new RoleController();

        // Llamar al método store
        $response = $controller->store(new Request([
            'name' => 'Admin',
            'isAdmin' => true,
        ]));

        // Validar la respuesta
        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testStoreNameUnique(){
        // Crear roles de prueba en la base de datos
        Role::factory()->create([
            'name' => 'Admin',
            'permissions' => ['create', 'read', 'update', 'delete'],
            'isAdmin' => true,
        ]);

        // Crear el controlador
        $controller = new RoleController();

        // Llamar al método store
        $response = $controller->store(new Request([
            'name' => 'Admin',
            'permissions' => ['create', 'read', 'update', 'delete'],
            'isAdmin' => true,
        ]));

        // Validar la respuesta
        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testShowRole(){
        // Crear roles de prueba en la base de datos
        $role = Role::factory()->create([
            'name' => 'Admin',
            'permissions' => ['create', 'read', 'update', 'delete'],
            'isAdmin' => true,
        ]);

        // Crear el controlador
        $controller = new RoleController();

        // Llamar al método show
        $response = $controller->show($role->id);

        // Validar la respuesta
        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        $this->assertEquals('Admin', $responseData['name']);
        $this->assertEquals(['create', 'read', 'update', 'delete'], $responseData['permissions']);
    }

    public function testShowRoleNotFound(){
        // Crear el controlador
        $controller = new RoleController();

        // Llamar al método show
        $response = $controller->show(1);

        // Validar la respuesta
        $this->assertEquals(404, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('error', $responseData);
    }

    public function testUpdateRole(){
        $role = Role::factory()->create([
            'name' => 'Admin',
            'permissions' => ['create', 'read', 'update', 'delete'],
            'isAdmin' => true,
        ]);

        $controller = new RoleController();

        $response = $controller->update(new Request([
            'name' => 'User',
            'permissions' => ['read'],
            'isAdmin' => false,
        ]), $role->id);

        $this->assertEquals(200, $response->status());

        $responseData = $response->getData(true);

        print_r($responseData);

        $this->assertEquals('User', $responseData['name']);
        $this->assertEquals(['read'], $responseData['permissions']);
        $this->assertEquals(1, $responseData['isAdmin']); // No se puede cambiar el valor de isAdmin
    }

    public function testUpdateRoleNotFound(){
        // Crear el controlador
        $controller = new RoleController();

        // Llamar al método update
        $response = $controller->update(new Request([
            'name' => 'User',
            'permissions' => ['read'],
            'isAdmin' => false,
        ]), 1);

        // Validar la respuesta
        $this->assertEquals(404, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('error', $responseData);
    }

    public function testUpdateRoleWithoutName(){
        // Crear roles de prueba en la base de datos
        $role = Role::factory()->create([
            'name' => 'Admin',
            'permissions' => ['create', 'read', 'update', 'delete'],
            'isAdmin' => true,
        ]);

        // Crear el controlador
        $controller = new RoleController();

        // Llamar al método update
        $response = $controller->update(new Request([
            'permissions' => ['read'],
            'isAdmin' => false,
        ]), $role->id);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testUpdateRoleWithoutPermissions(){
        // Crear roles de prueba en la base de datos
        $role = Role::factory()->create([
            'name' => 'Admin',
            'permissions' => ['create', 'read', 'update', 'delete'],
            'isAdmin' => true,
        ]);

        // Crear el controlador
        $controller = new RoleController();

        // Llamar al método update
        $response = $controller->update(new Request([
            'name' => 'User',
            'isAdmin' => false,
        ]), $role->id);

        // Validar la respuesta
        $this->assertEquals(422, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testDestroyRole(){
        // Crear roles de prueba en la base de datos
        $role = Role::factory()->create([
            'name' => 'Admin',
            'permissions' => ['create', 'read', 'update', 'delete'],
            'isAdmin' => true,
        ]);

        // Crear el controlador
        $controller = new RoleController();

        // Llamar al método destroy
        $response = $controller->destroy($role->id);

        // Validar la respuesta
        $this->assertEquals(200, $response->status());
    }

    public function testDestroyRoleNotFound(){
        // Crear el controlador
        $controller = new RoleController();

        // Llamar al método destroy
        $response = $controller->destroy(1);

        // Validar la respuesta
        $this->assertEquals(404, $response->status());

        $responseData = $response->getData(true);

        $this->assertArrayHasKey('error', $responseData);
    }


}
