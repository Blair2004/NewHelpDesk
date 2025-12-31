<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thread_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->enum('author_type', ['user', 'agent', 'system'])->default('user');
            $table->text('content');
            $table->string('locale', 5);
            $table->enum('visibility', ['public', 'sensitive'])->default('public');
            $table->foreignId('original_ref')->nullable()->constrained('thread_messages')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['thread_id', 'locale']);
            $table->index('original_ref');
        });

        Schema::create('message_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('thread_messages')->cascadeOnDelete();
            $table->text('old_content');
            $table->text('new_content');
            $table->foreignId('revised_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('thread_messages')->cascadeOnDelete();
            $table->string('filename');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_attachments');
        Schema::dropIfExists('message_revisions');
        Schema::dropIfExists('thread_messages');
    }
};
