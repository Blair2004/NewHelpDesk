<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('locale', 5);
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->enum('status', ['open', 'pending', 'resolved', 'closed'])->default('open');
            $table->boolean('is_locked')->default(false);
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('license_id')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->timestamp('last_reply_at')->nullable();
            $table->timestamp('last_user_reply_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'is_locked']);
            $table->index('last_reply_at');
        });

        Schema::create('thread_tag', function (Blueprint $table) {
            $table->foreignId('thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['thread_id', 'tag_id']);
        });

        Schema::create('thread_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            
            $table->unique(['thread_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thread_participants');
        Schema::dropIfExists('thread_tag');
        Schema::dropIfExists('threads');
    }
};
