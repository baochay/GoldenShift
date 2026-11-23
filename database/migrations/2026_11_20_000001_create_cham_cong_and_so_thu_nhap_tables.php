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
        if (! Schema::hasTable('lich_su_cham_cong')) {
            Schema::create('lich_su_cham_cong', function (Blueprint $table) {
                $table->increments('ma_cham_cong');
                $table->integer('ma_ung_tuyen');
                $table->integer('ma_nguoi_dung');
                $table->integer('ma_ca_lam');
                $table->dateTime('thoi_gian_vao_thuc_te')->nullable();
                $table->dateTime('thoi_gian_ra_thuc_te')->nullable();
                $table->decimal('so_gio_lam_thuc_te', 5, 2)->default(0.00);
                $table->string('trang_thai_cham_cong', 30)->default('chua_vao_ca');
                $table->string('hinh_thuc_xac_thuc', 30)->default('ma_qr');
                $table->text('ghi_chu_cham_cong')->nullable();
                $table->integer('nguoi_xac_nhan')->nullable();
                $table->timestamp('ngay_tao')->useCurrent();
            });
        }

        if (! Schema::hasTable('so_thu_nhap')) {
            Schema::create('so_thu_nhap', function (Blueprint $table) {
                $table->increments('ma_so_thu_nhap');
                $table->integer('ma_nguoi_dung');
                $table->string('ky_luong', 30);
                $table->integer('tong_so_ca_lam')->default(0);
                $table->decimal('tong_gio_lam', 6, 2)->default(0.00);
                $table->decimal('tong_tien_luong_co_ban', 12, 2)->default(0.00);
                $table->decimal('tong_tien_thuong', 12, 2)->default(0.00);
                $table->decimal('tong_tien_phat', 12, 2)->default(0.00);
                $table->decimal('thuc_nhan', 12, 2)->default(0.00);
                $table->string('trang_thai_thanh_toan', 30)->default('chua_thanh_toan');
                $table->dateTime('ngay_thanh_toan')->nullable();
                $table->text('ghi_chu')->nullable();
                $table->timestamp('ngay_tao')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop in production
    }
};
