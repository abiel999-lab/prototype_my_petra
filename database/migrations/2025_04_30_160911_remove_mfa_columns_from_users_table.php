<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_code',
                'mfa_enabled',
                'mfa_method',
                'google2fa_secret',
                'passwordless_enabled',
                'passwordless_token',
                'passwordless_expires_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_code')->nullable();
            $table->boolean('mfa_enabled')->default(false);
            $table->string('mfa_method')->default('email');
            $table->string('google2fa_secret')->nullable();
            $table->boolean('passwordless_enabled')->default(false);
            $table->string('passwordless_token')->nullable();
            $table->timestamp('passwordless_expires_at')->nullable();
        });
    }
};

