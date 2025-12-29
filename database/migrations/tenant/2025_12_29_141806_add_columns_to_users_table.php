<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete()->nullOnUpdate();
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete()->nullOnUpdate();
            $table->enum('language', ['en', 'ar'])->default('en');
            $table->string('jwt_version')->nullable();
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['branch_id', 'parent_id', 'language', 'jwt_version']);
            $table->dropSoftDeletes();
        });
    }
};
