<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->text('address');
            $table->string('postal_code');
            $table->string('city');
            $table->string('country');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('iban')->nullable();
            $table->text('legal_footer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
