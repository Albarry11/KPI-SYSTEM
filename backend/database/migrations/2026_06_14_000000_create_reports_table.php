<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('period_id');
            $table->unsignedBigInteger('manager_id');
            $table->string('title');
            $table->text('summary');
            $table->decimal('avg_score', 5, 2);
            $table->unsignedBigInteger('top_performer_id')->nullable();
            $table->unsignedBigInteger('lowest_performer_id')->nullable();
            $table->integer('total_employees');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('period_id')->references('id')->on('periods')->onDelete('cascade');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('top_performer_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('lowest_performer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
