<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_status_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('company_name');
            $table->string('location_city')->nullable();
            $table->unsignedInteger('salary')->nullable();
            $table->string('job_url')->nullable();
            $table->string('resume_version_url')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
            $table->index('job_status_id');
            $table->index('job_category_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
