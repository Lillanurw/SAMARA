<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique();
            $table->foreignId('visit_plan_id')->unique()->constrained('visit_plans');
            $table->dateTime('actual_start_at');
            $table->dateTime('actual_end_at');
            $table->text('outcome_summary');
            $table->string('outcome_type')->default('NO_CHANGE');
            $table->unsignedTinyInteger('engagement_score')->default(3);
            $table->text('attendance_summary');
            $table->text('barrier')->nullable();
            $table->text('need_or_opportunity')->nullable();
            $table->text('competitor_information')->nullable();
            $table->text('next_step_summary');
            $table->boolean('follow_up_required')->default(false);
            $table->foreignId('submitted_by')->constrained('users');
            $table->dateTime('submitted_at');
            $table->string('submit_status')->default('SUBMITTED');
            $table->text('correction_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_reports');
    }
};
