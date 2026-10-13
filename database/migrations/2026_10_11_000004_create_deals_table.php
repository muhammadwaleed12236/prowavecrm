<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Closed by agent
            $table->string('client_name');
            $table->string('project_title');
            $table->text('project_scope')->nullable();
            $table->decimal('deal_amount', 12, 2);
            $table->string('currency', 10)->default('USD');
            $table->date('closed_date');
            $table->enum('status', ['won', 'in_progress', 'completed'])->default('won');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
