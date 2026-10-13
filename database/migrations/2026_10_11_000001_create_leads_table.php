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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('client_name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('country')->nullable();
            $table->string('service_needed')->nullable();
            $table->string('source')->default('Cold Calling'); // Cold Calling, LinkedIn, Website, Referral, etc.
            $table->enum('status', [
                'new',
                'contacted',
                'interested',
                'callback',
                'meeting_scheduled',
                'proposal_sent',
                'deal_won',
                'lost',
                'not_interested'
            ])->default('new');
            $table->decimal('budget', 12, 2)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->text('notes')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
