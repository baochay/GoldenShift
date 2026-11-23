<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo bảng thong_bao - Hệ thống thông báo In-app nội bộ
     * Các trường: ma_thong_bao, ma_nguoi_dung, tieu_de, noi_dung,
     *             duong_dan_lien_ket, da_doc (mặc định 0), created_at
     */
    public function up(): void
    {
        Schema::create('thong_bao', function (Blueprint $table) {
            $table->bigIncrements('ma_thong_bao');
            $table->unsignedBigInteger('ma_nguoi_dung');
            $table->string('tieu_de', 255);
            $table->text('noi_dung');
            $table->string('duong_dan_lien_ket', 500)->nullable();
            $table->tinyInteger('da_doc')->default(0); // 0 = chưa đọc, 1 = đã đọc
            $table->timestamp('created_at')->useCurrent();

            $table->index('ma_nguoi_dung');
            $table->index(['ma_nguoi_dung', 'da_doc']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thong_bao');
    }
};
