<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shopping_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shopping_list_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('quantity_type')->nullable();
            $table->unsignedInteger('price')->nullable();
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_done')->default(false);
            $table->string('file')->nullable();
            $table->unsignedInteger('order_column');
            $table->timestamps();

            $table->index('shopping_list_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopping_items');
    }
};
