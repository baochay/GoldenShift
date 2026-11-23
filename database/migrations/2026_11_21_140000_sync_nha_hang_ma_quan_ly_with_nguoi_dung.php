<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Đồng bộ lại cột ma_quan_ly trên bảng nha_hang theo đúng tài khoản Quản lý
     * phụ trách thực tế trong bảng nguoi_dung (ma_nha_hang_truc_thuoc).
     */
    public function up(): void
    {
        if (! Schema::hasTable('nha_hang') || ! Schema::hasTable('nguoi_dung')) {
            return;
        }

        $restaurants = DB::table('nha_hang')->orderBy('ma_nha_hang')->get(['ma_nha_hang']);

        foreach ($restaurants as $branch) {
            $manager = DB::table('nguoi_dung')
                ->where('vai_tro', 'quan_ly_nha_hang')
                ->whereIn('trang_thai', ['da_duyet', 'hoat_dong'])
                ->where('ma_nha_hang_truc_thuoc', $branch->ma_nha_hang)
                ->orderBy('ma_nguoi_dung', 'asc')
                ->first(['ma_nguoi_dung']);

            DB::table('nha_hang')
                ->where('ma_nha_hang', $branch->ma_nha_hang)
                ->update([
                    'ma_quan_ly' => $manager ? $manager->ma_nguoi_dung : null,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dữ liệu ma_quan_ly đã đồng bộ không hoàn tác về giá trị sai lệch cũ
    }
};
