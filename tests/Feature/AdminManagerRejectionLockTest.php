<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminManagerRejectionLockTest extends TestCase
{
    /**
     * Test admin không thể khóa tài khoản quản lý nhà hàng đã bị từ chối
     */
    public function test_cannot_lock_rejected_manager_account()
    {
        $admin = [
            'ma_nguoi_dung' => 1,
            'ho_ten' => 'Quản Trị Viên',
            'email' => 'admin@goldenshift.vn',
            'vai_tro' => 'quan_tri_vien',
            'trang_thai' => 'hoat_dong',
        ];

        // Tạo tài khoản quản lý ở trạng thái tu_choi trong mock session
        $response = $this->withSession([
            'user' => $admin,
            'mock_pending_managers' => [
                [
                    'ma_nguoi_dung' => 999,
                    'ho_ten' => 'Trần Văn Từ Chối',
                    'email' => 'tuchoi@goldenshift.vn',
                    'vai_tro' => 'quan_ly_nha_hang',
                    'trang_thai' => 'tu_choi',
                    'ly_do_tu_choi' => 'Hồ sơ chưa đạt tiêu chuẩn quản lý chuỗi.',
                ],
            ],
        ])->post(route('admin.users.toggle_lock', 999));

        $response->assertSessionHas('error', 'Tài khoản Quản lý đã bị từ chối xét duyệt, không thể thực hiện thao tác khóa tài khoản.');
    }

    /**
     * Test giao diện quản lý tài khoản không hiển thị nút khóa cho tài khoản bị từ chối
     */
    public function test_ui_does_not_show_lock_button_for_rejected_manager()
    {
        $admin = [
            'ma_nguoi_dung' => 1,
            'ho_ten' => 'Quản Trị Viên',
            'email' => 'admin@goldenshift.vn',
            'vai_tro' => 'quan_tri_vien',
            'trang_thai' => 'hoat_dong',
        ];

        // 1. Kiểm tra trên danh sách tổng hợp: tài khoản ID 999 (tu_choi) không có action Khóa hay Duyệt
        $response = $this->withSession([
            'user' => $admin,
            'mock_pending_managers' => [
                [
                    'ma_nguoi_dung' => 999,
                    'ho_ten' => 'Trần Văn Từ Chối',
                    'email' => 'tuchoi@goldenshift.vn',
                    'so_dien_thoai' => '0911223344',
                    'vai_tro' => 'quan_ly_nha_hang',
                    'trang_thai' => 'tu_choi',
                    'ly_do_tu_choi' => 'Hồ sơ chưa đạt tiêu chuẩn quản lý chuỗi.',
                ],
            ],
        ])->get(route('admin.users.index', ['tab' => 'quan_ly']));

        $response->assertStatus(200);
        $response->assertSee('Trần Văn Từ Chối');
        $response->assertSee('Đã từ chối');
        $response->assertSee('—');
        // Không có link/form toggle_lock hoặc duyệt cho tài khoản 999
        $response->assertDontSee(route('admin.users.toggle_lock', 999), false);
        $response->assertDontSee(route('admin.users.duyet', 999), false);
        $response->assertDontSee('data-id="999"', false);

        // 2. Kiểm tra khi lọc theo trạng thái 'tu_choi': hoàn toàn không có nút Khóa / Mở khóa nào
        $filterResponse = $this->withSession([
            'user' => $admin,
            'mock_pending_managers' => [
                [
                    'ma_nguoi_dung' => 999,
                    'ho_ten' => 'Trần Văn Từ Chối',
                    'email' => 'tuchoi@goldenshift.vn',
                    'so_dien_thoai' => '0911223344',
                    'vai_tro' => 'quan_ly_nha_hang',
                    'trang_thai' => 'tu_choi',
                    'ly_do_tu_choi' => 'Hồ sơ chưa đạt tiêu chuẩn quản lý chuỗi.',
                ],
            ],
        ])->get(route('admin.users.index', ['tab' => 'quan_ly', 'status' => 'tu_choi']));

        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Trần Văn Từ Chối');
        $filterResponse->assertDontSee('class="btn-action-lock"', false);
        $filterResponse->assertDontSee('class="btn-action-unlock"', false);
        $filterResponse->assertDontSee('class="btn-action-duyet"', false);
    }
}
