<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shopping_lists', function (Blueprint $table): void {
            $table->unsignedBigInteger('folder_id')->nullable()->change();
            $table->dropForeign(['folder_id']);
            $table->foreign('folder_id')->references('id')->on('folders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shopping_lists', function (Blueprint $table): void {
            $table->unsignedBigInteger('folder_id')->nullable(false)->change();
            $table->dropForeign(['folder_id']);
            $table->foreign('folder_id')->references('id')->on('folders')->cascadeOnDelete();
        });
    }
};
