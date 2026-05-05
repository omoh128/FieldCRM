<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->string('assigned_to')->nullable();
            $table->string('title');
            $table->string('type')->nullable();
            $table->string('status')->default('Scheduled');
            $table->date('scheduled_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('value', 12, 2)->default(0);
            $table->integer('progress')->default(0);
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
