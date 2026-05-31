@extends('layouts.admin')

@section('title', 'Dashboard')

@section('header_title', 'Ringkasan Dashboard')

@section('content')
<style>
    .grid-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, transparent 70%);
        bottom: -30px;
        right: -30px;
        pointer-events: none;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        border-color: rgba(16, 185, 129, 0.3);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .stat-icon.devices {
        background-color: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .stat-icon.active-bans {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .stat-icon.violations {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--warning);
    }

    .stat-icon.assessment {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--primary);
    }

    .stat-icon.help-requests {
        background-color: rgba(139, 92, 246, 0.1);
        color: #a78bfa;
    }

    .stat-info {
        flex-grow: 1;
    }

    .stat-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .dashboard-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    @media (max-width: 991px) {
        .dashboard-split {
            grid-template-columns: 1fr;
        }
    }

    .info-list {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border-color);
    }

    .info-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-label {
        font-size: 14px;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-value {
        font-size: 14px;
        font-weight: 600;
    }

    .feed-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .feed-item {
        display: flex;
        gap: 16px;
        padding: 16px;
        background-color: rgba(7, 11, 19, 0.4);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        transition: var(--transition);
    }

    .feed-item:hover {
        border-color: rgba(239, 68, 68, 0.2);
        background-color: rgba(7, 11, 19, 0.7);
    }

    .feed-badge {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 18px;
    }

    .feed-badge.unbanned {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--primary);
    }

    .feed-content {
        flex-grow: 1;
        min-width: 0;
    }

    .feed-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 4px;
    }

    .feed-title {
        font-size: 14px;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .feed-time {
        font-size: 11px;
        color: var(--text-muted);
    }

    .feed-text {
        font-size: 13px;
        color: var(--text-secondary);
        margin-bottom: 6px;
        line-height: 1.4;
    }

    .feed-meta {
        font-size: 11px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .no-data {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-muted);
        font-size: 14px;
    }
</style>

<!-- Stats Grid -->
<div class="grid-stats">
    <div class="stat-card">
        <div class="stat-icon devices">
            <i class="bi bi-laptop"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Perangkat Terdaftar</div>
            <div class="stat-value">{{ number_format($totalDevices) }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon active-bans">
            <i class="bi bi-shield-slash-fill"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Siswa Ter-Ban Aktif</div>
            <div class="stat-value" style="color: var(--danger)">{{ number_format($activeBans) }}</div>
        </div>
    </div>

    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='{{ route('admin.help.index') }}'" title="Klik untuk membuka Pusat Bantuan Siswa">
        <div class="stat-icon help-requests">
            <i class="bi bi-headset"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Siswa Butuh Bantuan</div>
            <div class="stat-value" style="color: #a78bfa;">{{ number_format($pendingHelps) }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon violations">
            <i class="bi bi-exclamation-octagon-fill"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Pelanggaran</div>
            <div class="stat-value" style="color: var(--warning)">{{ number_format($totalViolations) }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon assessment">
            <i class="bi bi-clipboard2-check-fill"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Jenis Asesmen Aktif</div>
            <div class="stat-value" style="font-size: 16px; color: var(--primary); font-weight: 700; margin-top: 6px;">{{ $activeAssessment }}</div>
        </div>
    </div>
</div>

<!-- Main Split Layout -->
<div class="dashboard-split">
    
    <!-- Left Pane: System Parameter Statuses -->
    <div class="card card-primary">
        <div class="card-header-flex">
            <h2 class="card-title">
                <i class="bi bi-info-circle-fill"></i>
                Status Parameter Sistem
            </h2>
            <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-gear-fill"></i> Pengaturan
            </a>
        </div>
        
        <ul class="info-list">
            <li class="info-item">
                <div class="info-label">
                    <i class="bi bi-calendar3"></i> Rentang Tanggal Operasional
                </div>
                <div class="info-value">
                    {{ substr(\App\Models\Setting::getValue('operational_start_date', '2026-05-25'), 0, 10) }} s/d 
                    {{ substr(\App\Models\Setting::getValue('operational_end_date', '2026-06-05'), 0, 10) }}
                </div>
            </li>
            <li class="info-item">
                <div class="info-label">
                    <i class="bi bi-clock-fill"></i> Jam Ujian Harian
                </div>
                <div class="info-value">
                    {{ sprintf("%02d", \App\Models\Setting::getValue('daily_start_hour', 7)) }}:{{ sprintf("%02d", \App\Models\Setting::getValue('daily_start_minute', 25)) }} s/d 
                    {{ sprintf("%02d", \App\Models\Setting::getValue('daily_end_hour', 12)) }}:{{ sprintf("%02d", \App\Models\Setting::getValue('daily_end_minute', 30)) }} WIB
                </div>
            </li>
            <li class="info-item">
                <div class="info-label">
                    <i class="bi bi-shield-check"></i> Asesmen Sumatif
                </div>
                <div class="info-value">
                    @if(\App\Models\Setting::getValue('asesmen_sumatif_active', true))
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-danger">Nonaktif</span>
                    @endif
                </div>
            </li>
            <li class="info-item">
                <div class="info-label">
                    <i class="bi bi-shield-lock"></i> Asesmen Madrasah
                </div>
                <div class="info-value">
                    @if(\App\Models\Setting::getValue('asesmen_madrasah_active', false))
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-danger">Nonaktif</span>
                    @endif
                </div>
            </li>
            <li class="info-item">
                <div class="info-label">
                    <i class="bi bi-megaphone-fill"></i> Banner Iklan / Pengumuman
                </div>
                <div class="info-value">
                    @if(\App\Models\Setting::getValue('ad_show', true))
                        <span class="badge badge-success">Ditampilkan ({{ \App\Models\Setting::getValue('ad_time', 3) }} Detik)</span>
                    @else
                        <span class="badge badge-danger">Disembunyikan</span>
                    @endif
                </div>
            </li>
        </ul>
    </div>

    <!-- Right Pane: Real-Time Violation Log Feed -->
    <div class="card card-danger">
        <div class="card-header-flex">
            <h2 class="card-title">
                <i class="bi bi-activity"></i>
                Feed Pelanggaran Siswa Terbaru
            </h2>
            <a href="{{ route('admin.violations.index') }}" class="btn btn-secondary btn-sm">
                Lihat Semua
            </a>
        </div>

        <div class="feed-list">
            @forelse($recentViolations as $violation)
                <div class="feed-item">
                    <div class="feed-badge {{ $violation->status === 'UNBANNED' ? 'unbanned' : '' }}">
                        @if($violation->status === 'UNBANNED')
                            <i class="bi bi-shield-check"></i>
                        @else
                            <i class="bi bi-shield-slash-fill"></i>
                        @endif
                    </div>
                    <div class="feed-content">
                        <div class="feed-header">
                            <span class="feed-title" title="{{ $violation->student_name }}">{{ $violation->student_name }}</span>
                            <span class="feed-time">{{ $violation->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="feed-text">
                            Keluar dari aplikasi: <strong>{{ $violation->reason }}</strong>
                        </p>
                        <div class="feed-meta">
                            <span><i class="bi bi-phone"></i> {{ $violation->device_model }}</span>
                            <span>&bull;</span>
                            <span>Status: 
                                @if($violation->status === 'BANNED')
                                    <span style="color: var(--danger); font-weight: 700;">TERKUNCI</span>
                                @else
                                    <span style="color: var(--primary); font-weight: 700;">DIPULIHKAN</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="no-data">
                    <i class="bi bi-emoji-smile" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                    Tidak ada log pelanggaran terdeteksi hari ini. Semua siswa aman!
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
