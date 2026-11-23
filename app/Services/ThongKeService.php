<?php

namespace App\Services;

use App\Models\BaoCaoViPham;
use App\Models\CaLamViec;
use App\Models\DanhGiaCaLam;
use App\Models\HoSoUngTuyen;
use App\Models\LichSuChamCong;
use App\Models\NhaHang;
use App\Models\SoThuNhap;
use App\Models\ThuongHieu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ThongKeService
{
    /**
     * Phân giải khoảng thời gian lọc từ request (Hôm nay, Tuần này, Tháng này, Quý này, Tùy chọn, Tất cả).
     */
    public function resolveDateRange(Request $request): array
    {
        $now = Carbon::now();
        $presetInput = trim((string) $request->input('preset', ''));
        $tuNgayInput = trim((string) $request->input('tu_ngay', ''));
        $denNgayInput = trim((string) $request->input('den_ngay', ''));

        if ($presetInput === '' && ($tuNgayInput !== '' || $denNgayInput !== '')) {
            $preset = 'tuy_chon';
        } elseif ($presetInput === '') {
            $preset = 'tat_ca';
        } else {
            $preset = in_array($presetInput, ['tat_ca', 'hom_nay', 'tuan_nay', 'thang_nay', 'quy_nay', 'tuy_chon'], true)
                ? $presetInput
                : 'tat_ca';
        }

        $start = null;
        $end = null;
        $label = 'Toàn bộ thời gian';

        if ($preset === 'hom_nay') {
            $start = $now->copy()->startOfDay();
            $end = $now->copy()->endOfDay();
            $label = 'Hôm nay ('.$now->format('d/m/Y').')';
        } elseif ($preset === 'tuan_nay') {
            $start = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $end = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
            $label = 'Tuần này ('.$start->format('d/m').' - '.$end->format('d/m/Y').')';
        } elseif ($preset === 'thang_nay') {
            $start = $now->copy()->startOfMonth()->startOfDay();
            $end = $now->copy()->endOfMonth()->endOfDay();
            $label = 'Tháng '.$now->format('m/Y');
        } elseif ($preset === 'quy_nay') {
            $start = $now->copy()->startOfQuarter()->startOfDay();
            $end = $now->copy()->endOfQuarter()->endOfDay();
            $label = 'Quý '.$now->quarter.'/'.$now->year;
        } elseif ($preset === 'tuy_chon') {
            if ($tuNgayInput !== '') {
                try {
                    $start = Carbon::parse($tuNgayInput)->startOfDay();
                } catch (\Throwable $e) {
                    $start = null;
                }
            }
            if ($denNgayInput !== '') {
                try {
                    $end = Carbon::parse($denNgayInput)->endOfDay();
                } catch (\Throwable $e) {
                    $end = null;
                }
            }

            if ($start && $end && $start->gt($end)) {
                $tmp = $start->copy()->startOfDay();
                $start = $end->copy()->startOfDay();
                $end = $tmp->endOfDay();
            }

            if (! $start && ! $end) {
                $preset = 'tat_ca';
                $label = 'Toàn bộ thời gian';
            } elseif ($start && $end) {
                $label = 'Từ '.$start->format('d/m/Y').' đến '.$end->format('d/m/Y');
            } elseif ($start) {
                $label = 'Từ ngày '.$start->format('d/m/Y');
            } else {
                $label = 'Đến ngày '.$end->format('d/m/Y');
            }
        }

        return [
            'preset' => $preset,
            'tu_ngay' => $start ? $start->format('Y-m-d') : '',
            'den_ngay' => $end ? $end->format('Y-m-d') : '',
            'start' => $start,
            'end' => $end,
            'label' => $label,
        ];
    }

    /**
     * Tổng hợp báo cáo thống kê toàn hệ thống dành cho Admin.
     */
    public function getAdminAnalytics(array $range, ?int $brandId = null, ?int $restaurantId = null): array
    {
        $start = $range['start'];
        $end = $range['end'];

        $restaurantsQuery = NhaHang::with('thuongHieu');
        if ($brandId) {
            $restaurantsQuery->where('ma_thuong_hieu', $brandId);
        }
        if ($restaurantId) {
            $restaurantsQuery->where('ma_nha_hang', $restaurantId);
        }
        $restaurants = $restaurantsQuery->orderBy('ten_nha_hang')->get();
        $restaurantIds = $restaurants->pluck('ma_nha_hang')->all();

        $brandsQuery = ThuongHieu::query();
        if ($brandId) {
            $brandsQuery->where('ma_thuong_hieu', $brandId);
        } elseif ($restaurantId) {
            $brandsQuery->whereIn('ma_thuong_hieu', $restaurants->pluck('ma_thuong_hieu')->unique()->all());
        }
        $brands = $brandsQuery->orderBy('ten_thuong_hieu')->get();

        // 1. Ca làm việc
        $shiftsQuery = CaLamViec::with('nhaHang.thuongHieu');
        if ($brandId || $restaurantId) {
            $shiftsQuery->whereIn('ma_nha_hang', empty($restaurantIds) ? [0] : $restaurantIds);
        }
        $this->applyDateColumnFilter($shiftsQuery, 'ngay_lam_viec', $start, $end, true);
        $shifts = $shiftsQuery->get();

        // 2. Đơn ứng tuyển
        $appsQuery = HoSoUngTuyen::with('caLamViec.nhaHang');
        if ($brandId || $restaurantId) {
            $appsQuery->whereHas('caLamViec', function ($q) use ($restaurantIds) {
                $q->whereIn('ma_nha_hang', empty($restaurantIds) ? [0] : $restaurantIds);
            });
        }
        $this->applyDateColumnFilter($appsQuery, 'ngay_nop_don', $start, $end, false);
        $applications = $appsQuery->get();

        // 3. Lịch sử chấm công
        $attQuery = LichSuChamCong::with(['caLamViec.nhaHang', 'nguoiDung']);
        if ($brandId || $restaurantId) {
            $attQuery->whereHas('caLamViec', function ($q) use ($restaurantIds) {
                $q->whereIn('ma_nha_hang', empty($restaurantIds) ? [0] : $restaurantIds);
            });
        }
        $attendances = $this->filterCollectionByDate($attQuery->get(), function (LichSuChamCong $cc) {
            return $this->resolveAttendanceDate($cc);
        }, $start, $end);

        // 4. Sổ thu nhập (chi phí nhân sự đã thanh toán toàn hệ thống)
        $paidLedgers = $this->filterCollectionByDate(
            SoThuNhap::where('trang_thai_thanh_toan', 'da_thanh_toan')->get(),
            fn (SoThuNhap $stn) => $this->parseDateSafe($stn->ngay_thanh_toan ?? $stn->ngay_tao),
            $start,
            $end
        );
        $chiPhiDaThanhToan = (float) $paidLedgers->sum('thuc_nhan');

        // 5. Đánh giá
        $revQuery = DanhGiaCaLam::with(['caLamViec.nhaHang', 'nguoiDuocDanhGia']);
        if ($brandId || $restaurantId) {
            $revQuery->whereHas('caLamViec', function ($q) use ($restaurantIds) {
                $q->whereIn('ma_nha_hang', empty($restaurantIds) ? [0] : $restaurantIds);
            });
        }
        $this->applyDateColumnFilter($revQuery, 'ngay_danh_gia', $start, $end, false);
        $reviews = $revQuery->get();

        // 6. Báo cáo vi phạm
        $repQuery = BaoCaoViPham::with('caLamViec.nhaHang');
        if ($brandId || $restaurantId) {
            $repQuery->whereHas('caLamViec', function ($q) use ($restaurantIds) {
                $q->whereIn('ma_nha_hang', empty($restaurantIds) ? [0] : $restaurantIds);
            });
        }
        $this->applyDateColumnFilter($repQuery, 'ngay_lap_bao_cao', $start, $end, false);
        $reports = $repQuery->get();

        // Tính KPI tổng quan
        $tongCaLam = $shifts->count();
        $tongDon = $applications->count();
        $donDaNhan = $applications->whereIn('trang_thai_ung_tuyen', ['da_nhan', 'da_duyet'])->count();
        $tyLeThanhCong = $tongDon > 0 ? round(($donDaNhan / $tongDon) * 100, 1) : 0.0;

        $approvedAttendances = $attendances->whereIn('trang_thai_cham_cong', ['da_xac_nhan', 'tu_dong_duyet']);
        $tongGioLam = round((float) $approvedAttendances->sum('so_gio_lam_thuc_te'), 2);
        $tongQuyLuongChamCong = round((float) $approvedAttendances->sum(function (LichSuChamCong $cc) {
            $luongGio = (float) ($cc->caLamViec->muc_luong_theo_gio ?? 0);

            return (float) $cc->so_gio_lam_thuc_te * $luongGio;
        }), 0);

        $tongLuotDanhGia = $reviews->count();
        $diemDanhGiaTb = $tongLuotDanhGia > 0 ? round((float) $reviews->avg('so_sao'), 1) : 0.0;

        // Thống kê theo Chi nhánh nhà hàng
        $theoNhaHang = [];
        foreach ($restaurants as $nh) {
            $nhShifts = $shifts->where('ma_nha_hang', $nh->ma_nha_hang);
            $nhApps = $applications->filter(fn ($a) => $a->caLamViec && (int) $a->caLamViec->ma_nha_hang === (int) $nh->ma_nha_hang);
            $nhApprovedAtt = $approvedAttendances->filter(fn ($cc) => $cc->caLamViec && (int) $cc->caLamViec->ma_nha_hang === (int) $nh->ma_nha_hang);
            $nhReviews = $reviews->filter(function ($r) use ($nh) {
                return $r->loai_danh_gia === 'ung_vien_danh_gia_nha_hang'
                    && $r->caLamViec
                    && (int) $r->caLamViec->ma_nha_hang === (int) $nh->ma_nha_hang;
            });

            $nhTongDon = $nhApps->count();
            $nhDonNhan = $nhApps->whereIn('trang_thai_ung_tuyen', ['da_nhan', 'da_duyet'])->count();
            $nhGio = round((float) $nhApprovedAtt->sum('so_gio_lam_thuc_te'), 2);
            $nhLuong = round((float) $nhApprovedAtt->sum(fn ($cc) => (float) $cc->so_gio_lam_thuc_te * (float) ($cc->caLamViec->muc_luong_theo_gio ?? 0)), 0);

            $theoNhaHang[] = [
                'ma_nha_hang' => (int) $nh->ma_nha_hang,
                'ma_thuong_hieu' => (int) $nh->ma_thuong_hieu,
                'ten_nha_hang' => $nh->ten_nha_hang,
                'ten_thuong_hieu' => $nh->thuongHieu->ten_thuong_hieu ?? '—',
                'so_ca_lam' => $nhShifts->count(),
                'tong_don' => $nhTongDon,
                'don_da_nhan' => $nhDonNhan,
                'ty_le_thanh_cong' => $nhTongDon > 0 ? round(($nhDonNhan / $nhTongDon) * 100, 1) : 0.0,
                'tong_gio_lam' => $nhGio,
                'quy_luong' => $nhLuong,
                'diem_tb' => $nhReviews->count() > 0 ? round((float) $nhReviews->avg('so_sao'), 1) : null,
                'so_danh_gia' => $nhReviews->count(),
            ];
        }
        usort($theoNhaHang, fn ($a, $b) => $b['quy_luong'] <=> $a['quy_luong'] ?: $b['so_ca_lam'] <=> $a['so_ca_lam']);

        // Thống kê theo Thương hiệu
        $nhCol = collect($theoNhaHang);
        $theoThuongHieu = [];
        foreach ($brands as $brand) {
            $bBranches = $nhCol->where('ma_thuong_hieu', (int) $brand->ma_thuong_hieu);
            $bTongDon = (int) $bBranches->sum('tong_don');
            $bDonNhan = (int) $bBranches->sum('don_da_nhan');
            $bReviews = $reviews->filter(function ($r) use ($brand) {
                return $r->loai_danh_gia === 'ung_vien_danh_gia_nha_hang'
                    && $r->caLamViec
                    && $r->caLamViec->nhaHang
                    && (int) $r->caLamViec->nhaHang->ma_thuong_hieu === (int) $brand->ma_thuong_hieu;
            });

            $theoThuongHieu[] = [
                'ma_thuong_hieu' => (int) $brand->ma_thuong_hieu,
                'ten_thuong_hieu' => $brand->ten_thuong_hieu,
                'so_chi_nhanh' => $restaurants->where('ma_thuong_hieu', $brand->ma_thuong_hieu)->count(),
                'so_ca_lam' => (int) $bBranches->sum('so_ca_lam'),
                'tong_don' => $bTongDon,
                'don_da_nhan' => $bDonNhan,
                'ty_le_thanh_cong' => $bTongDon > 0 ? round(($bDonNhan / $bTongDon) * 100, 1) : 0.0,
                'tong_gio_lam' => round((float) $bBranches->sum('tong_gio_lam'), 2),
                'quy_luong' => round((float) $bBranches->sum('quy_luong'), 0),
                'diem_tb' => $bReviews->count() > 0 ? round((float) $bReviews->avg('so_sao'), 1) : null,
            ];
        }
        usort($theoThuongHieu, fn ($a, $b) => $b['quy_luong'] <=> $a['quy_luong'] ?: $b['so_ca_lam'] <=> $a['so_ca_lam']);

        // Thống kê Vi phạm theo loại
        $viPhamTheoLoai = $this->groupViolationsByType($reports);

        // Hiệu suất ca làm & trạng thái đơn & Top 5 Ứng viên xuất sắc
        $shiftPerf = $this->calculateShiftPerformance($shifts, $attendances, $reports);
        $appStatusChart = $this->buildApplicationStatusChart($applications);
        $lineTrend = $this->buildApplicationTrendLine($applications, $start, $end);
        $barMonthly = $this->buildMonthlyPayrollAndShiftBar($shifts, $approvedAttendances);
        $topUngVien = $this->calculateTopCandidatesRanking($attendances, $reviews);

        return [
            'kpis' => [
                'tong_ca_lam' => $tongCaLam,
                'tong_don_ung_tuyen' => $tongDon,
                'don_da_nhan' => $donDaNhan,
                'ty_le_ung_tuyen_thanh_cong' => $tyLeThanhCong,
                'chi_phi_da_thanh_toan' => $chiPhiDaThanhToan,
                'tong_quy_luong_cham_cong' => $tongQuyLuongChamCong,
                'tong_gio_lam' => $tongGioLam,
                'diem_danh_gia_tb' => $diemDanhGiaTb,
                'tong_luot_danh_gia' => $tongLuotDanhGia,
                'tong_vi_pham' => $reports->count(),
            ],
            'theo_thuong_hieu' => $theoThuongHieu,
            'theo_nha_hang' => $theoNhaHang,
            'vi_pham_theo_loai' => $viPhamTheoLoai,
            'hieu_suat_ca' => $shiftPerf,
            'top_ung_vien' => $topUngVien,
            'charts' => [
                'bar_monthly' => $barMonthly,
                'doughnut_shift_status' => [
                    'labels' => ['Đúng giờ', 'Đi muộn', 'Hủy / Bùng ca'],
                    'values' => [$shiftPerf['dung_gio'], $shiftPerf['di_muon'], $shiftPerf['huy_bung_ca']],
                ],
                'doughnut_application_status' => $appStatusChart,
                'line_applications_trend' => $lineTrend,
            ],
        ];
    }

    /**
     * Tổng hợp báo cáo thống kê dành riêng cho Quản lý chi nhánh.
     */
    public function getManagerAnalytics(NhaHang $nhaHang, array $range, string $periodGroup = 'thang'): array
    {
        $periodGroup = in_array($periodGroup, ['tuan', 'thang'], true) ? $periodGroup : 'thang';
        $start = $range['start'];
        $end = $range['end'];
        $branchId = (int) $nhaHang->ma_nha_hang;

        // 1. Ca làm chi nhánh
        $shiftsQuery = CaLamViec::where('ma_nha_hang', $branchId);
        $this->applyDateColumnFilter($shiftsQuery, 'ngay_lam_viec', $start, $end, true);
        $shifts = $shiftsQuery->get();

        // 2. Đơn ứng tuyển của chi nhánh
        $appsQuery = HoSoUngTuyen::with('caLamViec')
            ->whereHas('caLamViec', fn ($q) => $q->where('ma_nha_hang', $branchId));
        $this->applyDateColumnFilter($appsQuery, 'ngay_nop_don', $start, $end, false);
        $applications = $appsQuery->get();

        // 3. Chấm công chi nhánh
        $attQuery = LichSuChamCong::with(['caLamViec', 'nguoiDung'])
            ->whereHas('caLamViec', fn ($q) => $q->where('ma_nha_hang', $branchId));
        $attendances = $this->filterCollectionByDate(
            $attQuery->get(),
            fn (LichSuChamCong $cc) => $this->resolveAttendanceDate($cc),
            $start,
            $end
        );

        // 4. Đánh giá thuộc chi nhánh
        $revQuery = DanhGiaCaLam::with(['caLamViec', 'nguoiDuocDanhGia'])
            ->whereHas('caLamViec', fn ($q) => $q->where('ma_nha_hang', $branchId));
        $this->applyDateColumnFilter($revQuery, 'ngay_danh_gia', $start, $end, false);
        $reviews = $revQuery->get();

        // 5. Báo cáo vi phạm của chi nhánh
        $repQuery = BaoCaoViPham::with('caLamViec')
            ->whereHas('caLamViec', fn ($q) => $q->where('ma_nha_hang', $branchId));
        $this->applyDateColumnFilter($repQuery, 'ngay_lap_bao_cao', $start, $end, false);
        $reports = $repQuery->get();

        $approvedAttendances = $attendances->whereIn('trang_thai_cham_cong', ['da_xac_nhan', 'tu_dong_duyet']);
        $tongGioLam = round((float) $approvedAttendances->sum('so_gio_lam_thuc_te'), 2);
        $tongQuyLuong = round((float) $approvedAttendances->sum(function (LichSuChamCong $cc) {
            return (float) $cc->so_gio_lam_thuc_te * (float) ($cc->caLamViec->muc_luong_theo_gio ?? 0);
        }), 0);

        $tongDon = $applications->count();
        $donDaNhan = $applications->whereIn('trang_thai_ung_tuyen', ['da_nhan', 'da_duyet'])->count();
        $tyLeDuyet = $tongDon > 0 ? round(($donDaNhan / $tongDon) * 100, 1) : 0.0;

        $branchReviews = $reviews->where('loai_danh_gia', 'ung_vien_danh_gia_nha_hang');
        $tongLuotDanhGia = $branchReviews->count();
        $diemDanhGiaTb = $tongLuotDanhGia > 0 ? round((float) $branchReviews->avg('so_sao'), 1) : 0.0;

        $shiftPerf = $this->calculateShiftPerformance($shifts, $attendances, $reports);

        // Top 5 Ứng viên xuất sắc nhất (Thang 100: ĐG 40% + Giờ 40% + Chuyên cần 20%, lọc >= 2 ca & không vi phạm 30 ngày)
        $topUngVien = $this->calculateTopCandidatesRanking($attendances, $reviews);

        // Quỹ lương theo tuần / tháng
        $quyLuongTheoKy = $this->buildManagerPayrollByPeriod($approvedAttendances, $periodGroup);

        $barPayroll = [
            'labels' => array_column($quyLuongTheoKy, 'ky_label'),
            'chi_phi' => array_column($quyLuongTheoKy, 'chi_phi_luong'),
            'so_ca' => array_column($quyLuongTheoKy, 'so_ca'),
        ];

        $appStatusChart = $this->buildApplicationStatusChart($applications);
        $lineTrend = $this->buildApplicationTrendLine($applications, $start, $end);

        return [
            'kpis' => [
                'tong_ca_lam' => $shifts->count(),
                'tong_don_ung_tuyen' => $tongDon,
                'don_da_nhan' => $donDaNhan,
                'ty_le_duyet' => $tyLeDuyet,
                'tong_gio_lam' => $tongGioLam,
                'tong_quy_luong' => $tongQuyLuong,
                'ty_le_dung_gio' => $shiftPerf['ty_le_dung_gio'],
                'diem_danh_gia_tb' => $diemDanhGiaTb,
                'tong_luot_danh_gia' => $tongLuotDanhGia,
            ],
            'hieu_suat_ca' => $shiftPerf,
            'top_ung_vien' => $topUngVien,
            'quy_luong_theo_ky' => $quyLuongTheoKy,
            'charts' => [
                'bar_payroll' => $barPayroll,
                'doughnut_shift_performance' => [
                    'labels' => ['Đúng giờ', 'Đi muộn', 'Hủy / Bùng ca'],
                    'values' => [$shiftPerf['dung_gio'], $shiftPerf['di_muon'], $shiftPerf['huy_bung_ca']],
                ],
                'doughnut_application_status' => $appStatusChart,
                'line_applications_trend' => $lineTrend,
            ],
        ];
    }

    /**
     * Xuất mảng dữ liệu thành chuỗi CSV có UTF-8 BOM để mở trực tiếp trên Excel không lỗi font tiếng Việt.
     */
    public function buildCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return "\xEF\xBB\xBF".($csv ?: '');
    }

    private function applyDateColumnFilter($query, string $column, ?Carbon $start, ?Carbon $end, bool $dateOnly): void
    {
        if ($start) {
            $query->where($column, '>=', $dateOnly ? $start->toDateString() : $start->toDateTimeString());
        }
        if ($end) {
            $query->where($column, '<=', $dateOnly ? $end->toDateString() : $end->toDateTimeString());
        }
    }

    private function filterCollectionByDate($collection, callable $dateResolver, ?Carbon $start, ?Carbon $end)
    {
        if (! $start && ! $end) {
            return $collection;
        }

        return $collection->filter(function ($item) use ($dateResolver, $start, $end) {
            /** @var Carbon|null $dt */
            $dt = $dateResolver($item);
            if (! $dt) {
                return false;
            }
            if ($start && $dt->lt($start)) {
                return false;
            }
            if ($end && $dt->gt($end)) {
                return false;
            }

            return true;
        })->values();
    }

    private function resolveAttendanceDate(LichSuChamCong $cc): ?Carbon
    {
        $raw = $cc->thoi_gian_ra_thuc_te ?? $cc->thoi_gian_vao_thuc_te ?? $cc->ngay_tao;
        if ($raw) {
            return $this->parseDateSafe($raw);
        }
        if ($cc->caLamViec && $cc->caLamViec->ngay_lam_viec) {
            return $this->parseDateSafe($cc->caLamViec->ngay_lam_viec);
        }

        return null;
    }

    private function parseDateSafe($value): ?Carbon
    {
        if (! $value) {
            return null;
        }
        if ($value instanceof Carbon) {
            return $value->copy();
        }
        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function isAttendanceLate(LichSuChamCong $cc, ?CaLamViec $shift): bool
    {
        if ($cc->trang_thai_cham_cong === 'di_muon') {
            return true;
        }

        if ($cc->thoi_gian_vao_thuc_te && $shift && ! empty($shift->gio_bat_dau)) {
            try {
                $checkInHms = Carbon::parse($cc->thoi_gian_vao_thuc_te)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i:s');
                $startHms = strlen($shift->gio_bat_dau) === 5 ? $shift->gio_bat_dau.':00' : $shift->gio_bat_dau;

                return $checkInHms > $startHms;
            } catch (\Throwable $e) {
                return false;
            }
        }

        return false;
    }

    private function calculateShiftPerformance($shifts, $attendances, $reports): array
    {
        $dungGio = 0;
        $diMuon = 0;
        $noShowKeys = [];

        foreach ($attendances as $cc) {
            $status = $cc->trang_thai_cham_cong;
            if ($status === 'vang_mat') {
                $noShowKeys['ca_'.$cc->ma_ca_lam.'_u_'.$cc->ma_nguoi_dung] = true;

                continue;
            }

            $worked = ! in_array($status, ['chua_vao_ca'], true)
                && ($cc->thoi_gian_vao_thuc_te !== null || in_array($status, ['dung_gio', 'di_muon', 've_som', 'cho_xac_nhan', 'da_xac_nhan', 'tu_dong_duyet'], true));

            if (! $worked) {
                continue;
            }

            if ($this->isAttendanceLate($cc, $cc->caLamViec)) {
                $diMuon++;
            } else {
                $dungGio++;
            }
        }

        $cancelledShiftsCount = $shifts->where('trang_thai_ca', 'da_huy')->count();

        foreach ($reports as $rep) {
            if (in_array($rep->loai_vi_pham, ['bung_ca', 'bo_ca_khong_bao'], true)) {
                $key = $rep->ma_ca_lam
                    ? 'ca_'.$rep->ma_ca_lam.'_u_'.$rep->ma_nguoi_vi_pham
                    : 'rep_'.$rep->ma_vi_pham;
                $noShowKeys[$key] = true;
            }
        }

        $huyBungCa = $cancelledShiftsCount + count($noShowKeys);
        $totalPerf = $dungGio + $diMuon + $huyBungCa;

        return [
            'dung_gio' => $dungGio,
            'di_muon' => $diMuon,
            'huy_bung_ca' => $huyBungCa,
            'cancelled_shifts' => $cancelledShiftsCount,
            'no_show_count' => count($noShowKeys),
            'tong_luot' => $totalPerf,
            'ty_le_dung_gio' => $totalPerf > 0 ? round(($dungGio / $totalPerf) * 100, 1) : 0.0,
            'ty_le_di_muon' => $totalPerf > 0 ? round(($diMuon / $totalPerf) * 100, 1) : 0.0,
            'ty_le_huy_bung_ca' => $totalPerf > 0 ? round(($huyBungCa / $totalPerf) * 100, 1) : 0.0,
        ];
    }

    private function groupViolationsByType($reports): array
    {
        $total = $reports->count();
        $grouped = [];

        foreach ($reports as $rep) {
            $key = $rep->loai_vi_pham ?: 'ly_do_khac';
            $label = BaoCaoViPham::$danhSachLyDo[$key] ?? $key;

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'ma_loai' => $key,
                    'ten_loai' => $label,
                    'so_luong' => 0,
                    'cho_xu_ly' => 0,
                    'da_xu_ly' => 0,
                    'tu_choi' => 0,
                    'ty_le' => 0.0,
                ];
            }

            $grouped[$key]['so_luong']++;
            $st = $rep->trang_thai_chuan;
            if ($st === 'cho_xuly') {
                $grouped[$key]['cho_xu_ly']++;
            } elseif ($st === 'da_xuly') {
                $grouped[$key]['da_xu_ly']++;
            } else {
                $grouped[$key]['tu_choi']++;
            }
        }

        $result = array_values(array_map(function ($row) use ($total) {
            $row['ty_le'] = $total > 0 ? round(($row['so_luong'] / $total) * 100, 1) : 0.0;

            return $row;
        }, $grouped));

        usort($result, fn ($a, $b) => $b['so_luong'] <=> $a['so_luong']);

        return $result;
    }

    private function buildApplicationStatusChart($applications): array
    {
        $daNhan = $applications->whereIn('trang_thai_ung_tuyen', ['da_nhan', 'da_duyet'])->count();
        $choDuyet = $applications->where('trang_thai_ung_tuyen', 'cho_duyet')->count();
        $tuChoi = $applications->where('trang_thai_ung_tuyen', 'tu_choi')->count();
        $daHuy = $applications->whereIn('trang_thai_ung_tuyen', ['da_huy', 'da_huy_bo'])->count();

        return [
            'labels' => ['Đã nhận', 'Chờ duyệt', 'Từ chối', 'Đã hủy'],
            'values' => [$daNhan, $choDuyet, $tuChoi, $daHuy],
        ];
    }

    private function buildApplicationTrendLine($applications, ?Carbon $start, ?Carbon $end): array
    {
        $useDaily = false;
        if ($start && $end) {
            $useDaily = $start->diffInDays($end) <= 45;
        }

        $buckets = [];
        foreach ($applications as $app) {
            $dt = $this->parseDateSafe($app->ngay_nop_don);
            if (! $dt) {
                continue;
            }
            $key = $useDaily ? $dt->format('Y-m-d') : $dt->format('Y-m');
            $label = $useDaily ? $dt->format('d/m') : 'T'.$dt->format('m/Y');

            if (! isset($buckets[$key])) {
                $buckets[$key] = ['label' => $label, 'count' => 0];
            }
            $buckets[$key]['count']++;
        }

        ksort($buckets);

        return [
            'labels' => array_values(array_map(fn ($b) => $b['label'], $buckets)),
            'values' => array_values(array_map(fn ($b) => $b['count'], $buckets)),
        ];
    }

    private function buildMonthlyPayrollAndShiftBar($shifts, $approvedAttendances): array
    {
        $buckets = [];

        foreach ($shifts as $s) {
            $dt = $this->parseDateSafe($s->ngay_lam_viec);
            if (! $dt) {
                continue;
            }
            $key = $dt->format('Y-m');
            if (! isset($buckets[$key])) {
                $buckets[$key] = ['label' => 'Tháng '.$dt->format('m/Y'), 'chi_phi' => 0.0, 'so_ca' => 0];
            }
            $buckets[$key]['so_ca']++;
        }

        foreach ($approvedAttendances as $cc) {
            $dt = $this->resolveAttendanceDate($cc);
            if (! $dt) {
                continue;
            }
            $key = $dt->format('Y-m');
            if (! isset($buckets[$key])) {
                $buckets[$key] = ['label' => 'Tháng '.$dt->format('m/Y'), 'chi_phi' => 0.0, 'so_ca' => 0];
            }
            $wage = (float) ($cc->caLamViec->muc_luong_theo_gio ?? 0);
            $buckets[$key]['chi_phi'] += (float) $cc->so_gio_lam_thuc_te * $wage;
        }

        ksort($buckets);

        return [
            'labels' => array_values(array_map(fn ($b) => $b['label'], $buckets)),
            'chi_phi' => array_values(array_map(fn ($b) => round($b['chi_phi'], 0), $buckets)),
            'so_ca' => array_values(array_map(fn ($b) => $b['so_ca'], $buckets)),
        ];
    }

    /**
     * Thuật toán xếp hạng Top 5 Ứng viên xuất sắc theo thang điểm 100:
     *  - Điểm Đánh giá (40%): (Điểm TB / 5) * 40 (0 điểm nếu chưa có đánh giá)
     *  - Điểm Tổng giờ làm (40%): min(40, (Tổng giờ làm hoàn thành / 160) * 40)
     *  - Điểm Chuyên cần (20%): (Số ca đúng giờ / Tổng số ca đã tham gia) * 20
     * Điều kiện lọc: >= 2 ca hoàn thành/đã xác nhận & không có báo cáo vi phạm 'da_xuly' trong 30 ngày qua.
     */
    public function calculateTopCandidatesRanking($attendances, $reviews, ?Carbon $referenceTime = null): array
    {
        $now = $referenceTime ? $referenceTime->copy()->setTimezone('Asia/Ho_Chi_Minh') : Carbon::now('Asia/Ho_Chi_Minh');
        $violationCutoff = $now->copy()->subDays(30);

        $disqualifiedUserIds = [];
        if (Schema::hasTable('bao_cao_vi_pham')) {
            $disqualifiedUserIds = BaoCaoViPham::whereIn('trang_thai_xu_ly', ['da_xuly', 'da_xac_nhan'])
                ->where('ngay_lap_bao_cao', '>=', $violationCutoff->toDateTimeString())
                ->pluck('ma_nguoi_vi_pham')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->all();
        }
        $disqualifiedMap = array_flip($disqualifiedUserIds);

        $candStats = [];

        foreach ($attendances as $cc) {
            $uid = (int) $cc->ma_nguoi_dung;
            $u = $cc->nguoiDung;
            if (! $uid || ! $u || $cc->trang_thai_cham_cong === 'chua_vao_ca') {
                continue;
            }

            if (! isset($candStats[$uid])) {
                $candStats[$uid] = [
                    'ma_nguoi_dung' => $uid,
                    'ho_ten' => $u->ho_ten,
                    'email' => $u->email ?? '',
                    'so_dien_thoai' => $u->so_dien_thoai ?? '',
                    'anh_dai_dien' => $u->anh_dai_dien ?? '',
                    'so_ca' => 0,
                    'so_ca_tham_gia' => 0,
                    'so_ca_dung_gio' => 0,
                    'tong_gio_lam' => 0.0,
                    'tong_thu_nhap' => 0.0,
                    'diem_sum' => 0,
                    'so_luot_danh_gia' => 0,
                ];
            }

            $candStats[$uid]['so_ca_tham_gia']++;
            if ($cc->trang_thai_cham_cong !== 'vang_mat' && ! $this->isAttendanceLate($cc, $cc->caLamViec)) {
                $candStats[$uid]['so_ca_dung_gio']++;
            }

            $isCompleted = in_array($cc->trang_thai_cham_cong, ['da_xac_nhan', 'tu_dong_duyet'], true)
                || ($cc->caLamViec && $cc->caLamViec->trang_thai_ca === 'hoan_thanh' && $cc->trang_thai_cham_cong !== 'vang_mat');

            if ($isCompleted) {
                $hours = (float) $cc->so_gio_lam_thuc_te;
                $wage = (float) ($cc->caLamViec->muc_luong_theo_gio ?? 0);
                $candStats[$uid]['so_ca']++;
                $candStats[$uid]['tong_gio_lam'] += $hours;
                $candStats[$uid]['tong_thu_nhap'] += $hours * $wage;
            }
        }

        foreach ($reviews as $rev) {
            if ($rev->loai_danh_gia !== 'nha_hang_danh_gia_ung_vien') {
                continue;
            }
            $uid = (int) $rev->ma_nguoi_duoc_danh_gia;
            if (! isset($candStats[$uid])) {
                continue;
            }
            $candStats[$uid]['diem_sum'] += (int) $rev->so_sao;
            $candStats[$uid]['so_luot_danh_gia']++;
        }

        $ranked = [];
        foreach ($candStats as $uid => $row) {
            // Loại bỏ ứng viên vi phạm đã xử lý trong 30 ngày qua hoặc chưa đủ >= 2 ca hoàn thành
            if (isset($disqualifiedMap[$uid]) || (int) $row['so_ca'] < 2) {
                continue;
            }

            $luotDg = (int) $row['so_luot_danh_gia'];
            $diemTb = $luotDg > 0 ? round($row['diem_sum'] / $luotDg, 1) : 0.0;
            $diemDanhGia = $luotDg > 0 ? (($diemTb / 5.0) * 40.0) : 0.0;

            $tongGioLam = round((float) $row['tong_gio_lam'], 1);
            $diemGioLam = min(40.0, ($tongGioLam / 160.0) * 40.0);

            $tongThamGia = max(1, (int) $row['so_ca_tham_gia']);
            $tyLeDungGio = round(($row['so_ca_dung_gio'] / $tongThamGia) * 100.0, 1);
            $diemChuyenCan = ($row['so_ca_dung_gio'] / $tongThamGia) * 20.0;

            $diemXuatSac = round($diemDanhGia + $diemGioLam + $diemChuyenCan, 1);

            $avatarPath = $row['anh_dai_dien'];
            $avatarUrl = (! empty($avatarPath) && file_exists(public_path($avatarPath)))
                ? asset($avatarPath)
                : null;

            $ranked[] = [
                'ma_nguoi_dung' => $uid,
                'ho_ten' => $row['ho_ten'],
                'email' => $row['email'],
                'so_dien_thoai' => $row['so_dien_thoai'],
                'anh_dai_dien' => $avatarPath,
                'avatar_url' => $avatarUrl,
                'so_ca' => (int) $row['so_ca'],
                'so_ca_tham_gia' => (int) $row['so_ca_tham_gia'],
                'so_ca_dung_gio' => (int) $row['so_ca_dung_gio'],
                'tong_gio_lam' => $tongGioLam,
                'tong_thu_nhap' => round((float) $row['tong_thu_nhap'], 0),
                'so_luot_danh_gia' => $luotDg,
                'diem_danh_gia_tb' => $diemTb,
                'ty_le_dung_gio' => $tyLeDungGio,
                'diem_danh_gia' => round($diemDanhGia, 1),
                'diem_gio_lam' => round($diemGioLam, 1),
                'diem_chuyen_can' => round($diemChuyenCan, 1),
                'diem_xuat_sac' => $diemXuatSac,
            ];
        }

        usort($ranked, function ($a, $b) {
            if ($b['diem_xuat_sac'] !== $a['diem_xuat_sac']) {
                return $b['diem_xuat_sac'] <=> $a['diem_xuat_sac'];
            }
            if ($b['diem_danh_gia_tb'] !== $a['diem_danh_gia_tb']) {
                return $b['diem_danh_gia_tb'] <=> $a['diem_danh_gia_tb'];
            }
            if ($b['tong_gio_lam'] !== $a['tong_gio_lam']) {
                return $b['tong_gio_lam'] <=> $a['tong_gio_lam'];
            }

            return $b['ty_le_dung_gio'] <=> $a['ty_le_dung_gio'];
        });

        return array_slice($ranked, 0, 5);
    }

    private function buildManagerPayrollByPeriod($approvedAttendances, string $periodGroup): array
    {
        $buckets = [];

        foreach ($approvedAttendances as $cc) {
            $dt = $this->resolveAttendanceDate($cc);
            if (! $dt) {
                continue;
            }

            if ($periodGroup === 'tuan') {
                $key = sprintf('%04d-W%02d', $dt->isoWeekYear, $dt->isoWeek);
                $label = 'Tuần '.$dt->isoWeek.'/'.$dt->isoWeekYear;
            } else {
                $key = $dt->format('Y-m');
                $label = 'Tháng '.$dt->format('m/Y');
            }

            if (! isset($buckets[$key])) {
                $buckets[$key] = [
                    'ky_key' => $key,
                    'ky_label' => $label,
                    'so_ca' => 0,
                    'tong_gio' => 0.0,
                    'chi_phi_luong' => 0.0,
                ];
            }

            $hours = (float) $cc->so_gio_lam_thuc_te;
            $wage = (float) ($cc->caLamViec->muc_luong_theo_gio ?? 0);
            $buckets[$key]['so_ca']++;
            $buckets[$key]['tong_gio'] += $hours;
            $buckets[$key]['chi_phi_luong'] += $hours * $wage;
        }

        ksort($buckets);

        return array_values(array_map(function ($row) {
            $row['tong_gio'] = round($row['tong_gio'], 2);
            $row['chi_phi_luong'] = round($row['chi_phi_luong'], 0);

            return $row;
        }, $buckets));
    }
}
