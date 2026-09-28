<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->string('action_number')->unique();
            $table->foreignId('visit_plan_id')->constrained('visit_plans');
            $table->foreignId('visit_report_id')->nullable()->constrained('visit_reports');
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('action_title');
            $table->text('action_detail');
            $table->foreignId('owner_id')->constrained('users');
            $table->date('due_date');
            $table->string('priority')->default('MEDIUM');
            $table->string('status')->default('OPEN');
            $table->text('completion_note')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('blocked_reason')->nullable();
            $table->date('rescheduled_to')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
