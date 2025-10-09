<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            // App auth identity (likely already there):
            // $table->string('name')->nullable();
            // $table->string('email')->unique();
            // $table->string('password'); // Laravel's auth hash (NOT your E2EE key)

            // Zero-knowledge key wrapping (all set by the *client*)
            $table->binary('eak')->nullable();              // Encrypted Account Key
            $table->binary('kdf_salt')->nullable();
            $table->json('kdf_params')->nullable();         // { algo, mem, iters, parallelism, ... }
            $table->string('crypto_version')->default('v1');

            // Optional user-held recovery
            $table->binary('eak_recovery')->nullable();
            $table->json('recovery_meta')->nullable();

            // Future: PAKE/OPAQUE envelope (server-side verifier, NOT a password)
            $table->binary('opaque_record')->nullable();

            $table->index(['crypto_version']);
        });
    }

    public function down(): void {
    }
};
