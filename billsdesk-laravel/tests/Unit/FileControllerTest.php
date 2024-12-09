<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\File;
use App\Models\User;
use App\Models\Company;
use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsPaginatedFiles()
    {
        $company = Company::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
        ]);

        Auth::login($authUser);

        File::factory(10)->create([
            'company_id' => $company->id,
        ]);

        $controller = new FileController();

        $request = new Request([
            'per_page' => 5,
        ]);

        $response = $controller->index($request);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals(5, count($responseData['data']['data'])); // Verificar los archivos en la página actual
        $this->assertEquals(2, $responseData['data']['last_page']); // Última página
        $this->assertEquals(5, $responseData['data']['per_page']); // Archivos por página
        $this->assertEquals(10, $responseData['data']['total']); // Total de archivos
    }

    public function testIndexReturnsPaginatedFilesSearch()
    {
        $company = Company::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
        ]);

        Auth::login($authUser);

        File::factory()->create([
            'company_id' => $company->id,
            'file_name' => 'File 1',
        ]);

        File::factory()->create([
            'company_id' => $company->id,
            'file_name' => 'File 2',
        ]);

        $controller = new FileController();

        $request = new Request([
            'per_page' => 5,
            'search' => 'File 1',
        ]);

        $response = $controller->index($request);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals(1, count($responseData['data']['data'])); // Verificar los archivos en la página actual
        $this->assertEquals(1, $responseData['data']['last_page']); // Última página
        $this->assertEquals(5, $responseData['data']['per_page']); // Archivos por página
        $this->assertEquals(1, $responseData['data']['total']); // Total de archivos
    }

    public function testIndexReturnsPaginatedFilesIsFav()
    {
        $company = Company::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
        ]);

        Auth::login($authUser);

        File::factory()->create([
            'company_id' => $company->id,
            'is_fav' => true,
        ]);

        File::factory()->create([
            'company_id' => $company->id,
            'is_fav' => false,
        ]);

        $controller = new FileController();

        $request = new Request([
            'per_page' => 5,
            'is_fav' => true,
        ]);

        $response = $controller->index($request);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals(1, count($responseData['data']['data'])); // Verificar los archivos en la página actual
        $this->assertEquals(1, $responseData['data']['last_page']); // Última página
        $this->assertEquals(5, $responseData['data']['per_page']); // Archivos por página
        $this->assertEquals(1, $responseData['data']['total']); // Total de archivos
    }

    public function testShowReturnsFile()
    {
        $company = Company::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
        ]);

        Auth::login($authUser);

        $file = File::factory()->create([
            'company_id' => $company->id,
            'created_by' => $authUser->id,
        ]);

        $controller = new FileController();

        $request = new Request();

        $response = $controller->show($request, $file->id);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals($file->id, $responseData['data']['id']);
    }

    public function testShowReturnsFile403()
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
        ]);

        Auth::login($authUser);

        $file = File::factory()->create([
            'company_id' => $otherCompany->id, // Archivo de otra compañía
            'created_by' => User::factory()->create()->id, // Creado por otro usuario
        ]);

        $controller = new FileController();

        $request = new Request();

        $response = $controller->show($request, $file->id);

        $this->assertEquals(404, $response->status());
    }


    public function testShowReturnsFile404()
    {
        $company = Company::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
        ]);

        Auth::login($authUser);

        $file = File::factory()->create([
            'company_id' => $company->id,
            'created_by' => $authUser->id,
        ]);

        $controller = new FileController();

        $request = new Request();

        $response = $controller->show($request, $file->id + 1);

        $this->assertEquals(404, $response->status());
    }

    public function testShowByCompanyReturnsFiles()
    {
        $company = Company::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
        ]);

        Auth::login($authUser);

        File::factory(10)->create([
            'company_id' => $company->id,
        ]);

        $controller = new FileController();

        $request = new Request();

        $response = $controller->showByCompany($request, $company->id);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals(10, count($responseData['data']));
    }

    public function testShowByCompanyReturns403()
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
        ]);

        Auth::login($authUser);

        File::factory(10)->create([
            'company_id' => $otherCompany->id,
        ]);

        $controller = new FileController();

        $request = new Request();

        $response = $controller->showByCompany($request, $otherCompany->id);

        $this->assertEquals(403, $response->status());

        $responseData = $response->getData(true);

        $this->assertEquals('You can not see the files of this company', $responseData['message']);
    }

    public function testShowByUserReturnsFiles()
    {
        $company = Company::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
        ]);

        Auth::login($authUser);

        File::factory(10)->create([
            'created_by' => $authUser->id,
        ]);

        $controller = new FileController();

        $request = new Request();

        $response = $controller->showByUser($request, $authUser->id);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals(10, count($responseData['data']));
    }

    public function testShowByUserReturns403()
    {
        $company = Company::factory()->create();

        $authUser = User::factory()->create([
            'company_id' => $company->id,
        ]);

        Auth::login($authUser);

        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        File::factory(10)->create([
            'created_by' => $user->id,
        ]);

        $controller = new FileController();

        $request = new Request();

        $response = $controller->showByUser($request, $user->id + 1);

        $this->assertEquals(403, $response->status());

        $responseData = $response->getData(true);

        $this->assertEquals('You can not see the files of this user', $responseData['message']);
    }


    public function testUploadFileSuccess()
    {
        Storage::fake('local');

        $company = Company::factory()->create();
        $authUser = User::factory()->create(['company_id' => $company->id]);
        $this->actingAs($authUser);

        // Crear archivo simulado
        $uploadedFile = UploadedFile::fake()->create('test.xlsx', 1024, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Crear la solicitud
        $request = new Request([
            'file_type' => 'invoice',
            'file_description' => 'Test description',
        ]);
        $request->files->set('file', $uploadedFile);

        // Llamar al controlador
        $controller = new FileController();
        $response = $controller->uploadFile($request);

        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->status());
        $this->assertEquals('File uploaded successfully', $responseData['message']);

        $this->assertArrayHasKey('id', $responseData['data']);
        $this->assertArrayHasKey('file_name', $responseData['data']);
        $this->assertArrayHasKey('file_extension', $responseData['data']);
        $this->assertArrayHasKey('file_size', $responseData['data']);
        $this->assertArrayHasKey('file_size_type', $responseData['data']);
        $this->assertArrayHasKey('file_mime_type', $responseData['data']);
        $this->assertArrayHasKey('file_description', $responseData['data']);
        $this->assertArrayHasKey('file_status', $responseData['data']);
        $this->assertArrayHasKey('created_by', $responseData['data']);
        $this->assertArrayHasKey('updated_by', $responseData['data']);
        $this->assertArrayHasKey('is_fav', $responseData['data']);
    }

    public function testUploadFileValidationFailed()
    {
        $company = Company::factory()->create();
        $authUser = User::factory()->create(['company_id' => $company->id]);
        $this->actingAs($authUser);

        // Crear la solicitud
        $request = new Request([
            'file_type' => 'invoice',
            'file_description' => 'Test description',
        ]);

        // Llamar al controlador
        $controller = new FileController();
        $response = $controller->uploadFile($request);

        $responseData = $response->getData(true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals('Validation failed', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testUploadFileMaxSizeFailed()
    {
        Storage::fake('local');

        $company = Company::factory()->create();
        $authUser = User::factory()->create(['company_id' => $company->id]);
        $this->actingAs($authUser);

        // Crear archivo simulado
        $uploadedFile = UploadedFile::fake()->create('test.xlsx', 2049, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Crear la solicitud
        $request = new Request([
            'file_type' => 'invoice',
            'file_description' => 'Test description',
        ]);
        $request->files->set('file', $uploadedFile);

        // Llamar al controlador
        $controller = new FileController();
        $response = $controller->uploadFile($request);

        $responseData = $response->getData(true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals('Validation failed', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testUploadFileMimesFailed()
    {
        Storage::fake('local');

        $company = Company::factory()->create();
        $authUser = User::factory()->create(['company_id' => $company->id]);
        $this->actingAs($authUser);

        // Crear archivo simulado
        $uploadedFile = UploadedFile::fake()->create('test.txt', 1024, 'text/plain');

        // Crear la solicitud
        $request = new Request([
            'file_type' => 'invoice',
            'file_description' => 'Test description',
        ]);
        $request->files->set('file', $uploadedFile);

        // Llamar al controlador
        $controller = new FileController();
        $response = $controller->uploadFile($request);

        $responseData = $response->getData(true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals('Validation failed', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testUploadFileSuccessOtherFileType()
    {
        Storage::fake('local');

        $company = Company::factory()->create();
        $authUser = User::factory()->create(['company_id' => $company->id]);
        $this->actingAs($authUser);

        // Crear archivo simulado
        $uploadedFile = UploadedFile::fake()->create('test.xlsx', 1024, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Crear la solicitud
        $request = new Request([
            'file_type' => 'other',
            'file_description' => 'Test description',
        ]);
        $request->files->set('file', $uploadedFile);

        // Llamar al controlador
        $controller = new FileController();
        $response = $controller->uploadFile($request);

        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->status());
        $this->assertEquals('File uploaded successfully', $responseData['message']);

        $this->assertArrayHasKey('id', $responseData['data']);
        $this->assertArrayHasKey('file_name', $responseData['data']);
        $this->assertArrayHasKey('file_extension', $responseData['data']);
        $this->assertArrayHasKey('file_size', $responseData['data']);
        $this->assertArrayHasKey('file_size_type', $responseData['data']);
        $this->assertArrayHasKey('file_mime_type', $responseData['data']);
        $this->assertArrayHasKey('file_description', $responseData['data']);
        $this->assertArrayHasKey('file_status', $responseData['data']);
        $this->assertArrayHasKey('created_by', $responseData['data']);
        $this->assertArrayHasKey('updated_by', $responseData['data']);
        $this->assertArrayHasKey('is_fav', $responseData['data']);
    }

    public function testUploadFileSuccessNoDescription()
    {
        Storage::fake('local');

        $company = Company::factory()->create();
        $authUser = User::factory()->create(['company_id' => $company->id]);
        $this->actingAs($authUser);

        // Crear archivo simulado
        $uploadedFile = UploadedFile::fake()->create('test.xlsx', 1024, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Crear la solicitud
        $request = new Request([
            'file_type' => 'invoice',
        ]);
        $request->files->set('file', $uploadedFile);

        // Llamar al controlador
        $controller = new FileController();
        $response = $controller->uploadFile($request);

        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->status());
        $this->assertEquals('File uploaded successfully', $responseData['message']);

        $this->assertArrayHasKey('id', $responseData['data']);
        $this->assertArrayHasKey('file_name', $responseData['data']);
        $this->assertArrayHasKey('file_extension', $responseData['data']);
        $this->assertArrayHasKey('file_size', $responseData['data']);
        $this->assertArrayHasKey('file_size_type', $responseData['data']);
        $this->assertArrayHasKey('file_mime_type', $responseData['data']);
        $this->assertArrayHasKey('file_description', $responseData['data']);
        $this->assertArrayHasKey('file_status', $responseData['data']);
        $this->assertArrayHasKey('created_by', $responseData['data']);
        $this->assertArrayHasKey('updated_by', $responseData['data']);
        $this->assertArrayHasKey('is_fav', $responseData['data']);
    }

    public function testDeleteFile(){
        $company = Company::factory()->create();
        $authUser = User::factory()->create(['company_id' => $company->id]);
        $this->actingAs($authUser);

        $file = File::factory()->create([
            'company_id' => $company->id,
            'created_by' => $authUser->id,
        ]);

        $controller = new FileController();
        $request = new Request();
        $response = $controller->deleteFile($request, $file->id);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('File deleted successfully', $responseData['message']);
    }

    public function testDownloadFile404(){
        Storage::fake('local');

        $company = Company::factory()->create();
        $authUser = User::factory()->create(['company_id' => $company->id]);
        $this->actingAs($authUser);

        $file = File::factory()->create([
            'company_id' => $company->id,
            'created_by' => $authUser->id,
        ]);

        $controller = new FileController();
        $request = new Request();
        $response = $controller->downloadFile($request, $file->id + 1);

        $this->assertEquals(404, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('File not found', $responseData['message']);
    }

    public function testSearchMethod(){
        $company = Company::factory()->create();
        $authUser = User::factory()->create(['company_id' => $company->id]);
        $this->actingAs($authUser);

        $file = File::factory()->create([
            'company_id' => $company->id,
            'created_by' => $authUser->id,
            'file_name' => 'Test file',
        ]);

        $controller = new FileController();
        $request = new Request([
            'search' => 'Test file',
        ]);
        $response = $controller->index($request);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals(1, count($responseData['data']['data']));
    }


    public function testUpdateFile(){
        $company = Company::factory()->create();
        $authUser = User::factory()->create(['company_id' => $company->id]);
        $this->actingAs($authUser);

        $file = File::factory()->create([
            'company_id' => $company->id,
            'created_by' => $authUser->id,
        ]);

        $controller = new FileController();
        $request = new Request([
            'file_description' => 'Updated description',
            'is_fav' => true,
        ]);
        $response = $controller->updateFile($request, $file->id);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('File updated successfully', $responseData['message']);
    }

    public function testUpdateFile404(){
        $company = Company::factory()->create();
        $authUser = User::factory()->create(['company_id' => $company->id]);
        $this->actingAs($authUser);

        $file = File::factory()->create([
            'company_id' => $company->id,
            'created_by' => $authUser->id,
        ]);

        $controller = new FileController();
        $request = new Request([
            'file_description' => 'Updated description',
            'is_fav' => true,
        ]);
        $response = $controller->updateFile($request, $file->id + 1);

        $this->assertEquals(404, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('File not found', $responseData['message']);
    }

}
