/**
 * ====================================================================
 * GOLDEN STARS RECRUITMENT - CLIENT INTERACTION SCRIPTS
 * ====================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Quản lý Modal Ứng tuyển nhanh
    const modal = document.getElementById('modalUngTuyen');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const openApplyButtons = document.querySelectorAll('.btn-open-apply');
    const formUngTuyenNhanh = document.getElementById('formUngTuyenNhanh');
    const modalJobTitle = document.getElementById('modalJobTitle');
    const modalJobSub = document.getElementById('modalJobSub');
    const inputMaCaLam = document.getElementById('inputMaCaLam');
    const modalAlert = document.getElementById('modalAlert');
    const submitApplyBtn = document.getElementById('submitApplyBtn');

    // Hàm mở modal
    function openModal(id, title, restaurant) {
        if (!modal) return;
        if (inputMaCaLam) inputMaCaLam.value = id;
        if (modalJobTitle) modalJobTitle.textContent = title || 'Đăng Ký Nhận Ca Làm Việc';
        if (modalJobSub) modalJobSub.textContent = 'Chi nhánh: ' + (restaurant || 'Golden Stars F&B');
        if (modalAlert) {
            modalAlert.style.display = 'none';
            modalAlert.className = 'alert-box';
        }
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    // Hàm đóng modal
    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Gắn sự kiện cho các nút mở ứng tuyển
    openApplyButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            const title = btn.getAttribute('data-title');
            const restaurant = btn.getAttribute('data-restaurant');
            openModal(id, title, restaurant);
        });
    });

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    // Đóng khi click ngoài modal box
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    // Đóng khi nhấn phím Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
            closeModal();
        }
    });

    // 2. Xử lý gửi hồ sơ qua AJAX trong modal
    if (formUngTuyenNhanh) {
        formUngTuyenNhanh.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(formUngTuyenNhanh);

            if (submitApplyBtn) {
                submitApplyBtn.disabled = true;
                submitApplyBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang gửi hồ sơ...';
            }

            try {
                // Xác định đường dẫn gốc
                const baseUrl = window.location.origin + window.location.pathname.replace(/\/(ca-lam-viec|dashboard).*$/, '');
                const targetUrl = baseUrl.replace(/\/$/, '') + '/ung-tuyen/nop-don';

                const response = await fetch(targetUrl, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (modalAlert) {
                    modalAlert.style.display = 'block';
                    if (data.success) {
                        modalAlert.className = 'alert-box success';
                        modalAlert.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + data.message;
                        formUngTuyenNhanh.reset();
                        setTimeout(() => {
                            closeModal();
                        }, 2200);
                    } else {
                        modalAlert.className = 'alert-box error';
                        modalAlert.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + (data.message || 'Đã xảy ra lỗi khi gửi.');
                    }
                }
            } catch (error) {
                if (modalAlert) {
                    modalAlert.style.display = 'block';
                    modalAlert.className = 'alert-box error';
                    modalAlert.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Không thể kết nối đến máy chủ. Vui lòng thử lại sau.';
                }
            } finally {
                if (submitApplyBtn) {
                    submitApplyBtn.disabled = false;
                    submitApplyBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Gửi Hồ Sơ Ứng Tuyển';
                }
            }
        });
    }

    // 3. Xử lý form nộp đơn tại trang chi tiết ca làm việc
    const formDetail = document.getElementById('formUngTuyenTrangChiTiet');
    const detailAlert = document.getElementById('detailAlert');
    const detailSubmitBtn = document.getElementById('detailSubmitBtn');

    if (formDetail) {
        formDetail.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(formDetail);

            if (detailSubmitBtn) {
                detailSubmitBtn.disabled = true;
                detailSubmitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang nộp hồ sơ...';
            }

            try {
                const baseUrl = window.location.origin + window.location.pathname.replace(/\/(ca-lam-viec|dashboard).*$/, '');
                const targetUrl = baseUrl.replace(/\/$/, '') + '/ung-tuyen/nop-don';

                const response = await fetch(targetUrl, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (detailAlert) {
                    detailAlert.style.display = 'block';
                    if (data.success) {
                        detailAlert.className = 'alert-box success';
                        detailAlert.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + data.message;
                        formDetail.reset();
                    } else {
                        detailAlert.className = 'alert-box error';
                        detailAlert.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + data.message;
                    }
                }
            } catch (err) {
                if (detailAlert) {
                    detailAlert.style.display = 'block';
                    detailAlert.className = 'alert-box error';
                    detailAlert.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Có lỗi xảy ra trong quá trình nộp đơn.';
                }
            } finally {
                if (detailSubmitBtn) {
                    detailSubmitBtn.disabled = false;
                    detailSubmitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Gửi Hồ Sơ Ngay';
                }
            }
        });
    }

    // 4. HỆ THỐNG THÔNG BÁO IN-APP (BELL & DROPDOWN)
    // Toàn bộ logic hiển thị dropdown thông báo, cập nhật số lượng và polling real-time
    // đã được thống nhất và quản lý tập trung tại resources/views/layouts/app.blade.php
    // để đảm bảo giao diện đồng bộ tuyệt đối trên tất cả các phân hệ (Quản lý, Ứng viên, Quản trị viên).

    // 5. Quản lý Toggle Menu Điều Hướng Trên Mobile (Hamburger Menu)
    const mobileNavBtn = document.getElementById('btnMobileNavToggle');
    const gsNav = document.querySelector('.gs-nav');
    if (mobileNavBtn && gsNav) {
        mobileNavBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = gsNav.classList.toggle('is-open');
            mobileNavBtn.classList.toggle('active', isOpen);
            mobileNavBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            mobileNavBtn.innerHTML = isOpen ? '<i class="fa-solid fa-xmark"></i>' : '<i class="fa-solid fa-bars"></i>';
        });

        // Đóng menu khi bấm ra ngoài
        document.addEventListener('click', (e) => {
            if (!gsNav.contains(e.target) && !mobileNavBtn.contains(e.target)) {
                gsNav.classList.remove('is-open');
                mobileNavBtn.classList.remove('active');
                mobileNavBtn.setAttribute('aria-expanded', 'false');
                mobileNavBtn.innerHTML = '<i class="fa-solid fa-bars"></i>';
            }
        });

        // Đóng menu khi bấm vào bất kỳ nav-item nào
        gsNav.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                gsNav.classList.remove('is-open');
                mobileNavBtn.classList.remove('active');
                mobileNavBtn.setAttribute('aria-expanded', 'false');
                mobileNavBtn.innerHTML = '<i class="fa-solid fa-bars"></i>';
            });
        });
    }
});
