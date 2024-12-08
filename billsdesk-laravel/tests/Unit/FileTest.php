<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\File;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreateFile(): void
    {
        $file = File::factory()->create([
            'file_name' => 'example.txt',
            'file_type' => 'invoice',
            'file_status' => 'active',
        ]);

        $this->assertDatabaseHas('files', [
            'file_name' => 'example.txt',
            'file_type' => 'invoice',
            'file_status' => 'active',
        ]);

        $this->assertEquals('example.txt', $file->file_name);
        $this->assertEquals('invoice', $file->file_type);
        $this->assertEquals('active', $file->file_status);
    }

    public function testFileBelongsToCompany(): void
    {
        $company = Company::factory()->create(['name' => 'Test Company']);
        $file = File::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(Company::class, $file->company);
        $this->assertEquals('Test Company', $file->company->name);
    }

    public function testFileBelongsToCreatedByUser(): void
    {
        $user = User::factory()->create(['name' => 'Creator User']);
        $file = File::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $file->createdBy);
        $this->assertEquals('Creator User', $file->createdBy->name);
    }

    public function testFileBelongsToUpdatedByUser(): void
    {
        $user = User::factory()->create(['name' => 'Updater User']);
        $file = File::factory()->create(['updated_by' => $user->id]);

        $this->assertInstanceOf(User::class, $file->updatedBy);
        $this->assertEquals('Updater User', $file->updatedBy->name);
    }

    public function testFileBelongsToDeletedByUser(): void
    {
        $user = User::factory()->create(['name' => 'Deleter User']);
        $file = File::factory()->create(['deleted_by' => $user->id]);

        $this->assertInstanceOf(User::class, $file->deletedBy);
        $this->assertEquals('Deleter User', $file->deletedBy->name);
    }

    public function testFileIsFav(): void
    {
        $file = File::factory()->create(['is_fav' => true]);

        $this->assertTrue($file->is_fav);
    }

    public function testFileIsNotFav(): void
    {
        $file = File::factory()->create(['is_fav' => false]);

        $this->assertFalse($file->is_fav);
    }

    public function testFileIsNotFavByDefault(): void
    {
        $file = File::factory()->create();

        $this->assertFalse($file->is_fav);
    }

    public function testFileHasSoftDeletes(): void
    {
        $file = File::factory()->create();

        $file->delete();

        $this->assertSoftDeleted($file);
    }

    public function testFileHasTimestamps(): void
    {
        $file = File::factory()->create();

        $this->assertNotNull($file->created_at);
        $this->assertNotNull($file->updated_at);
    }

    public function testFileHasFileDescription(): void
    {
        $file = File::factory()->create(['file_description' => 'This is a test file']);

        $this->assertEquals('This is a test file', $file->file_description);
    }

    public function testFileHasFileMimeType(): void
    {
        $file = File::factory()->create(['file_mime_type' => 'text/plain']);

        $this->assertEquals('text/plain', $file->file_mime_type);
    }

    public function testCreateFileWithAllData(){
        $company = Company::factory()->create(['name' => 'Test Company']);
        $user = User::factory()->create(['name' => 'Creator User']);
        $file = File::factory()->create([
            'company_id' => $company->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'deleted_by' => $user->id,
            'file_name' => 'example.txt',
            'file_type' => 'invoice',
            'file_status' => 'active',
            'file_path' => '/path/to/file',
            'file_extension' => 'txt',
            'file_size' => '1024',
            'file_size_type' => 'KB',
            'file_mime_type' => 'text/plain',
            'file_description' => 'This is a test file',
            'is_fav' => true,
        ]);

        $this->assertDatabaseHas('files', [
            'company_id' => $company->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'deleted_by' => $user->id,
            'file_name' => 'example.txt',
            'file_type' => 'invoice',
            'file_status' => 'active',
            'file_path' => '/path/to/file',
            'file_extension' => 'txt',
            'file_size' => '1024',
            'file_size_type' => 'KB',
            'file_mime_type' => 'text/plain',
            'file_description' => 'This is a test file',
            'is_fav' => true,
        ]);

        $this->assertEquals('example.txt', $file->file_name);
        $this->assertEquals('invoice', $file->file_type);
        $this->assertEquals('active', $file->file_status);
        $this->assertEquals('/path/to/file', $file->file_path);
        $this->assertEquals('txt', $file->file_extension);
        $this->assertEquals('1024', $file->file_size);
        $this->assertEquals('KB', $file->file_size_type);
        $this->assertEquals('text/plain', $file->file_mime_type);
        $this->assertEquals('This is a test file', $file->file_description);
        $this->assertTrue($file->is_fav);
    }

    public function testDeleteOnCascadeCompany(): void
    {
        $company = Company::factory()->create();
        $file = File::factory()->create(['company_id' => $company->id]);

        $company->delete();

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);

        $this->assertDatabaseMissing('files', ['id' => $file->id]);
    }

    public function testDeleteOnCascadeCreatedByUser(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['created_by' => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        $this->assertDatabaseMissing('files', ['id' => $file->id]);
    }

    public function testDeleteOnCascadeUpdatedByUser(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['updated_by' => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        $this->assertDatabaseMissing('files', ['id' => $file->id]);
    }

    public function testCreateFileWithoutName(): void
    {
        $this->expectException(\Exception::class);

        File::factory()->create(['file_name' => null]);
    }


}
