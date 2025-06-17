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
        Schema::table('mfa', function (Blueprint $table) {
            $table->boolean('extended_mfa_enabled')->default(false);
            $table->string('extended_mfa_method')->nullable(); // 'email', 'whatsapp', 'google_auth', dll
        });
    }

    public function down(): void
    {
        Schema::table('mfa', function (Blueprint $table) {
            $table->dropColumn(['extended_mfa_enabled', 'extended_mfa_method']);
        });
    }

};
