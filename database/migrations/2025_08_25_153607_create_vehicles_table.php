<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('external_id')->nullable()->index();
            $table->timestamp('external_updated_at')->nullable();

            $table->string('type', 50)->nullable();
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->string('version', 255)->nullable();

            $table->json('year')->nullable();
            $table->text('optionals_json')->nullable();
            $table->text('fotos_json')->nullable();

            $table->unsignedTinyInteger('doors')->nullable();

            $table->string('board', 20)->nullable();
            $table->string('chassi', 64)->nullable();
            $table->string('transmission', 50)->nullable();

            $table->integer('km')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('old_price', 12, 2)->nullable();

            $table->string('color', 50)->nullable();
            $table->string('fuel', 50)->nullable();

            $table->boolean('sold')->default(false);
            $table->string('category', 100)->nullable();
            $table->string('url_car', 255)->nullable();

            $table->string('source', 20)->default('local');
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
