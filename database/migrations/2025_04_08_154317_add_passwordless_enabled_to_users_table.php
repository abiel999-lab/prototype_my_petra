<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'passwordless_enabled')) {
                $table->boolean('passwordless_enabled')
                    ->default(false)
                    ->before('mfa_enabled');
            }

            if (!Schema::hasColumn('users', 'passwordless_token')) {
                $table->string('passwordless_token')
                    ->nullable()
                    ->after('remember_token');
            }

            if (!Schema::hasColumn('users', 'passwordless_expires_at')) {
                $table->timestamp('passwordless_expires_at')
                    ->nullable()
                    ->after('passwordless_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'passwordless_enabled')) {
                $table->dropColumn('passwordless_enabled');
            }
            if (Schema::hasColumn('users', 'passwordless_token')) {
                $table->dropColumn('passwordless_token');
            }
            if (Schema::hasColumn('users', 'passwordless_expires_at')) {
                $table->dropColumn('passwordless_expires_at');
            }
        });
    }
};
