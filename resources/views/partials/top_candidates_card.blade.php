@php
    $topCandidates = $topCandidates ?? [];
    $cardTitle = $cardTitle ?? 'Top 5 Ứng Viên Xuất Sắc Nhất';
    $tbodyId = $tbodyId ?? 'tbodyTopCandidates';
@endphp

<div class="stats-table-card" style="border: 1px solid #fde68a; box-shadow: 0 4px 18px rgba(217, 119, 6, 0.08);">
    <div class="table-card-head" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-bottom: 1px solid #fde68a;">
        <h3 class="chart-title" style="color: #78350f;">
            <i class="fa-solid fa-trophy" style="color: #d97706;"></i> {{ $cardTitle }}
        </h3>
        <span style="font-size: 11.5px; font-weight: 700; color: #92400e; background: rgba(255, 255, 255, 0.85); border: 1px solid #fcd34d; padding: 4px 12px; border-radius: 999px;">
            Thang 100: ĐG 40% + Giờ 40% (160h) + Đúng giờ 20% • ≥ 2 ca • Không vi phạm 30 ngày
        </span>
    </div>
    <div style="overflow-x: auto;">
        <table class="stats-table">
            <thead>
                <tr>
                    <th style="text-align: center; width: 115px;">Hạng</th>
                    <th>Ứng Viên</th>
                    <th style="text-align: center;">Tổng Số Ca &amp; Giờ Làm</th>
                    <th style="text-align: center;">Điểm Đánh Giá</th>
                    <th style="text-align: center;">Tỷ Lệ Đúng Giờ</th>
                    <th style="text-align: right; min-width: 175px;">Điểm Xuất Sắc</th>
                </tr>
            </thead>
            <tbody id="{{ $tbodyId }}">
                @forelse ($topCandidates as $idx => $uv)
                    @php
                        $rank = $idx + 1;
                        $rankBadges = [
                            1 => ['label' => '🥇 Hạng 1', 'bg' => '#fef3c7', 'color' => '#b45309', 'border' => '#f59e0b'],
                            2 => ['label' => '🥈 Hạng 2', 'bg' => '#f1f5f9', 'color' => '#334155', 'border' => '#94a3b8'],
                            3 => ['label' => '🥉 Hạng 3', 'bg' => '#ffedd5', 'color' => '#c2410c', 'border' => '#fb923c'],
                        ];
                        $badgeCfg = $rankBadges[$rank] ?? null;

                        $avatarUrl = $uv['avatar_url'] ?? null;
                        $initial = mb_strtoupper(mb_substr(trim((string) ($uv['ho_ten'] ?? 'U')), 0, 1));
                        $onTime = (float) ($uv['ty_le_dung_gio'] ?? 0);
                        if ($onTime >= 90) {
                            $onTimeBg = '#dcfce7';
                            $onTimeColor = '#15803d';
                            $onTimeBorder = '#86efac';
                        } elseif ($onTime >= 75) {
                            $onTimeBg = '#fef3c7';
                            $onTimeColor = '#b45309';
                            $onTimeBorder = '#fde68a';
                        } else {
                            $onTimeBg = '#fee2e2';
                            $onTimeColor = '#b91c1c';
                            $onTimeBorder = '#fca5a5';
                        }
                        $luotDg = (int) ($uv['so_luot_danh_gia'] ?? 0);
                        $diemTb = (float) ($uv['diem_danh_gia_tb'] ?? 0);
                        $scoreTotal = (float) ($uv['diem_xuat_sac'] ?? 0);
                    @endphp
                    <tr style="{{ $rank === 1 ? 'background: rgba(255, 251, 235, 0.55);' : '' }}">
                        <td style="text-align: center; white-space: nowrap;">
                            @if ($badgeCfg)
                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 800; background: {{ $badgeCfg['bg'] }}; color: {{ $badgeCfg['color'] }}; border: 1px solid {{ $badgeCfg['border'] }};">
                                    {{ $badgeCfg['label'] }}
                                </span>
                            @else
                                <span style="width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12.5px; font-weight: 800; background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0;">
                                    {{ $rank }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 11px;">
                                @if ($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="{{ $uv['ho_ten'] }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #fde68a; flex-shrink: 0;">
                                @else
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: {{ $rank === 1 ? 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)' : '#fef3c7' }}; color: {{ $rank === 1 ? '#ffffff' : '#b45309' }}; border: 1.5px solid {{ $rank === 1 ? '#d97706' : '#fcd34d' }}; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; flex-shrink: 0;">
                                        {{ $initial }}
                                    </div>
                                @endif
                                <div style="min-width: 0;">
                                    <div style="font-weight: 800; color: #0f172a; font-size: 13.5px;">{{ $uv['ho_ten'] }}</div>
                                    <div style="font-size: 12px; color: #64748b; word-break: break-all;">{{ $uv['email'] ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="text-align: center; white-space: nowrap;">
                            <span style="font-weight: 800; color: #0284c7; background: #e0f2fe; border: 1px solid #bae6fd; padding: 4px 10px; border-radius: 999px; font-size: 12.5px;">
                                {{ $uv['so_ca'] }} ca ({{ number_format($uv['tong_gio_lam'], 1, ',', '.') }} giờ)
                            </span>
                        </td>
                        <td style="text-align: center; white-space: nowrap;">
                            @if ($luotDg > 0)
                                <span style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; padding: 4px 10px; border-radius: 999px; font-weight: 800; font-size: 12.5px;">
                                    {{ number_format($diemTb, 1) }} ⭐
                                </span>
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">{{ $luotDg }} lượt</div>
                            @else
                                <span style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; padding: 4px 10px; border-radius: 999px; font-weight: 700; font-size: 12px;">
                                    0.0 ⭐ (Chưa có)
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center; white-space: nowrap;">
                            <span style="background: {{ $onTimeBg }}; color: {{ $onTimeColor }}; border: 1px solid {{ $onTimeBorder }}; padding: 4px 10px; border-radius: 999px; font-weight: 800; font-size: 12.5px;">
                                {{ number_format($onTime, 1) }}%
                            </span>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                {{ $uv['so_ca_dung_gio'] ?? 0 }}/{{ $uv['so_ca_tham_gia'] ?? $uv['so_ca'] }} ca
                            </div>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            @if ($rank === 1)
                                <span style="display: inline-block; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; padding: 5px 12px; border-radius: 999px; font-weight: 900; font-size: 13px; box-shadow: 0 2px 8px rgba(217, 119, 6, 0.28);">
                                    {{ number_format($scoreTotal, 1) }} / 100 điểm
                                </span>
                            @else
                                <span style="display: inline-block; background: #fffbeb; color: #b45309; border: 1.5px solid #f59e0b; padding: 4px 11px; border-radius: 999px; font-weight: 800; font-size: 12.5px;">
                                    {{ number_format($scoreTotal, 1) }} / 100 điểm
                                </span>
                            @endif
                            <div style="font-size: 10.5px; color: #94a3b8; margin-top: 3px;">
                                ĐG {{ number_format($uv['diem_danh_gia'] ?? 0, 1) }} + Giờ {{ number_format($uv['diem_gio_lam'] ?? 0, 1) }} + CC {{ number_format($uv['diem_chuyen_can'] ?? 0, 1) }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 28px; color: #94a3b8;">
                            Chưa có ứng viên đạt điều kiện xếp hạng (≥ 2 ca hoàn thành và không bị xử lý vi phạm trong 30 ngày qua).
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@once
<script>
window.gsRenderTopCandidatesTable = function(tbodyEl, list) {
    if (!tbodyEl) return;
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const fmtDec = (n, d = 1) => Number(n || 0).toLocaleString('vi-VN', { minimumFractionDigits: d, maximumFractionDigits: d });

    if (!Array.isArray(list) || !list.length) {
        tbodyEl.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:28px;color:#94a3b8;">Chưa có ứng viên đạt điều kiện xếp hạng (≥ 2 ca hoàn thành và không bị xử lý vi phạm trong 30 ngày qua).</td></tr>';
        return;
    }

    const medals = {
        1: { label: '🥇 Hạng 1', bg: '#fef3c7', color: '#b45309', border: '#f59e0b' },
        2: { label: '🥈 Hạng 2', bg: '#f1f5f9', color: '#334155', border: '#94a3b8' },
        3: { label: '🥉 Hạng 3', bg: '#ffedd5', color: '#c2410c', border: '#fb923c' }
    };

    tbodyEl.innerHTML = list.map((uv, idx) => {
        const rank = idx + 1;
        const m = medals[rank];
        const rankHtml = m
            ? `<span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:800;background:${m.bg};color:${m.color};border:1px solid ${m.border};">${m.label}</span>`
            : `<span style="width:28px;height:28px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:12.5px;font-weight:800;background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;">${rank}</span>`;

        const nameStr = String(uv.ho_ten || 'U').trim();
        const initial = esc(nameStr.charAt(0).toUpperCase() || 'U');
        const avatarHtml = uv.avatar_url
            ? `<img src="${esc(uv.avatar_url)}" alt="${esc(uv.ho_ten)}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #fde68a;flex-shrink:0;">`
            : `<div style="width:40px;height:40px;border-radius:50%;background:${rank === 1 ? 'linear-gradient(135deg,#f59e0b 0%,#d97706 100%)' : '#fef3c7'};color:${rank === 1 ? '#fff' : '#b45309'};border:1.5px solid ${rank === 1 ? '#d97706' : '#fcd34d'};display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;flex-shrink:0;">${initial}</div>`;

        const luotDg = Number(uv.so_luot_danh_gia || 0);
        const ratingHtml = luotDg > 0
            ? `<span style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;padding:4px 10px;border-radius:999px;font-weight:800;font-size:12.5px;">${Number(uv.diem_danh_gia_tb || 0).toFixed(1)} ⭐</span><div style="font-size:11px;color:#94a3b8;margin-top:2px;">${luotDg} lượt</div>`
            : `<span style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;padding:4px 10px;border-radius:999px;font-weight:700;font-size:12px;">0.0 ⭐ (Chưa có)</span>`;

        const onTime = Number(uv.ty_le_dung_gio || 0);
        const otBg = onTime >= 90 ? '#dcfce7' : (onTime >= 75 ? '#fef3c7' : '#fee2e2');
        const otColor = onTime >= 90 ? '#15803d' : (onTime >= 75 ? '#b45309' : '#b91c1c');
        const otBorder = onTime >= 90 ? '#86efac' : (onTime >= 75 ? '#fde68a' : '#fca5a5');

        const totalScore = Number(uv.diem_xuat_sac || 0).toFixed(1);
        const scoreBadgeHtml = rank === 1
            ? `<span style="display:inline-block;background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);color:#fff;padding:5px 12px;border-radius:999px;font-weight:900;font-size:13px;box-shadow:0 2px 8px rgba(217,119,6,0.28);">${totalScore} / 100 điểm</span>`
            : `<span style="display:inline-block;background:#fffbeb;color:#b45309;border:1.5px solid #f59e0b;padding:4px 11px;border-radius:999px;font-weight:800;font-size:12.5px;">${totalScore} / 100 điểm</span>`;

        return `
            <tr style="${rank === 1 ? 'background:rgba(255,251,235,0.55);' : ''}">
                <td style="text-align:center;white-space:nowrap;">${rankHtml}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:11px;">
                        ${avatarHtml}
                        <div style="min-width:0;">
                            <div style="font-weight:800;color:#0f172a;font-size:13.5px;">${esc(uv.ho_ten)}</div>
                            <div style="font-size:12px;color:#64748b;word-break:break-all;">${esc(uv.email || '')}</div>
                        </div>
                    </div>
                </td>
                <td style="text-align:center;white-space:nowrap;">
                    <span style="font-weight:800;color:#0284c7;background:#e0f2fe;border:1px solid #bae6fd;padding:4px 10px;border-radius:999px;font-size:12.5px;">
                        ${uv.so_ca} ca (${fmtDec(uv.tong_gio_lam, 1)} giờ)
                    </span>
                </td>
                <td style="text-align:center;white-space:nowrap;">${ratingHtml}</td>
                <td style="text-align:center;white-space:nowrap;">
                    <span style="background:${otBg};color:${otColor};border:1px solid ${otBorder};padding:4px 10px;border-radius:999px;font-weight:800;font-size:12.5px;">
                        ${onTime.toFixed(1)}%
                    </span>
                    <div style="font-size:11px;color:#64748b;margin-top:2px;">${uv.so_ca_dung_gio ?? 0}/${uv.so_ca_tham_gia ?? uv.so_ca} ca</div>
                </td>
                <td style="text-align:right;white-space:nowrap;">
                    ${scoreBadgeHtml}
                    <div style="font-size:10.5px;color:#94a3b8;margin-top:3px;">
                        ĐG ${Number(uv.diem_danh_gia || 0).toFixed(1)} + Giờ ${Number(uv.diem_gio_lam || 0).toFixed(1)} + CC ${Number(uv.diem_chuyen_can || 0).toFixed(1)}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
};
</script>
@endonce
