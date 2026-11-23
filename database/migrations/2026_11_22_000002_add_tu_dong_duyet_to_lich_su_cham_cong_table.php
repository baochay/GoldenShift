<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true) && Schema::hasTable('lich_su_cham_cong')) {
            DB::statement(
                "ALTER TABLE lich_su_cham_cong MODIFY COLUMN trang_thai_cham_cong ENUM('chua_vao_ca','dung_gio','di_muon','ve_som','vang_mat','cho_xac_nhan','da_xac_nhan','tu_dong_duyet','can_kiem_tra') DEFAULT 'chua_vao_ca'"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Giữ nguyên ENUM để tránh mất dữ liệu trạng thái đang sử dụng
    }
};
