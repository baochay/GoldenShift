<!-- ================================================================
     MODAL GỬI BÁO CÁO VI PHẠM (KHIẾU NẠI & KỶ LUẬT) - GOLDEN SHIFT
================================================================ -->
<div id="modalBaoCaoViPham" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 520px; border-radius: 18px; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.25); animation: zoomIn 0.2s ease;">
        <!-- Header Modal -->
        <div style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #ffffff; padding: 20px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #fff;">Báo Cáo Vi Phạm &amp; Khiếu Nại</h3>
                    <p style="margin: 3px 0 0; font-size: 12.5px; color: #fecaca;">Gửi thông tin xác thực để Ban Quản Trị xem xét xử lý</p>
                </div>
            </div>
            <button type="button" onclick="closeReportModal()" style="background: transparent; border: none; color: #ffffff; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Form Báo Cáo -->
        <form id="formSubmitReport" action="{{ route('bao_cao_vi_pham.store') }}" method="POST" enctype="multipart/form-data" style="margin: 0; padding: 24px;">
            @csrf
            <input type="hidden" name="ma_ca_lam" id="inputReportMaCaLam" value="">
            <input type="hidden" name="ma_nguoi_vi_pham" id="inputReportTargetUser" value="">

            <!-- Card Tóm tắt đối tượng bị báo cáo -->
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 11.5px; color: #991b1b; font-weight: 700; text-transform: uppercase;">ĐỐI TƯỢNG BỊ BÁO CÁO</div>
                    <div style="font-size: 15px; font-weight: 800; color: #b91c1c; margin-top: 2px;" id="modalReportTargetName">Tên đối tượng</div>
                    <div style="font-size: 12px; color: #dc2626; margin-top: 2px;" id="modalReportShiftName">Ca làm việc liên quan</div>
                </div>
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #dc2626; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>

            <!-- CHỌN LÝ DO VI PHẠM (Được cập nhật tự động theo vai trò) -->
            <div style="margin-bottom: 16px;">
                <label for="selectReportReason" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    Lý Do Vi Phạm <span style="color: #dc2626;">*</span>
                </label>
                <select name="loai_vi_pham" id="selectReportReason" required style="width: 100%; height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; font-size: 13.5px; outline: none; background: #fff;">
                    <option value="">-- Chọn lý do vi phạm --</option>
                </select>
            </div>

            <!-- MÔ TẢ CHI TIẾT SỰ VIỆC -->
            <div style="margin-bottom: 16px;">
                <label for="textareaReportDesc" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    Mô Tả Chi Tiết Diễn Biến Sự Việc <span style="color: #dc2626;">*</span>
                </label>
                <textarea name="mo_ta_chi_tiet" id="textareaReportDesc" rows="3" required placeholder="Nêu rõ thời gian, địa điểm, sự việc đã xảy ra và thiệt hại (nếu có)..." style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; font-size: 13px; outline: none; font-family: inherit; line-height: 1.4;"></textarea>
            </div>

            <!-- TẢI LÊN ẢNH MINH CHỨNG -->
            <div style="margin-bottom: 22px;">
                <label for="inputReportImage" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    Hình Ảnh Minh Chứng (Tùy chọn)
                </label>
                <input type="file" name="hinh_anh_minh_chung" id="inputReportImage" accept="image/png, image/jpeg, image/jpg, image/webp" style="width: 100%; font-size: 12.5px; color: #64748b;">
                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 4px;">Hỗ trợ định dạng JPG, PNG, WEBP. Tối đa 5MB.</div>
            </div>

            <!-- Nút hành động -->
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeReportModal()" class="btn btn-outline btn-sm" style="border-radius: 8px; padding: 8px 18px;">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn btn-sm" style="background: #dc2626; color: #fff; border: none; border-radius: 8px; padding: 8px 22px; font-weight: 700;">
                    <i class="fa-solid fa-flag"></i> Gửi Báo Cáo
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const REASONS_MANAGER = [
        { value: 'bung_ca',               text: 'Bùng ca / Tự ý bỏ ca không báo trước' },
        { value: 'di_muon_ve_som',        text: 'Đi muộn / Về sớm nhiều lần' },
        { value: 'thai_do_khong_phu_hop', text: 'Thái độ làm việc kém / Không tuân thủ nội quy' },
        { value: 'gay_thiet_hai',         text: 'Gây hư hỏng / Thiệt hại tài sản nhà hàng' },
        { value: 'ly_do_khac',            text: 'Lý do khác' }
    ];

    const REASONS_CANDIDATE = [
        { value: 'sai_luong_thu_nhap',       text: 'Gian lận giờ làm / Không thanh toán đúng mức lương' },
        { value: 'mo_ta_khong_dung',         text: 'Phân công công việc không đúng như mô tả ca làm' },
        { value: 'thai_do_chen_ep',          text: 'Quản lý có thái độ chèn ép / Quấy rối' },
        { value: 'moi_truong_khong_an_toan', text: 'Môi trường làm việc không đảm bảo an toàn' },
        { value: 'ly_do_khac',               text: 'Lý do khác' }
    ];

    window.openReportModal = function(opts) {
        opts = opts || {};
        document.getElementById('inputReportMaCaLam').value = opts.maCaLam || '';
        document.getElementById('inputReportTargetUser').value = opts.targetUserId || '';
        document.getElementById('modalReportTargetName').innerText = opts.targetName || 'Đối tượng vi phạm';
        document.getElementById('modalReportShiftName').innerText = opts.shiftName ? ('Ca: ' + opts.shiftName) : '';

        // Tự động phân chia danh sách lý do theo phân hệ
        const select = document.getElementById('selectReportReason');
        select.innerHTML = '<option value="">-- Chọn lý do vi phạm --</option>';

        const isManager = Boolean(opts.isManager || opts.role === 'quan_ly_nha_hang' || opts.role === 'quan_tri_vien');
        const reasonList = isManager ? REASONS_MANAGER : REASONS_CANDIDATE;

        reasonList.forEach(function(item) {
            const opt = document.createElement('option');
            opt.value = item.value;
            opt.textContent = item.text;
            if (opts.defaultReason && opts.defaultReason === item.value) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });

        document.getElementById('textareaReportDesc').value = '';
        document.getElementById('inputReportImage').value = '';

        document.getElementById('modalBaoCaoViPham').style.display = 'flex';
    };

    window.closeReportModal = function() {
        document.getElementById('modalBaoCaoViPham').style.display = 'none';
    };
})();
</script>
