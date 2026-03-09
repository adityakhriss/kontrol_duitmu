<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_financial_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 50)->default('openrouter');
            $table->string('model')->nullable();
            $table->date('snapshot_month');
            $table->date('analysis_period_start');
            $table->date('analysis_period_end');
            $table->string('status', 20)->default('generated');
            $table->text('analysis')->nullable();
            $table->text('recommendations')->nullable();
            $table->json('input_summary')->nullable();
            $table->json('output_payload')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'snapshot_month']);
            $table->index(['user_id', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_financial_insights');
    }
};
