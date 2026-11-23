<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ho_so_ung_vien') && ! Schema::hasColumn('ho_so_ung_vien', 'diem_danh_gia')) {
            Schema::table('ho_so_ung_vien', function (Blueprint $table) {
                $table->decimal('diem_danh_gia', 3, 1)->default(0.0)->after('trang_thai_ho_so');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ho_so_ung_vien') && Schema::hasColumn('ho_so_ung_vien', 'diem_danh_gia')) {
            Schema::table('ho_so_ung_vien', function (Blueprint $table) {
                $table->dropColumn('diem_danh_gia');
            });
        }
    }
};
