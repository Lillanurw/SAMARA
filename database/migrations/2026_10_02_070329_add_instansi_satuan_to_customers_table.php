<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('instansi')->nullable()->after('customer_name');
            $table->string('satuan')->nullable()->after('instansi');
            $table->string('contact_pangkat')->nullable()->after('contact_name');
            $table->string('contact_letting')->nullable()->after('contact_position');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['instansi', 'satuan', 'contact_pangkat', 'contact_letting']);
        });
    }
};
