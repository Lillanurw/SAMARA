<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_plan_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_plan_id')->constrained('visit_plans')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unique(['visit_plan_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_plan_members');
    }
};
