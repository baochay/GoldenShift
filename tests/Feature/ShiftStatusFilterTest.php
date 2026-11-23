<?php

namespace Tests\Feature;

use Tests\TestCase;

class ShiftStatusFilterTest extends TestCase
{
    /**
     * Test lọc ca làm việc theo trạng thái 'hoan_thanh' giữ nguyên giá trị dropdown selected
     */
    public function test_manager_filter_completed_shifts_preserves_selected_option()
    {
        $manager = [
            'ma_nguoi_dung' => 2,
            'ho_ten' => 'Nguyễn Thị Bảo Châu',
            'email' => 'chau.ntb@goldenshift.vn',
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => 1,
            'trang_thai' => 'da_duyet',
        ];

        $response = $this->withSession(['user' => $manager])
            ->get(route('ca-lam-viec.index', ['trang_thai' => 'hoan_thanh']));

        $response->assertStatus(200);
        // Kiểm tra option hoàn thành có selected
        $response->assertSee('<option value="hoan_thanh" selected>Hoàn thành</option>', false);
    }

    /**
     * Test lọc ca làm việc theo trạng thái 'dang_tuyen' giữ nguyên giá trị dropdown selected
     */
    public function test_manager_filter_recruiting_shifts_preserves_selected_option()
    {
        $manager = [
            'ma_nguoi_dung' => 2,
            'ho_ten' => 'Nguyễn Thị Bảo Châu',
            'email' => 'chau.ntb@goldenshift.vn',
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => 1,
            'trang_thai' => 'da_duyet',
        ];

        $response = $this->withSession(['user' => $manager])
            ->get(route('ca-lam-viec.index', ['trang_thai' => 'dang_tuyen']));

        $response->assertStatus(200);
        $response->assertSee('<option value="dang_tuyen" selected>Đang tuyển</option>', false);
    }
}
