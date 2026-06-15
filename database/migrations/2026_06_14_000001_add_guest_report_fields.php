<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lost_items', function (Blueprint $table) {
            $table->string('guest_name')->nullable()->after('user_id');
            $table->string('guest_contact')->nullable()->after('guest_name');
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('found_items', function (Blueprint $table) {
            $table->string('guest_name')->nullable()->after('staff_id');
            $table->string('guest_contact')->nullable()->after('guest_name');
            $table->foreignId('staff_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('lost_items', function (Blueprint $table) {
            $table->dropColumn(['guest_name', 'guest_contact']);
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('found_items', function (Blueprint $table) {
            $table->dropColumn(['guest_name', 'guest_contact']);
            $table->foreignId('staff_id')->nullable(false)->change();
        });
    }
};
