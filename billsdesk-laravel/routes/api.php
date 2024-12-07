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
use App\Http\Controllers\ContactController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/registerWithInvitation', [AuthController::class, 'registerWithInvitation']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('isValidTokenResetPassword', [AuthController::class, 'isValidTokenResetPassword']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('/company/users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware(CheckPermission::class.':manage_users,view_users');
        Route::post('/', [UserController::class, 'store'])->middleware(CheckPermission::class.':manage_users');
        Route::get('{id}', [UserController::class, 'show'])->middleware(CheckPermission::class.':manage_users,view_users');
        Route::put('{id}', [UserController::class, 'update'])->middleware(CheckPermission::class.':manage_users');
        Route::delete('{id}', [UserController::class, 'destroy'])->middleware(CheckPermission::class.':manage_users');
    });

    Route::prefix('/me/profile')->group(function () {
        Route::get('/', [UserController::class, 'meProfile']);
        Route::put('/', [UserController::class, 'updateProfile']);
    });

    Route::prefix('/roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->middleware(CheckPermission::class.':manage_roles,view_roles');
        Route::post('/', [RoleController::class, 'store'])->middleware(CheckPermission::class.':manage_roles');
        Route::get('{id}', [RoleController::class, 'show'])->middleware(CheckPermission::class.':manage_roles,view_roles');
        Route::put('{id}', [RoleController::class, 'update'])->middleware(CheckPermission::class.':manage_roles');
        Route::delete('{id}', [RoleController::class, 'destroy'])->middleware(CheckPermission::class.':manage_roles');
    });

    Route::prefix('/companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index'])->middleware(CheckPermission::class.':manage_companies,view_companies');
        Route::post('/', [CompanyController::class, 'store'])->middleware(CheckPermission::class.':manage_companies');
        Route::get('{id}', [CompanyController::class, 'show'])->middleware(CheckPermission::class.':manage_companies,view_companies');
        Route::put('{id}', [CompanyController::class, 'update'])->middleware(CheckPermission::class.':manage_companies');
        Route::delete('{id}', [CompanyController::class, 'destroy'])->middleware(CheckPermission::class.':manage_companies');
    });

    Route::prefix('/me/companies')->group(function () {
        Route::get('/', [CompanyController::class, 'meCompany'])->middleware(CheckPermission::class.':manage_companies,view_companies');
    });


    Route::prefix('/company/invitations')->group(function () {
        Route::post('/', [InvitationController::class, 'sendInvitation'])->middleware(CheckPermission::class.':manage_invitations');
        Route::get('/', [InvitationController::class, 'index'])->middleware(CheckPermission::class.':manage_invitations');
        Route::post('/resend', [InvitationController::class, 'resendInvitation'])->middleware(CheckPermission::class.':manage_invitations');
        Route::post('/cancel', [InvitationController::class, 'cancelInvitation'])->middleware(CheckPermission::class.':manage_invitations');
    });

    Route::prefix('/files')->group(function () {
        Route::get('/', [FileController::class, 'index'])->middleware(CheckPermission::class.':manage_files,view_files');
        Route::post('/', [FileController::class, 'uploadFile'])->middleware(CheckPermission::class.':manage_files');
        Route::get('/search', [FileController::class, 'search'])->middleware(CheckPermission::class.':manage_files,view_files');
        Route::get('{id}', [FileController::class, 'show'])->middleware(CheckPermission::class.':manage_files,view_files');
        Route::get('/company/{company_id}', [FileController::class, 'showByCompany'])->middleware(CheckPermission::class.':manage_files,view_files');
        Route::get('/user/{user_id}', [FileController::class, 'showByUser'])->middleware(CheckPermission::class.':manage_files,view_files');
        Route::delete('{id}', [FileController::class, 'deleteFile'])->middleware(CheckPermission::class.':manage_files');
        Route::get('/{id}/download', [FileController::class, 'downloadFile'])->middleware(CheckPermission::class.':manage_files,view_files');
        Route::put('{id}', [FileController::class, 'updateFile'])->middleware(CheckPermission::class.':manage_files');
        Route::get('/{id}/getHeaders', [FileController::class, 'getCsvHeaders'])->middleware(CheckPermission::class.':manage_files');
    });


    Route::prefix('/company/invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->middleware(CheckPermission::class.':manage_invoices,view_invoices');
        Route::post('/', [InvoiceController::class, 'create'])->middleware(CheckPermission::class.':manage_invoices');
        Route::get('{id}', [InvoiceController::class, 'show'])->middleware(CheckPermission::class.':manage_invoices,view_invoices');
        Route::put('{id}', [InvoiceController::class, 'update'])->middleware(CheckPermission::class.':manage_invoices');
        Route::get('{id}/template', [InvoiceController::class, 'getTemplate'])->middleware(CheckPermission::class.':manage_invoices,view_invoices');
        Route::get('/template/{templateId}/correction-rules', [InvoiceController::class, 'getCorrectionRules'])->middleware(CheckPermission::class.':manage_invoices,view_invoices');
        Route::get('/process/{id}', [InvoiceController::class, 'processInvoice'])->middleware(CheckPermission::class.':manage_invoices');
        Route::get('/process/{id}/download', [InvoiceController::class, 'processInvoiceDonwloade'])->middleware(CheckPermission::class.':manage_invoices');
    });

    Route::prefix('/company/invoice-templates')->group(function () {
        Route::get('/', [InvoiceTemplateController::class, 'index'])->middleware(CheckPermission::class.':manage_invoice_templates,view_invoice_templates,manage_invoices');
        Route::post('/', [InvoiceTemplateController::class, 'create'])->middleware(CheckPermission::class.':manage_invoice_templates,manage_invoices');
        Route::get('{id}', [InvoiceTemplateController::class, 'show'])->middleware(CheckPermission::class.':manage_invoice_templates,view_invoice_templates,manage_invoices');
        Route::put('{id}', [InvoiceTemplateController::class, 'update'])->middleware(CheckPermission::class.':manage_invoice_templates,manage_invoices');
    });

    Route::prefix('/company/correction-rules')->group(function () {
        Route::get('/', [CorrectionRuleController::class, 'index'])->middleware(CheckPermission::class.':manage_correction_rules,view_correction_rules,manage_invoices');
        Route::post('/', [CorrectionRuleController::class, 'create'])->middleware(CheckPermission::class.':manage_correction_rules,manage_invoices');
        Route::get('{id}', [CorrectionRuleController::class, 'show'])->middleware(CheckPermission::class.':manage_correction_rules,view_correction_rules,manage_invoices');
        Route::put('{id}', [CorrectionRuleController::class, 'update'])->middleware(CheckPermission::class.':manage_correction_rules,manage_invoices');
        Route::delete('{id}', [CorrectionRuleController::class, 'destroy'])->middleware(CheckPermission::class.':manage_correction_rules,manage_invoices');
    });

    Route::prefix('/company/contacts')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [ContactController::class, 'index'])->middleware(CheckPermission::class.':manage_contacts,view_contacts');
        Route::post('/', [ContactController::class, 'store'])->middleware(CheckPermission::class.':manage_contacts');
        Route::get('/{contactId}', [ContactController::class, 'show'])->middleware(CheckPermission::class.':manage_contacts,view_contacts');
        Route::put('/{contactId}', [ContactController::class, 'update'])->middleware(CheckPermission::class.':manage_contacts');
        Route::delete('/{contactId}', [ContactController::class, 'destroy'])->middleware(CheckPermission::class.':manage_contacts');
    });
});

Route::prefix('/company/invitations')->group(function () {
    Route::get('/{token}', [InvitationController::class, 'getInvitations']);
});

?>
