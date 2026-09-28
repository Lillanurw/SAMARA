<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('director_inputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_report_id')->nullable()->constrained('visit_reports');
            $table->foreignId('visit_plan_id')->nullable()->constrained('visit_plans');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->foreignId('area_id')->nullable()->constrained('areas');
            $table->string('topic');
            $table->string('input_type')->default('STRATEGIC_DIRECTION');
            $table->text('direction_text');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->string('priority')->default('MEDIUM');
            $table->date('due_date')->nullable();
            $table->string('status')->default('OPEN');
            $table->dateTime('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users');
            $table->dateTime('started_at')->nullable();
            $table->text('completion_note')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('director_inputs');
    }
};
