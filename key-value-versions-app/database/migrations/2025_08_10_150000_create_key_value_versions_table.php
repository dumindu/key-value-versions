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
        Schema::create('key_value_versions', function (Blueprint $table) {
            $table->id();
            $table->text('key');
            $table->text('value');
            $table->text('value_hash');
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['key'], 'idx_kvv_key');
            $table->index(['key', 'created_at'], 'idx_kvv_key_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('key_value_versions');
    }
};
