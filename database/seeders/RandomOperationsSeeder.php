<?php

namespace Database\Seeders;

use App\Http\Controllers\SoThuNhapController;
use App\Models\DanhGiaCaLam;
use App\Models\LichSuChamCong;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RandomOperationsSeeder extends Seeder
{
    /**
     * Bổ sung dữ liệu ngẫu nhiên cho cả 8 nhà hàng (đủ 4 trạng thái ca)
     * và lịch sử ca làm quá khứ cho toàn bộ ứng viên trực tiếp vào MySQL.
     */
    public function run(): void
    {
        $tz = 'Asia/Ho_Chi_Minh';
        $now = Carbon::now($tz);

        $positions = [
            'Nhân viên Phục vụ' => [
                'wage_range' => [30000, 38000],
                'req' => 'Tác phong nhanh nhẹn, giao tiếp lịch sự, ưu tiên có kinh nghiệm bưng bê và chăm sóc bàn khách F&B.',
                'benefit' => 'Hỗ trợ 1 bữa cơm ca miễn phí, phụ cấp gửi xe, tiền tip chia đều theo ca và thanh toán tiền mặt ngay sau ca.',
                'app_notes' => [
                    'Em đã từng làm phục vụ bàn tại nhà hàng lẩu nướng, cam kết đến trước 15 phút.',
                    'Sinh viên năm 3, tác phong gọn gàng, có thể hỗ trợ dọn bàn và order nhanh.',
                    'Em sẵn sàng nhận ca, đã nắm rõ quy trình phục vụ chuẩn nhà hàng.',
                ],
            ],
            'Nhân viên Tiếp thực' => [
                'wage_range' => [28000, 35000],
                'req' => 'Sức khỏe tốt, cẩn thận khi lên món và hỗ trợ thu dọn bàn khu vực sảnh chính.',
                'benefit' => 'Bao cơm ca theo khung giờ, giữ xe miễn phí và thưởng chuyên cần cuối tháng.',
                'app_notes' => [
                    'Em quen việc tiếp thực giờ cao điểm, phối hợp nhịp nhàng với bếp và sảnh.',
                    'Sức khỏe tốt, chịu được áp lực cuối tuần, mong được quản lý xét duyệt.',
                    'Em từng chạy bàn tiếp thực tại khu trung tâm thương mại, đi làm đúng giờ.',
                ],
            ],
            'Nhân viên Pha chế' => [
                'wage_range' => [34000, 42000],
                'req' => 'Biết sử dụng máy pha chế cơ bản, đong định lượng trà/nước ép theo công thức chuẩn và giữ quầy bar sạch sẽ.',
                'benefit' => 'Phụ cấp cơm ca, thưởng doanh thu đồ uống và cơ hội nhận ca cố định dài hạn.',
                'app_notes' => [
                    'Em có kinh nghiệm 1 năm đứng quầy pha chế trà trái cây và đồ uống lạnh.',
                    'Thành thạo vệ sinh quầy bar, làm việc sạch sẽ và đúng công thức định lượng.',
                    'Em đã có chứng chỉ pha chế cơ bản, rất mong được tham gia ca làm tại chi nhánh.',
                ],
            ],
            'Nhân viên Phụ bếp' => [
                'wage_range' => [30000, 36000],
                'req' => 'Hỗ trợ sơ chế rau củ, chia định lượng nguyên liệu và giữ vệ sinh an toàn thực phẩm khu bếp.',
                'benefit' => 'Bao ăn giữa ca, cung cấp tạp dề đồng phục và gửi xe miễn phí tại tòa nhà.',
                'app_notes' => [
                    'Em cẩn thận, sạch sẽ, từng phụ bếp sơ chế tại chuỗi nhà hàng Nhật - Hàn.',
                    'Em có thể xử lý nhanh khâu chuẩn bị nguyên liệu giờ cao điểm buổi tối.',
                    'Sẵn sàng tuân thủ nghiêm ngặt quy định vệ sinh an toàn thực phẩm của bếp.',
                ],
            ],
            'Nhân viên Bếp' => [
                'wage_range' => [35000, 42000],
                'req' => 'Có kinh nghiệm đứng bếp nóng hoặc quầy nướng/lẩu, thao tác nhanh và phối hợp tốt với bếp trưởng ca.',
                'benefit' => 'Mức lương giờ cao theo tay nghề, bao cơm ca, thưởng hiệu suất khi đạt chuẩn chất lượng.',
                'app_notes' => [
                    'Em từng đứng bếp chính món nướng và lẩu hơn 1 năm, thao tác gọn và sạch.',
                    'Nắm vững quy trình ra món nhanh giờ đông khách, cam kết đảm bảo chất lượng.',
                    'Em mong muốn ứng tuyển ca bếp và gắn bó lâu dài với hệ thống Golden Shift.',
                ],
            ],
        ];

        $timeSlots = [
            ['08:00:00', '13:00:00'],
            ['09:00:00', '14:00:00'],
            ['13:00:00', '17:30:00'],
            ['14:00:00', '18:00:00'],
            ['17:00:00', '22:00:00'],
            ['17:30:00', '22:30:00'],
            ['18:00:00', '22:30:00'],
        ];

        $cancelReasons = [
            'Nhà hàng tiến hành bảo trì đột xuất hệ thống hút khói và điều hòa khu bếp trong khung giờ này.',
            'Đoàn khách đặt tiệc lớn thông báo dời lịch nên chi nhánh điều chỉnh giảm nhân sự thời vụ.',
            'Chi nhánh đã điều phối đủ nhân sự nội bộ toàn thời gian hỗ trợ ca làm việc.',
            'Kiểm tra an toàn PCCC định kỳ tại trung tâm thương mại trong buổi làm việc dự kiến.',
        ];

        $rejectNotes = [
            'Ca làm việc đã tuyển đủ số lượng nhân sự phù hợp cho khung giờ này.',
            'Chi nhánh ưu tiên ứng viên có kinh nghiệm đứng vị trí tương đương ở đợt này.',
            'Hồ sơ ứng viên nộp sau khi quản lý đã hoàn tất xếp lịch phân khu sảnh.',
        ];

        $candToRestComments = [
            5 => [
                'Quản lý hướng dẫn rất tận tâm, môi trường chuyên nghiệp, thanh toán đúng hẹn ngay sau ca!',
                'Đồng nghiệp cực kỳ hòa đồng, bếp và sảnh phối hợp nhịp nhàng, cơm ca ngon và đầy đủ.',
                'Ca làm phân công rõ ràng, không gian sạch sẽ chuẩn chuỗi cao cấp, chắc chắn sẽ quay lại.',
            ],
            4 => [
                'Môi trường làm việc tốt, giờ cao điểm hơi bận nhưng quản lý hỗ trợ rất kịp thời.',
                'Quy trình làm việc rõ ràng, thanh toán sòng phẳng, gửi xe thuận tiện.',
                'Trải nghiệm ổn áp, được hướng dẫn vị trí kỹ trước giờ mở cửa đón khách.',
            ],
            3 => [
                'Công việc đúng như mô tả, tuy nhiên cuối tuần khách đông nên cường độ hơi cao một chút.',
                'Môi trường tạm ổn, mong nhà hàng bổ sung thêm bảng phân công cụ thể trước giờ vào ca.',
            ],
        ];

        $restToCandComments = [
            5 => [
                'Ứng viên đến đúng giờ, tác phong nhanh nhẹn, thái độ niềm nở với khách hàng, hoàn thành xuất sắc.',
                'Chủ động phối hợp với tổ phục vụ, giữ khu vực bàn sạch sẽ, đánh giá rất cao tinh thần trách nhiệm.',
                'Kỹ năng nghiệp vụ vững, tiếp thu nhanh và xử lý tình huống giờ cao điểm rất khéo léo.',
            ],
            4 => [
                'Làm việc chăm chỉ, hoàn thành tốt phần việc được giao, cần tự tin hơn khi giới thiệu thực đơn mới.',
                'Tuân thủ đúng đồng phục và nội quy chi nhánh, phối hợp tốt với nhân sự ca.',
            ],
            3 => [
                'Hoàn thành đủ thời lượng ca làm, nhưng cần cải thiện tốc độ thao tác trong khung giờ cao điểm.',
            ],
        ];

        $authMethods = ['ma_qr', 'toa_do_gps', 'quan_ly_xac_nhan'];
        $positionNames = array_keys($positions);

        $summary = [
            'shifts_created' => 0,
            'apps_created' => 0,
            'attendance_created' => 0,
            'reviews_created' => 0,
            'violations_created' => 0,
            'notifications_created' => 0,
        ];

        DB::transaction(function () use (
            $tz,
            $now,
            $positions,
            $positionNames,
            $timeSlots,
            $cancelReasons,
            $rejectNotes,
            $candToRestComments,
            $restToCandComments,
            $authMethods,
            &$summary
        ) {
            $restaurants = DB::table('nha_hang')->orderBy('ma_nha_hang')->get();
            $candidates = DB::table('nguoi_dung')
                ->where('vai_tro', 'ung_vien')
                ->orderBy('ma_nguoi_dung')
                ->get();

            if ($restaurants->isEmpty() || $candidates->isEmpty()) {
                return;
            }

            // Đảm bảo tất cả ứng viên đều có hồ sơ chi tiết trong ho_so_ung_vien
            foreach ($candidates as $cand) {
                $existsProfile = DB::table('ho_so_ung_vien')
                    ->where('ma_nguoi_dung', $cand->ma_nguoi_dung)
                    ->exists();
                if (! $existsProfile) {
                    DB::table('ho_so_ung_vien')->insert([
                        'ma_nguoi_dung' => $cand->ma_nguoi_dung,
                        'ngay_sinh' => '2002-06-15',
                        'gioi_tinh' => 'khac',
                        'so_cccd' => '079202'.str_pad((string) $cand->ma_nguoi_dung, 6, '0', STR_PAD_LEFT),
                        'gioi_thieu_ban_than' => 'Ứng viên thời vụ F&B sẵn sàng nhận ca linh hoạt tại hệ thống Golden Shift.',
                        'trang_thai_ho_so' => 'san_sang_nhan_viec',
                        'diem_danh_gia' => 0.0,
                        'ngay_tao' => $now->format('Y-m-d H:i:s'),
                        'ngay_cap_nhat' => $now->format('Y-m-d H:i:s'),
                    ]);
                }
            }

            $candIds = $candidates->pluck('ma_nguoi_dung')->map(fn ($id) => (int) $id)->values()->all();

            // Nạp lịch sử đặt ca hiện có để không bao giờ xếp trùng khung giờ cho cùng 1 ứng viên
            $candidateBooked = [];
            $existingApps = DB::table('ho_so_ung_tuyen')
                ->join('ca_lam_viec', 'ho_so_ung_tuyen.ma_ca_lam', '=', 'ca_lam_viec.ma_ca_lam')
                ->select(
                    'ho_so_ung_tuyen.ma_nguoi_dung',
                    'ca_lam_viec.ma_ca_lam',
                    'ca_lam_viec.ngay_lam_viec',
                    'ca_lam_viec.gio_bat_dau',
                    'ca_lam_viec.gio_ket_thuc'
                )
                ->get();

            foreach ($existingApps as $ea) {
                $uid = (int) $ea->ma_nguoi_dung;
                $d = (string) $ea->ngay_lam_viec;
                $candidateBooked[$uid][$d][] = [
                    'ma_ca_lam' => (int) $ea->ma_ca_lam,
                    'start' => (string) $ea->gio_bat_dau,
                    'end' => (string) $ea->gio_ket_thuc,
                ];
            }

            // Đếm số ca đã hoàn thành của từng ứng viên để cân bằng xoay vòng đồng đều
            $completedCounts = [];
            foreach ($candIds as $cid) {
                $completedCounts[$cid] = DB::table('lich_su_cham_cong')
                    ->where('ma_nguoi_dung', $cid)
                    ->where('trang_thai_cham_cong', 'da_xac_nhan')
                    ->count();
            }

            $pickCandidates = function (int $needed, string $date, string $start, string $end, bool $preferLowHistory = true) use (&$candIds, &$candidateBooked, &$completedCounts): array {
                $pool = $candIds;
                shuffle($pool);
                if ($preferLowHistory) {
                    usort($pool, function ($a, $b) use ($completedCounts) {
                        return ($completedCounts[$a] ?? 0) <=> ($completedCounts[$b] ?? 0);
                    });
                }

                $selected = [];
                foreach ($pool as $uid) {
                    if (count($selected) >= $needed) {
                        break;
                    }
                    $conflict = false;
                    foreach ($candidateBooked[$uid][$date] ?? [] as $slot) {
                        if ($slot['start'] < $end && $slot['end'] > $start) {
                            $conflict = true;
                            break;
                        }
                    }
                    if (! $conflict) {
                        $selected[] = $uid;
                    }
                }

                return $selected;
            };

            $markBooked = function (int $uid, int $maCaLam, string $date, string $start, string $end) use (&$candidateBooked): void {
                $candidateBooked[$uid][$date][] = [
                    'ma_ca_lam' => $maCaLam,
                    'start' => $start,
                    'end' => $end,
                ];
            };

            $newConfirmedRecords = [];
            $todayShiftsForPendingCC = [];
            $violationSeedPool = [];

            // Sinh 9 ca làm việc mới cho MỖI nhà hàng trong 8 nhà hàng
            foreach ($restaurants as $rIdx => $nh) {
                $maNhaHang = (int) $nh->ma_nha_hang;
                $maQuanLy = (int) ($nh->ma_quan_ly ?: 2);

                // Tạo khung ngày quá khứ rải đều 09/2026, 10/2026, 11/2026 (luôn < 2026-11-22)
                $pastDates = [
                    sprintf('2026-09-%02d', min(28, 4 + ($rIdx * 3) + random_int(0, 2))),
                    sprintf('2026-10-%02d', min(29, 3 + ($rIdx * 3) + random_int(0, 2))),
                    sprintf('2026-11-%02d', min(18, 1 + ($rIdx * 2) + random_int(0, 1))),
                    sprintf('2026-11-%02d', min(21, 10 + $rIdx + random_int(0, 2))),
                ];

                // Riêng nhà hàng 1 & 2 có 1 ca sáng hôm nay 2026-11-22 (đã kết thúc 13:00) để có bản ghi chờ duyệt chấm công
                if ($maNhaHang <= 2) {
                    $pastDates[3] = '2026-11-22';
                }

                // --------------------------------------------------------
                // A. 4 CA HOÀN THÀNH (QUÁ KHỨ) CHO MỖI NHÀ HÀNG
                // --------------------------------------------------------
                foreach ($pastDates as $pIdx => $ngayLam) {
                    $posName = $positionNames[($rIdx + $pIdx) % count($positionNames)];
                    $posMeta = $positions[$posName];
                    $wage = random_int((int) ($posMeta['wage_range'][0] / 1000), (int) ($posMeta['wage_range'][1] / 1000)) * 1000;

                    if ($ngayLam === '2026-11-22') {
                        $gioBatDau = '08:00:00';
                        $gioKetThuc = '13:00:00';
                    } else {
                        $slot = $timeSlots[array_rand($timeSlots)];
                        $gioBatDau = $slot[0];
                        $gioKetThuc = $slot[1];
                    }

                    $soLuongTuyen = random_int(2, 3);
                    $picked = $pickCandidates(2, $ngayLam, $gioBatDau, $gioKetThuc, true);
                    if (empty($picked)) {
                        $slotAlt = ['14:00:00', '18:00:00'];
                        $gioBatDau = $slotAlt[0];
                        $gioKetThuc = $slotAlt[1];
                        $picked = $pickCandidates(2, $ngayLam, $gioBatDau, $gioKetThuc, true);
                    }

                    $shiftStartDt = Carbon::parse($ngayLam.' '.$gioBatDau, $tz);
                    $shiftEndDt = Carbon::parse($ngayLam.' '.$gioKetThuc, $tz);
                    $createdAt = $shiftStartDt->copy()->subDays(random_int(5, 8));

                    $maCaLam = DB::table('ca_lam_viec')->insertGetId([
                        'ma_nha_hang' => $maNhaHang,
                        'ten_vi_tri_tuyen_dung' => $posName,
                        'ngay_lam_viec' => $ngayLam,
                        'gio_bat_dau' => $gioBatDau,
                        'gio_ket_thuc' => $gioKetThuc,
                        'muc_luong_theo_gio' => $wage,
                        'so_luong_tuyen' => max($soLuongTuyen, count($picked)),
                        'so_luong_da_tuyen' => count($picked),
                        'yeu_cau_cong_viec' => $posMeta['req'],
                        'quyen_loi_ca_lam' => $posMeta['benefit'],
                        'trang_thai_ca' => 'hoan_thanh',
                        'ly_do_huy' => null,
                        'ngay_tao' => $createdAt->format('Y-m-d H:i:s'),
                        'ngay_cap_nhat' => $shiftEndDt->copy()->addMinutes(30)->format('Y-m-d H:i:s'),
                    ]);
                    $summary['shifts_created']++;

                    foreach ($picked as $cOrder => $candId) {
                        $markBooked($candId, $maCaLam, $ngayLam, $gioBatDau, $gioKetThuc);
                        $ngayNop = $shiftStartDt->copy()->subDays(random_int(2, 4))->setTime(random_int(9, 20), random_int(0, 59));
                        $ngayDuyet = $ngayNop->copy()->addHours(random_int(2, 10));
                        $note = $posMeta['app_notes'][array_rand($posMeta['app_notes'])];

                        $maUngTuyen = DB::table('ho_so_ung_tuyen')->insertGetId([
                            'ma_ca_lam' => $maCaLam,
                            'ma_nguoi_dung' => $candId,
                            'ngay_nop_don' => $ngayNop->format('Y-m-d H:i:s'),
                            'loi_nhan_ung_vien' => $note,
                            'trang_thai_ung_tuyen' => 'da_nhan',
                            'ghi_chu_nha_tuyen_dung' => 'Hồ sơ đạt yêu cầu. Mời bạn có mặt trước ca 15 phút để nhận vị trí.',
                            'nguoi_duyet' => $maQuanLy,
                            'ngay_duyet' => $ngayDuyet->format('Y-m-d H:i:s'),
                            'ngay_cap_nhat' => $ngayDuyet->format('Y-m-d H:i:s'),
                        ]);
                        $summary['apps_created']++;

                        // Chấm công thực tế: nếu là ca sáng 2026-11-22 (ứng viên đầu tiên) thì để trạng thái cho_xac_nhan (< 60 phút trước)
                        $isPendingToday = ($ngayLam === '2026-11-22' && $cOrder === 0);
                        if ($isPendingToday) {
                            $vao = $shiftStartDt->copy()->addMinutes(random_int(-4, 3));
                            $ra = $now->copy()->subMinutes(15);
                            $soGio = round(max(1.0, $vao->diffInMinutes($ra) / 60), 2);
                            $maChamCong = DB::table('lich_su_cham_cong')->insertGetId([
                                'ma_ung_tuyen' => $maUngTuyen,
                                'ma_nguoi_dung' => $candId,
                                'ma_ca_lam' => $maCaLam,
                                'thoi_gian_vao_thuc_te' => $vao->format('Y-m-d H:i:s'),
                                'thoi_gian_ra_thuc_te' => $ra->format('Y-m-d H:i:s'),
                                'so_gio_lam_thuc_te' => $soGio,
                                'trang_thai_cham_cong' => 'cho_xac_nhan',
                                'hinh_thuc_xac_thuc' => 'ma_qr',
                                'ghi_chu_cham_cong' => 'Ứng viên đã quét mã QR ra ca, đang chờ quản lý xác nhận giờ công.',
                                'nguoi_xac_nhan' => null,
                                'ngay_tao' => $ra->format('Y-m-d H:i:s'),
                            ]);
                            $summary['attendance_created']++;
                            $todayShiftsForPendingCC[] = [$maCaLam, $maQuanLy, $candId, $posName, $nh->ten_nha_hang];
                        } else {
                            $vao = $shiftStartDt->copy()->addMinutes(random_int(-8, 4));
                            $ra = $shiftEndDt->copy()->addMinutes(random_int(0, 10));
                            $soGio = round($vao->diffInMinutes($ra) / 60, 2);

                            $hasBonus = (random_int(1, 100) <= 25);
                            $bonusAmt = $hasBonus ? (random_int(0, 1) ? '20.000' : '30.000') : null;
                            $ccNote = $hasBonus
                                ? "Hoàn thành xuất sắc ca {$posName}, hỗ trợ tốt giờ cao điểm. Thưởng thêm: +{$bonusAmt} VNĐ"
                                : "Đã hoàn thành đầy đủ khung giờ ca {$posName} và kiểm tra bàn giao cuối ca.";

                            $maChamCong = DB::table('lich_su_cham_cong')->insertGetId([
                                'ma_ung_tuyen' => $maUngTuyen,
                                'ma_nguoi_dung' => $candId,
                                'ma_ca_lam' => $maCaLam,
                                'thoi_gian_vao_thuc_te' => $vao->format('Y-m-d H:i:s'),
                                'thoi_gian_ra_thuc_te' => $ra->format('Y-m-d H:i:s'),
                                'so_gio_lam_thuc_te' => $soGio,
                                'trang_thai_cham_cong' => 'da_xac_nhan',
                                'hinh_thuc_xac_thuc' => $authMethods[array_rand($authMethods)],
                                'ghi_chu_cham_cong' => $ccNote,
                                'nguoi_xac_nhan' => $maQuanLy,
                                'ngay_tao' => $ra->copy()->addMinutes(15)->format('Y-m-d H:i:s'),
                            ]);
                            $summary['attendance_created']++;
                            $completedCounts[$candId] = ($completedCounts[$candId] ?? 0) + 1;

                            $newConfirmedRecords[] = [
                                'ma_cham_cong' => $maChamCong,
                                'ma_ca_lam' => $maCaLam,
                                'ma_nha_hang' => $maNhaHang,
                                'ten_nha_hang' => $nh->ten_nha_hang,
                                'ten_vi_tri' => $posName,
                                'ma_quan_ly' => $maQuanLy,
                                'ma_nguoi_dung' => $candId,
                                'ngay_ra' => $ra,
                                'ngay_lam' => $ngayLam,
                            ];

                            $violationSeedPool[] = [
                                'ma_ca_lam' => $maCaLam,
                                'ma_nguoi_dung' => $candId,
                                'ma_quan_ly' => $maQuanLy,
                                'ten_nha_hang' => $nh->ten_nha_hang,
                                'ngay_lam' => $ngayLam,
                            ];
                        }
                    }

                    // Thêm 1 đơn phụ ở trạng thái 'tu_choi' cho ca hoàn thành đầu tiên mỗi nhà hàng để phong phú lịch sử đơn
                    if ($pIdx === 0) {
                        $extraReject = $pickCandidates(1, $ngayLam, $gioBatDau, $gioKetThuc, false);
                        if (! empty($extraReject)) {
                            $rUid = $extraReject[0];
                            $markBooked($rUid, $maCaLam, $ngayLam, $gioBatDau, $gioKetThuc);
                            $rNop = $shiftStartDt->copy()->subDays(2)->setTime(10, 15);
                            $rDuyet = $rNop->copy()->addHours(4);
                            DB::table('ho_so_ung_tuyen')->insert([
                                'ma_ca_lam' => $maCaLam,
                                'ma_nguoi_dung' => $rUid,
                                'ngay_nop_don' => $rNop->format('Y-m-d H:i:s'),
                                'loi_nhan_ung_vien' => $posMeta['app_notes'][0],
                                'trang_thai_ung_tuyen' => 'tu_choi',
                                'ghi_chu_nha_tuyen_dung' => $rejectNotes[array_rand($rejectNotes)],
                                'nguoi_duyet' => $maQuanLy,
                                'ngay_duyet' => $rDuyet->format('Y-m-d H:i:s'),
                                'ngay_cap_nhat' => $rDuyet->format('Y-m-d H:i:s'),
                            ]);
                            $summary['apps_created']++;
                        }
                    }
                }

                // --------------------------------------------------------
                // B. 2 CA ĐANG TUYỂN (TƯƠNG LAI / TỐI HÔM NAY) CHO MỖI NHÀ HÀNG
                // --------------------------------------------------------
                $openDates = [
                    $maNhaHang <= 2 ? '2026-11-22' : Carbon::parse('2026-11-23', $tz)->addDays($rIdx)->format('Y-m-d'),
                    Carbon::parse('2026-11-28', $tz)->addDays($rIdx * 2)->format('Y-m-d'),
                ];

                foreach ($openDates as $oIdx => $ngayLam) {
                    $posName = $positionNames[($rIdx + $oIdx + 1) % count($positionNames)];
                    $posMeta = $positions[$posName];
                    $wage = random_int((int) ($posMeta['wage_range'][0] / 1000), (int) ($posMeta['wage_range'][1] / 1000)) * 1000;
                    $slot = ($ngayLam === '2026-11-22') ? ['18:00:00', '22:30:00'] : $timeSlots[array_rand($timeSlots)];
                    $gioBatDau = $slot[0];
                    $gioKetThuc = $slot[1];

                    $soLuongTuyen = random_int(3, 4);
                    $approvedPick = $pickCandidates(1, $ngayLam, $gioBatDau, $gioKetThuc, false);
                    $soLuongDaTuyen = count($approvedPick); // tối đa 1 (< 3..4)

                    $createdAt = $now->copy()->subDays(random_int(1, 3));
                    $maCaLam = DB::table('ca_lam_viec')->insertGetId([
                        'ma_nha_hang' => $maNhaHang,
                        'ten_vi_tri_tuyen_dung' => $posName,
                        'ngay_lam_viec' => $ngayLam,
                        'gio_bat_dau' => $gioBatDau,
                        'gio_ket_thuc' => $gioKetThuc,
                        'muc_luong_theo_gio' => $wage,
                        'so_luong_tuyen' => $soLuongTuyen,
                        'so_luong_da_tuyen' => $soLuongDaTuyen,
                        'yeu_cau_cong_viec' => $posMeta['req'],
                        'quyen_loi_ca_lam' => $posMeta['benefit'],
                        'trang_thai_ca' => 'dang_tuyen',
                        'ly_do_huy' => null,
                        'ngay_tao' => $createdAt->format('Y-m-d H:i:s'),
                        'ngay_cap_nhat' => $now->format('Y-m-d H:i:s'),
                    ]);
                    $summary['shifts_created']++;

                    foreach ($approvedPick as $aUid) {
                        $markBooked($aUid, $maCaLam, $ngayLam, $gioBatDau, $gioKetThuc);
                        $nopAt = $now->copy()->subHours(random_int(12, 36));
                        $duyetAt = $now->copy()->subHours(random_int(2, 10));
                        DB::table('ho_so_ung_tuyen')->insert([
                            'ma_ca_lam' => $maCaLam,
                            'ma_nguoi_dung' => $aUid,
                            'ngay_nop_don' => $nopAt->format('Y-m-d H:i:s'),
                            'loi_nhan_ung_vien' => $posMeta['app_notes'][array_rand($posMeta['app_notes'])],
                            'trang_thai_ung_tuyen' => 'da_nhan',
                            'ghi_chu_nha_tuyen_dung' => 'Đã duyệt nhận ca! Vui lòng mặc đồng phục chỉnh tề và có mặt đúng giờ.',
                            'nguoi_duyet' => $maQuanLy,
                            'ngay_duyet' => $duyetAt->format('Y-m-d H:i:s'),
                            'ngay_cap_nhat' => $duyetAt->format('Y-m-d H:i:s'),
                        ]);
                        $summary['apps_created']++;
                    }

                    // Thêm 1-2 đơn 'cho_duyet' để quản lý các chi nhánh đều có đơn mới
                    $pendingPicks = $pickCandidates(2, $ngayLam, $gioBatDau, $gioKetThuc, false);
                    foreach ($pendingPicks as $pUid) {
                        $markBooked($pUid, $maCaLam, $ngayLam, $gioBatDau, $gioKetThuc);
                        $nopAt = $now->copy()->subHours(random_int(1, 8));
                        DB::table('ho_so_ung_tuyen')->insert([
                            'ma_ca_lam' => $maCaLam,
                            'ma_nguoi_dung' => $pUid,
                            'ngay_nop_don' => $nopAt->format('Y-m-d H:i:s'),
                            'loi_nhan_ung_vien' => $posMeta['app_notes'][array_rand($posMeta['app_notes'])],
                            'trang_thai_ung_tuyen' => 'cho_duyet',
                            'ghi_chu_nha_tuyen_dung' => null,
                            'nguoi_duyet' => null,
                            'ngay_duyet' => null,
                            'ngay_cap_nhat' => $nopAt->format('Y-m-d H:i:s'),
                        ]);
                        $summary['apps_created']++;
                    }

                    // Thêm 1 đơn 'da_huy_bo' cho ca mở thứ 2 để đủ 4 trạng thái ho_so_ung_tuyen
                    if ($oIdx === 1) {
                        $selfCancelPick = $pickCandidates(1, $ngayLam, $gioBatDau, $gioKetThuc, false);
                        if (! empty($selfCancelPick)) {
                            $cUid = $selfCancelPick[0];
                            $markBooked($cUid, $maCaLam, $ngayLam, $gioBatDau, $gioKetThuc);
                            $nopAt = $now->copy()->subDays(1);
                            DB::table('ho_so_ung_tuyen')->insert([
                                'ma_ca_lam' => $maCaLam,
                                'ma_nguoi_dung' => $cUid,
                                'ngay_nop_don' => $nopAt->format('Y-m-d H:i:s'),
                                'loi_nhan_ung_vien' => 'Em muốn đăng ký ca này nhưng vừa phát sinh lịch thi đột xuất ở trường.',
                                'trang_thai_ung_tuyen' => 'da_huy_bo',
                                'ghi_chu_nha_tuyen_dung' => 'Ứng viên chủ động xin rút đơn trước giờ chốt lịch.',
                                'nguoi_duyet' => $maQuanLy,
                                'ngay_duyet' => $now->copy()->subHours(5)->format('Y-m-d H:i:s'),
                                'ngay_cap_nhat' => $now->copy()->subHours(5)->format('Y-m-d H:i:s'),
                            ]);
                            $summary['apps_created']++;
                        }
                    }
                }

                // --------------------------------------------------------
                // C. 2 CA ĐÃ ĐỦ NGƯỜI (TƯƠNG LAI) CHO MỖI NHÀ HÀNG
                // --------------------------------------------------------
                $fullDates = [
                    Carbon::parse('2026-11-24', $tz)->addDays($rIdx)->format('Y-m-d'),
                    Carbon::parse('2026-12-03', $tz)->addDays($rIdx)->format('Y-m-d'),
                ];

                foreach ($fullDates as $fIdx => $ngayLam) {
                    $posName = $positionNames[($rIdx + $fIdx + 2) % count($positionNames)];
                    $posMeta = $positions[$posName];
                    $wage = random_int((int) ($posMeta['wage_range'][0] / 1000), (int) ($posMeta['wage_range'][1] / 1000)) * 1000;
                    $slot = $timeSlots[array_rand($timeSlots)];
                    $gioBatDau = $slot[0];
                    $gioKetThuc = $slot[1];

                    $picked = $pickCandidates(2, $ngayLam, $gioBatDau, $gioKetThuc, false);
                    $soLuong = max(1, count($picked));

                    $maCaLam = DB::table('ca_lam_viec')->insertGetId([
                        'ma_nha_hang' => $maNhaHang,
                        'ten_vi_tri_tuyen_dung' => $posName,
                        'ngay_lam_viec' => $ngayLam,
                        'gio_bat_dau' => $gioBatDau,
                        'gio_ket_thuc' => $gioKetThuc,
                        'muc_luong_theo_gio' => $wage,
                        'so_luong_tuyen' => $soLuong,
                        'so_luong_da_tuyen' => $soLuong,
                        'yeu_cau_cong_viec' => $posMeta['req'],
                        'quyen_loi_ca_lam' => $posMeta['benefit'],
                        'trang_thai_ca' => 'da_du_nguoi',
                        'ly_do_huy' => null,
                        'ngay_tao' => $now->copy()->subDays(3)->format('Y-m-d H:i:s'),
                        'ngay_cap_nhat' => $now->copy()->subHours(6)->format('Y-m-d H:i:s'),
                    ]);
                    $summary['shifts_created']++;

                    foreach ($picked as $aUid) {
                        $markBooked($aUid, $maCaLam, $ngayLam, $gioBatDau, $gioKetThuc);
                        $nopAt = $now->copy()->subDays(2);
                        $duyetAt = $now->copy()->subHours(random_int(4, 18));
                        DB::table('ho_so_ung_tuyen')->insert([
                            'ma_ca_lam' => $maCaLam,
                            'ma_nguoi_dung' => $aUid,
                            'ngay_nop_don' => $nopAt->format('Y-m-d H:i:s'),
                            'loi_nhan_ung_vien' => $posMeta['app_notes'][array_rand($posMeta['app_notes'])],
                            'trang_thai_ung_tuyen' => 'da_nhan',
                            'ghi_chu_nha_tuyen_dung' => 'Ca làm đã chốt danh sách nhân sự chính thức.',
                            'nguoi_duyet' => $maQuanLy,
                            'ngay_duyet' => $duyetAt->format('Y-m-d H:i:s'),
                            'ngay_cap_nhat' => $duyetAt->format('Y-m-d H:i:s'),
                        ]);
                        $summary['apps_created']++;
                    }
                }

                // --------------------------------------------------------
                // D. 1 CA ĐÃ HỦY (KÈM LÝ DO CHI TIẾT) CHO MỖI NHÀ HÀNG
                // --------------------------------------------------------
                $cancelDate = ($maNhaHang % 2 === 0)
                    ? Carbon::parse('2026-10-20', $tz)->addDays($maNhaHang * 2)->format('Y-m-d')
                    : Carbon::parse('2026-11-27', $tz)->addDays($maNhaHang)->format('Y-m-d');
                $posNameCancel = $positionNames[($rIdx + 3) % count($positionNames)];
                $posMetaCancel = $positions[$posNameCancel];
                $slotCancel = $timeSlots[array_rand($timeSlots)];
                $lyDoHuy = $cancelReasons[($rIdx) % count($cancelReasons)];

                $maCaHuy = DB::table('ca_lam_viec')->insertGetId([
                    'ma_nha_hang' => $maNhaHang,
                    'ten_vi_tri_tuyen_dung' => $posNameCancel,
                    'ngay_lam_viec' => $cancelDate,
                    'gio_bat_dau' => $slotCancel[0],
                    'gio_ket_thuc' => $slotCancel[1],
                    'muc_luong_theo_gio' => 33000,
                    'so_luong_tuyen' => 2,
                    'so_luong_da_tuyen' => 0,
                    'yeu_cau_cong_viec' => $posMetaCancel['req'],
                    'quyen_loi_ca_lam' => $posMetaCancel['benefit'],
                    'trang_thai_ca' => 'da_huy',
                    'ly_do_huy' => $lyDoHuy,
                    'ngay_tao' => Carbon::parse($cancelDate, $tz)->subDays(6)->format('Y-m-d H:i:s'),
                    'ngay_cap_nhat' => Carbon::parse($cancelDate, $tz)->subDays(2)->format('Y-m-d H:i:s'),
                ]);
                $summary['shifts_created']++;

                $cancelAppPick = $pickCandidates(1, $cancelDate, $slotCancel[0], $slotCancel[1], false);
                if (! empty($cancelAppPick)) {
                    $cUid = $cancelAppPick[0];
                    $markBooked($cUid, $maCaHuy, $cancelDate, $slotCancel[0], $slotCancel[1]);
                    $nopAt = Carbon::parse($cancelDate, $tz)->subDays(4);
                    DB::table('ho_so_ung_tuyen')->insert([
                        'ma_ca_lam' => $maCaHuy,
                        'ma_nguoi_dung' => $cUid,
                        'ngay_nop_don' => $nopAt->format('Y-m-d H:i:s'),
                        'loi_nhan_ung_vien' => $posMetaCancel['app_notes'][0],
                        'trang_thai_ung_tuyen' => 'cho_duyet',
                        'ghi_chu_nha_tuyen_dung' => 'Ca làm việc đã hủy: '.$lyDoHuy,
                        'nguoi_duyet' => $maQuanLy,
                        'ngay_duyet' => Carbon::parse($cancelDate, $tz)->subDays(2)->format('Y-m-d H:i:s'),
                        'ngay_cap_nhat' => Carbon::parse($cancelDate, $tz)->subDays(2)->format('Y-m-d H:i:s'),
                    ]);
                    $summary['apps_created']++;
                }
            }

            // --------------------------------------------------------
            // 2. SINH ĐÁNH GIÁ 2 CHIỀU CHO CÁC CA ĐÃ HOÀN THÀNH MỚI TẠO
            // --------------------------------------------------------
            foreach ($newConfirmedRecords as $rec) {
                $raDt = $rec['ngay_ra'];
                $maCa = $rec['ma_ca_lam'];
                $maQl = $rec['ma_quan_ly'];
                $maUv = $rec['ma_nguoi_dung'];

                // Ứng viên đánh giá Nhà hàng (3..5 sao)
                $existsUvReview = DB::table('danh_gia_ca_lam')
                    ->where('ma_ca_lam', $maCa)
                    ->where('ma_nguoi_danh_gia', $maUv)
                    ->where('loai_danh_gia', 'ung_vien_danh_gia_nha_hang')
                    ->exists();

                if (! $existsUvReview) {
                    $roll = random_int(1, 100);
                    $stars = $roll <= 60 ? 5 : ($roll <= 90 ? 4 : 3);
                    $cmtList = $candToRestComments[$stars];
                    DB::table('danh_gia_ca_lam')->insert([
                        'ma_ca_lam' => $maCa,
                        'ma_nguoi_danh_gia' => $maUv,
                        'ma_nguoi_duoc_danh_gia' => $maQl,
                        'loai_danh_gia' => 'ung_vien_danh_gia_nha_hang',
                        'so_sao' => $stars,
                        'tieu_chi_thai_do' => $stars,
                        'tieu_chi_chuyen_can' => min(5, $stars + random_int(0, 1)),
                        'tieu_chi_hieu_suat' => $stars,
                        'noi_dung_nhan_xet' => $cmtList[array_rand($cmtList)],
                        'ngay_danh_gia' => $raDt->copy()->addMinutes(random_int(15, 90))->format('Y-m-d H:i:s'),
                    ]);
                    $summary['reviews_created']++;
                }

                // Nhà hàng đánh giá Ứng viên (3..5 sao)
                $existsQlReview = DB::table('danh_gia_ca_lam')
                    ->where('ma_ca_lam', $maCa)
                    ->where('ma_nguoi_duoc_danh_gia', $maUv)
                    ->where('loai_danh_gia', 'nha_hang_danh_gia_ung_vien')
                    ->exists();

                if (! $existsQlReview) {
                    $roll = random_int(1, 100);
                    $stars = $roll <= 65 ? 5 : ($roll <= 92 ? 4 : 3);
                    $thaiDo = min(5, max(3, $stars + random_int(0, 1)));
                    $chuyenCan = min(5, max(3, $stars + random_int(-1, 1)));
                    $hieuSuat = $stars;
                    $cmtList = $restToCandComments[$stars];

                    DB::table('danh_gia_ca_lam')->insert([
                        'ma_ca_lam' => $maCa,
                        'ma_nguoi_danh_gia' => $maQl,
                        'ma_nguoi_duoc_danh_gia' => $maUv,
                        'loai_danh_gia' => 'nha_hang_danh_gia_ung_vien',
                        'so_sao' => $stars,
                        'tieu_chi_thai_do' => $thaiDo,
                        'tieu_chi_chuyen_can' => $chuyenCan,
                        'tieu_chi_hieu_suat' => $hieuSuat,
                        'noi_dung_nhan_xet' => $cmtList[array_rand($cmtList)],
                        'ngay_danh_gia' => $raDt->copy()->addMinutes(random_int(25, 180))->format('Y-m-d H:i:s'),
                    ]);
                    $summary['reviews_created']++;
                }
            }

            // --------------------------------------------------------
            // 3. ĐỒNG BỘ SỔ THU NHẬP & ĐIỂM ĐÁNH GIÁ CHO TOÀN BỘ ỨNG VIÊN
            // --------------------------------------------------------
            $soThuNhapCtrl = new SoThuNhapController;
            foreach ($candIds as $cid) {
                $confirmedRows = LichSuChamCong::with('caLamViec')
                    ->where('ma_nguoi_dung', $cid)
                    ->where('trang_thai_cham_cong', 'da_xac_nhan')
                    ->get();

                $soThuNhapCtrl->syncSoThuNhapFromAttendance($cid, $confirmedRows);

                // Cập nhật diem_danh_gia trung bình chính xác vào ho_so_ung_vien
                $stats = DanhGiaCaLam::tinhDiemTrungBinhUngVien($cid);
                DB::table('ho_so_ung_vien')
                    ->where('ma_nguoi_dung', $cid)
                    ->update([
                        'diem_danh_gia' => $stats['diem_tb'],
                        'ngay_cap_nhat' => $now->format('Y-m-d H:i:s'),
                    ]);
            }

            // Điền đầy đủ ngay_thanh_toan & ghi_chu cho tất cả các bản ghi so_thu_nhap
            $stnRows = DB::table('so_thu_nhap')->get();
            foreach ($stnRows as $stn) {
                if (empty($stn->ngay_thanh_toan) || empty($stn->ghi_chu)) {
                    $payDate = $now->format('Y-m-d H:i:s');
                    if (preg_match('/(\d{2})\/(\d{4})/', (string) $stn->ky_luong, $m)) {
                        $monthEnd = Carbon::createFromDate((int) $m[2], (int) $m[1], 1, $tz)->endOfMonth()->setTime(20, 0);
                        $payDate = $monthEnd->greaterThan($now) ? $now->format('Y-m-d H:i:s') : $monthEnd->format('Y-m-d H:i:s');
                    }

                    DB::table('so_thu_nhap')
                        ->where('ma_so_thu_nhap', $stn->ma_so_thu_nhap)
                        ->update([
                            'trang_thai_thanh_toan' => 'da_thanh_toan',
                            'ngay_thanh_toan' => $stn->ngay_thanh_toan ?: $payDate,
                            'ghi_chu' => $stn->ghi_chu ?: 'Đã tổng hợp từ lịch sử chấm công và thanh toán tiền mặt trực tiếp tại nhà hàng.',
                        ]);
                }
            }

            // --------------------------------------------------------
            // 4. BỔ SUNG BÁO CÁO VI PHẠM & THÔNG BÁO MẪU
            // --------------------------------------------------------
            $sampleReports = [
                [
                    'loai_vi_pham' => 'di_muon_ve_som',
                    'muc_do' => 'nhe',
                    'mo_ta' => 'Ứng viên check-in muộn 18 phút so với giờ bắt đầu ca tối, đã nhắc nhở và bổ sung bù giờ cuối ca.',
                    'phat' => 0,
                    'status' => 'da_xuly',
                    'xu_ly' => 'Nhắc nhở cảnh cáo lần 1 và ghi nhận ứng viên đã ở lại bù đủ 18 phút cuối ca.',
                    'ghi_chu' => 'Quản lý xác nhận không ảnh hưởng vận hành sảnh.',
                    'manager_reports_candidate' => true,
                ],
                [
                    'loai_vi_pham' => 'thai_do_khong_phu_hop',
                    'muc_do' => 'trung_binh',
                    'mo_ta' => 'Ứng viên sử dụng điện thoại riêng trong khu vực đón khách giờ cao điểm, gây phản ánh từ bàn VIP.',
                    'phat' => 50000,
                    'status' => 'cho_xuly',
                    'xu_ly' => null,
                    'ghi_chu' => null,
                    'manager_reports_candidate' => true,
                ],
                [
                    'loai_vi_pham' => 'mo_ta_khong_dung',
                    'muc_do' => 'nhe',
                    'mo_ta' => 'Ứng viên phản ánh ca đăng tuyển vị trí Pha chế nhưng được điều chuyển hỗ trợ tiếp thực sảnh ngoài dự kiến.',
                    'phat' => 0,
                    'status' => 'tu_choi',
                    'xu_ly' => null,
                    'ghi_chu' => 'Sau khi kiểm tra lịch phân ca và xác nhận phụ cấp hỗ trợ tăng cường đã được chi trả đầy đủ.',
                    'manager_reports_candidate' => false,
                ],
                [
                    'loai_vi_pham' => 'vi_pham_dong_phuc',
                    'muc_do' => 'nhe',
                    'mo_ta' => 'Ứng viên mang giày không đúng quy định an toàn chống trượt khu vực bếp chi nhánh.',
                    'phat' => 30000,
                    'status' => 'cho_xuly',
                    'xu_ly' => null,
                    'ghi_chu' => null,
                    'manager_reports_candidate' => true,
                ],
                [
                    'loai_vi_pham' => 'bung_ca',
                    'muc_do' => 'nghiem_trong',
                    'mo_ta' => 'Vắng mặt không báo trước ở ca dự bị cuối tuần làm thiếu vị trí phụ bếp đột xuất.',
                    'phat' => 100000,
                    'status' => 'da_xuly',
                    'xu_ly' => 'Cảnh cáo mức độ nghiêm trọng trên hệ thống và tạm khấu trừ tiền thưởng chuyên cần kỳ lương.',
                    'ghi_chu' => 'Đã gọi điện xác minh với quản lý chi nhánh và lưu hồ sơ theo dõi.',
                    'manager_reports_candidate' => true,
                ],
            ];

            if (! empty($violationSeedPool)) {
                shuffle($violationSeedPool);
                foreach ($sampleReports as $idx => $rep) {
                    $targetSeed = $violationSeedPool[$idx % count($violationSeedPool)];
                    $createdReportAt = Carbon::parse($targetSeed['ngay_lam'], $tz)->addDays(1)->setTime(10 + $idx, 20);
                    if ($createdReportAt->greaterThan($now)) {
                        $createdReportAt = $now->copy()->subHours(6 - $idx);
                    }

                    DB::table('bao_cao_vi_pham')->insert([
                        'ma_nguoi_vi_pham' => $rep['manager_reports_candidate'] ? $targetSeed['ma_nguoi_dung'] : $targetSeed['ma_quan_ly'],
                        'ma_nguoi_bao_cao' => $rep['manager_reports_candidate'] ? $targetSeed['ma_quan_ly'] : $targetSeed['ma_nguoi_dung'],
                        'ma_ca_lam' => $targetSeed['ma_ca_lam'],
                        'loai_vi_pham' => $rep['loai_vi_pham'],
                        'muc_do_nghiem_trong' => $rep['muc_do'],
                        'mo_ta_chi_tiet' => $rep['mo_ta'],
                        'hinh_anh_minh_chung' => null,
                        'so_tien_phat_du_kien' => $rep['phat'],
                        'trang_thai_xu_ly' => $rep['status'],
                        'hinh_thuc_xu_ly' => $rep['xu_ly'],
                        'ghi_chu_admin' => $rep['ghi_chu'],
                        'ngay_lap_bao_cao' => $createdReportAt->format('Y-m-d H:i:s'),
                    ]);
                    $summary['violations_created']++;
                }
            }

            // Thông báo cho 2 ca chấm công chờ duyệt hôm nay
            foreach ($todayShiftsForPendingCC as $info) {
                [$maCa, $maQl, $maUv, $posName, $tenNh] = $info;
                DB::table('thong_bao')->insert([
                    'ma_nguoi_dung' => $maQl,
                    'tieu_de' => 'Ứng viên vừa ra ca chờ duyệt chấm công',
                    'noi_dung' => "Ứng viên vừa hoàn thành ra ca [{$posName}] tại {$tenNh}. Vui lòng kiểm tra và xác nhận giờ công.",
                    'duong_dan_lien_ket' => '/quan-ly/cham-cong',
                    'da_doc' => 0,
                    'created_at' => $now->copy()->subMinutes(10)->format('Y-m-d H:i:s'),
                ]);
                $summary['notifications_created']++;
            }

            // Thông báo kết quả sổ thu nhập mới nhất cho mỗi ứng viên
            foreach ($candIds as $cid) {
                DB::table('thong_bao')->insert([
                    'ma_nguoi_dung' => $cid,
                    'tieu_de' => 'Sổ thu nhập & Lịch sử ca làm đã được cập nhật',
                    'noi_dung' => 'Dữ liệu giờ công và tiền lương các ca làm việc đã hoàn thành của bạn đã được tổng hợp đầy đủ trong Sổ thu nhập.',
                    'duong_dan_lien_ket' => '/ung-vien/so-thu-nhap',
                    'da_doc' => 0,
                    'created_at' => $now->format('Y-m-d H:i:s'),
                ]);
                $summary['notifications_created']++;
            }
        });

        if ($this->command) {
            $this->command->info(sprintf(
                'RandomOperationsSeeder hoàn tất: +%d ca làm việc, +%d hồ sơ ứng tuyển, +%d lịch sử chấm công, +%d đánh giá 2 chiều, +%d báo cáo vi phạm, +%d thông báo.',
                $summary['shifts_created'],
                $summary['apps_created'],
                $summary['attendance_created'],
                $summary['reviews_created'],
                $summary['violations_created'],
                $summary['notifications_created']
            ));
        }
    }
}
