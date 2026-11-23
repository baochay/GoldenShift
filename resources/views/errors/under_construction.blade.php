@extends('layouts.app')

@section('title', ($featureName ?? 'Tính năng') . ' - Đang trong quá trình phát triển | Golden Shift')

@section('content')
@php
    $currentUser = session('user') ?? auth()->user();
    $userRole = is_array($currentUser) ? ($currentUser['vai_tro'] ?? '') : ($currentUser->vai_tro ?? '');
    $featureName = $featureName ?? 'Chức năng';
    $featureDesc = $featureDesc ?? "Chức năng '{$featureName}' đang được đội ngũ phát triển hoàn thiện và sẽ sớm cập nhật trong thời gian tới. Vui lòng quay lại sau!";
    
    $homeUrl = url('/');
    $homeText = 'Về Trang Chủ';
    $homeIcon = 'fa-solid fa-house';

    if ($userRole === 'quan_ly_nha_hang') {
        $homeUrl = url('/quan-ly/ca-lam-viec');
        $homeText = 'Về Quản Lý Ca Làm';
        $homeIcon = 'fa-solid fa-calendar-days';
    } elseif ($userRole === 'quan_tri_vien') {
        $homeUrl = url('/admin/xet-duyet-quan-ly');
        $homeText = 'Về Quản Trị Hệ Thống';
        $homeIcon = 'fa-solid fa-shield-halved';
    }
@endphp

<div class="under-construction-wrapper" style="padding: 4rem 1rem; min-height: 70vh; display: flex; align-items: center; justify-content: center;">
    <div class="gs-container" style="max-width: 760px; margin: 0 auto; width: 100%;">
        <div class="uc-card glass-panel" style="background: #ffffff; border-radius: 20px; padding: 3rem 2.5rem; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.06); border: 1px solid #f1e6d0; position: relative; overflow: hidden;">
            
            <!-- AMBIENT GLOW & DECORATIVE ELEMENTS -->
            <div class="uc-glow-bg" style="position: absolute; top: -50px; left: 50%; transform: translateX(-50%); width: 220px; height: 220px; background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, rgba(255,255,255,0) 70%); border-radius: 50%; pointer-events: none;"></div>
            
            <!-- ICON BOX WITH BADGE -->
            <div class="uc-icon-container" style="position: relative; width: 90px; height: 90px; margin: 0 auto 1.5rem;">
                <div class="uc-icon-circle" style="width: 90px; height: 90px; border-radius: 50%; background: linear-gradient(135deg, #fffdf5 0%, #fef3c7 100%); border: 2px solid #fde68a; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: #d97706; box-shadow: 0 10px 25px rgba(217, 119, 6, 0.15);">
                    <i class="fa-solid fa-person-digging uc-main-icon"></i>
                </div>
            </div>

            <!-- STATUS BADGE -->
            <div class="uc-status-badge" style="display: inline-flex; align-items: center; gap: 8px; background: #fffbeb; border: 1px solid #fde68a; color: #b45309; padding: 6px 16px; border-radius: 20px; font-size: 0.82rem; font-weight: 700; margin-bottom: 1.25rem;">
                <span class="pulse-dot" style="width: 8px; height: 8px; background: #d97706; border-radius: 50%; display: inline-block;"></span>
                <span>🚧 TÍNH NĂNG ĐANG ĐƯỢC PHÁT TRIỂN</span>
            </div>

            <!-- TITLE & DESCRIPTIONS -->
            <h1 class="uc-title" style="font-size: 1.85rem; font-weight: 800; color: #1e293b; margin-bottom: 1rem; line-height: 1.3;">
                @if(!empty($featureName) && $featureName !== 'Chức năng')
                    <span class="text-gold" style="color: #d97706;">{{ $featureName }}</span><br>
                @endif
                Tính năng đang trong quá trình xây dựng
            </h1>

            <p class="uc-description" style="color: #64748b; font-size: 1rem; line-height: 1.6; max-width: 560px; margin: 0 auto 2rem;">
                {{ $featureDesc }}
            </p>

            <!-- ROADMAP PROGRESS INDICATOR -->
            <div class="uc-progress-block" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 2rem; max-width: 480px; margin-left: auto; margin-right: auto;">
                <div class="uc-progress-info" style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 8px;">
                    <span><i class="fa-solid fa-code-commit text-gold"></i> Trạng thái tiến độ:</span>
                    <span class="font-bold text-gold" style="font-weight: 700; color: #d97706;">Sắp ra mắt (Coming Soon)</span>
                </div>
                <div class="uc-progress-bar-wrap" style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                    <div class="uc-progress-bar-fill" style="width: 75%; height: 100%; background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%); border-radius: 4px;"></div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="uc-actions" style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="javascript:history.back()" class="btn btn-outline" style="padding: 0.65rem 1.5rem; border-radius: 10px; font-weight: 600;">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại trang trước
                </a>
                <a href="{{ $homeUrl }}" class="btn btn-gold" style="padding: 0.65rem 1.75rem; border-radius: 10px; font-weight: 700;">
                    <i class="{{ $homeIcon }}"></i> {{ $homeText }}
                </a>
            </div>

            <!-- SUPPORT FOOTNOTE -->
            <div class="uc-footnote" style="margin-top: 2rem; font-size: 0.85rem; color: #94a3b8;">
                <i class="fa-regular fa-circle-question"></i> Cần hỗ trợ khẩn cấp? Liên hệ hotline hỗ trợ kỹ thuật: <strong style="color: #64748b;">1900 6868</strong> (8:00 - 22:00)
            </div>
        </div>
    </div>
</div>
@endsection
