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
        if (Schema::hasTable('nguoi_dung') && ! Schema::hasColumn('nguoi_dung', 'remember_token')) {
            Schema::table('nguoi_dung', function (Blueprint $table) {
                $table->rememberToken()->after('trang_thai');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('nguoi_dung') && Schema::hasColumn('nguoi_dung', 'remember_token')) {
            Schema::table('nguoi_dung', function (Blueprint $table) {
                $table->dropRememberToken();
            });
        }
    }
};
