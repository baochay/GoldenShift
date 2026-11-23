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
        Schema::table('ca_lam_viec', function (Blueprint $table) {
            if (! Schema::hasColumn('ca_lam_viec', 'ly_do_huy')) {
                $table->text('ly_do_huy')->nullable()->after('trang_thai_ca');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ca_lam_viec', function (Blueprint $table) {
            if (Schema::hasColumn('ca_lam_viec', 'ly_do_huy')) {
                $table->dropColumn('ly_do_huy');
            }
        });
    }
};
