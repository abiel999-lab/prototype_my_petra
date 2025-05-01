<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mfa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // MFA Core
            $table->boolean('mfa_enabled')->default(false);
            $table->string('mfa_method')->default('email');
            $table->text('two_factor_code')->nullable();
            $table->dateTime('otp_expires_at')->nullable();
            $table->string('google2fa_secret')->nullable();

            // Passwordless Login
            $table->boolean('passwordless_enabled')->default(false);
            $table->string('passwordless_token')->nullable();
            $table->timestamp('passwordless_expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mfa');
    }
};

