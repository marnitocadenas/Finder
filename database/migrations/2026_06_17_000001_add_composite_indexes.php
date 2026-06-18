<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('found_items', function (Blueprint $table) {
            $table->index(['staff_id', 'status']);
        });

        Schema::table('lost_items', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
        });

        Schema::table('claims', function (Blueprint $table) {
            $table->index(['student_id', 'status']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::table('found_items', function (Blueprint $table) {
            $table->dropIndex(['staff_id', 'status']);
        });

        Schema::table('lost_items', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('claims', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'status']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_read']);
        });
    }
};
