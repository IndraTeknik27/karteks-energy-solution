<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('avatar')->nullable()->after('phone_verified_at');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('avatar');
            $table->date('birth_date')->nullable()->after('gender');
            $table->boolean('is_active')->default(true)->after('birth_date');
            $table->timestamp('last_login_at')->nullable()->after('is_active');

            $table->unique('phone');
            $table->index('is_active');
            $table->index('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['last_login_at']);
            $table->dropColumn([
                'phone',
                'phone_verified_at',
                'avatar',
                'gender',
                'birth_date',
                'is_active',
                'last_login_at',
            ]);
        });
    }
};