<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->string('role')->default('reporter')->index());
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('path')->unique();
            $table->string('original_name');
            $table->string('mime', 80);
            $table->unsignedBigInteger('size');
            $table->string('hash', 64)->unique();
            $table->json('alt')->nullable();
            $table->text('credit')->nullable();
            $table->timestamps();
        });
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('media_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->json('title');
            $table->json('summary')->nullable();
            $table->json('body')->nullable();
            $table->string('slug')->unique();
            $table->string('category')->index();
            $table->string('district')->nullable()->index();
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_bn_at')->nullable()->index();
            $table->timestamp('published_en_at')->nullable()->index();
            $table->string('demo_image')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->text('editorial_notes')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('article_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('snapshot');
            $table->string('event');
            $table->timestamps();
        });
        Schema::create('frontpages', function (Blueprint $table) {
            $table->id();
            $table->string('state')->index();
            $table->json('layout');
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('locale', 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['newsletter_subscribers', 'frontpages', 'article_revisions', 'articles', 'media'] as $name) {
            Schema::dropIfExists($name);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('role'));
    }
};
