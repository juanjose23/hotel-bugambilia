<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promocion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promocion_id')
                ->constrained('promociones')
                ->cascadeOnDelete();
            $table->string('item_type');
            $table->unsignedBigInteger('item_id');
            $table->decimal('precio_especial', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['item_type', 'item_id']);
            $table->index('promocion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promocion_items');
    }
};
