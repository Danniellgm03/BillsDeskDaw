<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id'); // Relación con empresas
            $table->unsignedBigInteger('user_id'); // Relación con usuarios
            $table->unsignedBigInteger('file_id'); // Relación con la tabla files
            $table->enum('status', ['pending', 'corrected', 'rejected'])->default('pending');
            $table->string('currency', 10)->nullable(); // Moneda de la factura
            $table->decimal('total_amount', 15, 2)->nullable(); // Total original de la factura
            $table->decimal('saved_amount', 15, 2)->nullable(); // Ahorro tras correcciones
            $table->string('template_id'); // Relación con MongoDB para la plantilla
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
