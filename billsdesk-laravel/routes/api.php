<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckPermission;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceTemplateController;
use App\Http\Controllers\CorrectionRuleController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/registerWithInvitation', [AuthController::class, 'registerWithInvitation']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('/company/users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware(CheckPermission::class.':manage_users');
        Route::post('/', [UserController::class, 'store']);
        Route::get('{id}', [UserController::class, 'show']);
        Route::put('{id}', [UserController::class, 'update']);
        Route::delete('{id}', [UserController::class, 'destroy']);
    });

    Route::prefix('/me/profile')->group(function () {
        Route::get('/', [UserController::class, 'meProfile']);
        Route::put('/', [UserController::class, 'updateProfile']);
    });

    Route::prefix('/roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);
        Route::get('{id}', [RoleController::class, 'show']);
        Route::put('{id}', [RoleController::class, 'update']);
        Route::delete('{id}', [RoleController::class, 'destroy']);
    });

    Route::prefix('/companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::get('{id}', [CompanyController::class, 'show']);
        Route::put('{id}', [CompanyController::class, 'update']);
        Route::delete('{id}', [CompanyController::class, 'destroy']);
    });

    Route::prefix('/me/companies')->group(function () {
        Route::get('/', [CompanyController::class, 'meCompany']);
    });


    Route::prefix('/company/invitations')->group(function () {
        Route::post('/', [InvitationController::class, 'sendInvitation']);
        Route::get('/', [InvitationController::class, 'index']);
        Route::post('/resend', [InvitationController::class, 'resendInvitation']);
        Route::post('/cancel', [InvitationController::class, 'cancelInvitation']);
    });

    Route::prefix('/files')->group(function () {
        Route::get('/', [FileController::class, 'index']);
        Route::post('/', [FileController::class, 'uploadFile']);
        Route::get('/search', [FileController::class, 'search']);
        Route::get('{id}', [FileController::class, 'show']);
        Route::get('/company/{company_id}', [FileController::class, 'showByCompany']);
        Route::get('/user/{user_id}', [FileController::class, 'showByUser']);
        Route::delete('{id}', [FileController::class, 'deleteFile']);
        Route::get('/{id}/download', [FileController::class, 'downloadFile']);
        Route::put('{id}', [FileController::class, 'updateFile']);
        Route::get('/{id}/getHeaders', [FileController::class, 'getCsvHeaders']);
    });


    Route::prefix('/company/invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::post('/', [InvoiceController::class, 'create']);
        Route::get('{id}', [InvoiceController::class, 'show']);
        Route::put('{id}', [InvoiceController::class, 'update']);
        Route::get('{id}/template', [InvoiceController::class, 'getTemplate']);
        Route::get('/template/{templateId}/correction-rules', [InvoiceController::class, 'getCorrectionRules']);
        Route::get('/process/{id}', [InvoiceController::class, 'processInvoice']);
        Route::get('/process/{id}/download', [InvoiceController::class, 'processInvoiceDonwloade']);
    });

    Route::prefix('/company/invoice-templates')->group(function () {
        Route::get('/', [InvoiceTemplateController::class, 'index']);
        Route::post('/', [InvoiceTemplateController::class, 'create']);
        Route::get('{id}', [InvoiceTemplateController::class, 'show']);
        Route::put('{id}', [InvoiceTemplateController::class, 'update']);
    });

    Route::prefix('/company/correction-rules')->group(function () {
        Route::get('/', [CorrectionRuleController::class, 'index']);
        Route::post('/', [CorrectionRuleController::class, 'create']);
        Route::get('{id}', [CorrectionRuleController::class, 'show']);
        Route::put('{id}', [CorrectionRuleController::class, 'update']);
        Route::delete('{id}', [CorrectionRuleController::class, 'destroy']);
    });
});

Route::prefix('/company/invitations')->group(function () {
    Route::get('/{token}', [InvitationController::class, 'getInvitations']);
});

?>
