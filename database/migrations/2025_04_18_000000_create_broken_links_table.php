<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrokenLinksTable extends Migration
{
    public function up(): void
    {
        Schema::create('broken_links', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->integer('status_code');
            $table->string('reason')->nullable();
            $table->text('link_text')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broken_links');
    }
}
