<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('link_hits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')->constrained('links')->cascadeOnUpdate()->cascadeOnDelete();
            $table->ipAddress('ip')->nullable(false);
            $table->text('user_agent')->nullable(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('link_hits', function (Blueprint $table) {
            $table->dropForeign(['link_id']);
            $table->drop();
        });
    }
};
