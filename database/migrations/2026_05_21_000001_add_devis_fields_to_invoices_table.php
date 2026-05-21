<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('source_document_id')
                ->nullable()
                ->after('id')
                ->constrained('invoices')
                ->nullOnDelete();

            $table->string('type', 16)->default('invoice')->after('client_id')->index();

            $table->date('valid_until')->nullable()->after('due_date');
            $table->date('estimated_start_date')->nullable()->after('valid_until');
            $table->timestamp('accepted_at')->nullable()->after('paid_at');

            $table->text('chantier_address')->nullable()->after('footer');
            $table->text('payment_conditions')->nullable()->after('chantier_address');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status', 32)->default('draft')->change();
            $table->date('due_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['source_document_id']);
            $table->dropColumn([
                'source_document_id',
                'type',
                'valid_until',
                'estimated_start_date',
                'accepted_at',
                'chantier_address',
                'payment_conditions',
            ]);
        });
    }
};
