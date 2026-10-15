<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('honorific', 20)->nullable()->after('full_name');
        });

        // Update data user Dirut secara idempoten jika tepat satu user ber-role DIREKTUR_UTAMA
        $dirutUsers = DB::table('users')->where('role', 'DIREKTUR_UTAMA')->get();
        if ($dirutUsers->count() === 1) {
            DB::table('users')->where('id', $dirutUsers->first()->id)->update([
                'honorific' => 'Bpk',
                'full_name' => 'Irwan Wardianto',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('honorific');
        });
    }
};
