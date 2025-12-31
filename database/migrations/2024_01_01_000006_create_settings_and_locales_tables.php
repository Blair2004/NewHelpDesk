<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique();
            $table->string('name');
            $table->string('native_name');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
        });

        Schema::create('user_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('external_license_id')->nullable();
            $table->string('product_name');
            $table->string('item_id')->nullable();
            $table->string('domain')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('item_id');
            $table->index(['user_id', 'external_license_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_licenses');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('locales');
    }
};
