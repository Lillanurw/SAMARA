<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_number')->unique()->nullable();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('area_id')->constrained('areas');
            $table->foreignId('owner_id')->constrained('users');
            $table->string('plan_month', 7); // e.g. 2026-09
            $table->date('planned_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('activity_type')->default('SALES_VISIT');
            $table->string('priority')->default('MEDIUM');
            $table->text('monthly_objective')->nullable();
            $table->text('specific_objective');
            $table->text('resource_notes')->nullable();
            $table->string('location_text')->nullable();
            $table->string('status')->default('PLANNED');
            $table->foreignId('rescheduled_from_id')->nullable()->constrained('visit_plans')->onDelete('set null');
            $table->text('cancellation_reason')->nullable();
            $table->dateTime('actual_start_at')->nullable();
            $table->dateTime('actual_end_at')->nullable();
            $table->foreignId('started_by')->nullable()->constrained('users');
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_plans');
    }
};
