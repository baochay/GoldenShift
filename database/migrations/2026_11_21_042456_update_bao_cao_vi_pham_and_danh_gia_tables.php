<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('bao_cao_vi_pham')) {
            Schema::table('bao_cao_vi_pham', function (Blueprint $table) {
                if (! Schema::hasColumn('bao_cao_vi_pham', 'hinh_anh_minh_chung')) {
                    $table->string('hinh_anh_minh_chung', 255)->nullable()->after('mo_ta_chi_tiet');
                }
                if (! Schema::hasColumn('bao_cao_vi_pham', 'ghi_chu_admin')) {
                    $table->text('ghi_chu_admin')->nullable()->after('hinh_thuc_xu_ly');
                }
            });

            // Mở rộng loai_vi_pham và trang_thai_xu_ly để linh hoạt lưu trữ
            try {
                DB::statement('ALTER TABLE bao_cao_vi_pham MODIFY COLUMN loai_vi_pham VARCHAR(100) NOT NULL');
                DB::statement("ALTER TABLE bao_cao_vi_pham MODIFY COLUMN trang_thai_xu_ly VARCHAR(50) DEFAULT 'cho_xuly'");
            } catch (Throwable $e) {
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bao_cao_vi_pham')) {
            Schema::table('bao_cao_vi_pham', function (Blueprint $table) {
                if (Schema::hasColumn('bao_cao_vi_pham', 'hinh_anh_minh_chung')) {
                    $table->dropColumn('hinh_anh_minh_chung');
                }
                if (Schema::hasColumn('bao_cao_vi_pham', 'ghi_chu_admin')) {
                    $table->dropColumn('ghi_chu_admin');
                }
            });
        }
    }
};
