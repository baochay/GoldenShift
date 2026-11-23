<?php

namespace App\Http\Controllers;

class UnderConstructionController extends Controller
{
    public function show(string $featureName = 'Chức năng', string $featureDesc = '')
    {
        if (empty($featureDesc)) {
            $featureDesc = "Chức năng '{$featureName}' đang được đội ngũ kỹ thuật hoàn thiện và sẽ sớm ra mắt trong phiên bản tiếp theo. Vui lòng quay lại sau!";
        }

        return view('errors.under_construction', [
            'featureName' => $featureName,
            'featureDesc' => $featureDesc,
        ]);
    }

    public function baoCaoThongKe()
    {
        return $this->show(
            'Báo Cáo Thống Kê & Phân Tích',
            'Hệ thống tổng hợp báo cáo tuyển dụng, chi phí nhân sự và hiệu suất ca làm đang được tích hợp công cụ biểu đồ nâng cao.'
        );
    }

    public function quanLyThuongHieuNhaHang()
    {
        return $this->show(
            'Quản Lý Thương Hiệu & Nhà Hàng',
            'Module quản lý danh mục chuỗi nhà hàng F&B, thiết lập định biên ca làm và phân bổ nhân sự chi nhánh đang được xây dựng.'
        );
    }

    public function quanLyTaiKhoan()
    {
        return $this->show(
            'Quản Lý Toàn Bộ Tài Khoản',
            'Chức năng phân quyền, kích hoạt/khóa tài khoản quản lý và ứng viên trên toàn hệ thống đang được hoàn thiện.'
        );
    }

    public function chamCongThuNhap()
    {
        return $this->show(
            'Chấm Công & Bảng Lương Ca Làm',
            'Hệ thống quét mã QR điểm danh ca làm, xác nhận giờ làm thực tế và tính lương tự động cho nhân sự theo ca đang được hoàn thiện.'
        );
    }

    public function soThuNhap()
    {
        return $this->show(
            'Sổ Thu Nhập & Lịch Sử Nhận Lương',
            'Tính năng theo dõi tổng giờ làm, đối soát thu nhập theo ca và lịch sử thanh toán lương của bạn đang được phát triển.'
        );
    }

    public function thongTinCaNhan()
    {
        return $this->show(
            'Hồ Sơ & Cài Đặt Cá Nhân',
            'Chức năng chỉnh sửa thông tin cá nhân, cập nhật ảnh đại diện và thay đổi mật khẩu tài khoản đang được hoàn thiện.'
        );
    }

    public function dashboard()
    {
        return $this->show(
            'Bảng Quản Trị Hệ Thống',
            'Bảng điều khiển trung tâm quản trị toàn hệ thống Golden Shift đang được nâng cấp.'
        );
    }
}
