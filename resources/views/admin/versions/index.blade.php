@extends('layouts.admin')

@section('title', 'Manajemen Versi Aplikasi')

@section('header_title', 'Manajemen Pembaruan Versi Aplikasi')

@section('content')
<style>
    .versions-grid {
        display: grid;
        grid-template-columns: 3.2fr 2fr;
        gap: 24px;
    }

    @media (max-width: 991px) {
        .versions-grid {
            grid-template-columns: 1fr;
        }
    }

    .form-section-title {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--primary);
        margin-bottom: 20px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 8px;
    }

    .checkbox-tile {
        position: relative;
        background-color: rgba(7, 11, 19, 0.4);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        transition: var(--transition);
    }

    .checkbox-tile:hover {
        border-color: rgba(16, 185, 129, 0.2);
        background-color: rgba(7, 11, 19, 0.7);
    }

    .checkbox-tile.danger-state:hover {
        border-color: rgba(239, 68, 68, 0.2);
    }

    .checkbox-tile input {
        accent-color: var(--primary);
        width: 20px;
        height: 20px;
    }

    .checkbox-tile.danger-state input {
        accent-color: var(--danger);
    }

    .checkbox-tile-label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
    }

    /* Premium Status Banner */
    .status-banner {
        border-radius: var(--radius-lg);
        padding: 24px;
        background: rgba(15, 22, 36, 0.4);
        border: 1px solid var(--border-color);
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
        backdrop-filter: blur(8px);
    }

    .status-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
    }

    .status-banner.status-danger::before {
        background: var(--danger);
        box-shadow: 0 0 15px var(--danger);
    }

    .status-banner.status-success::before {
        background: var(--primary);
        box-shadow: 0 0 15px var(--primary);
    }

    .status-glow-badge {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-block;
        margin-bottom: 12px;
    }

    .status-danger .status-glow-badge {
        background: rgba(239, 68, 68, 0.15);
        color: #ff6b6b;
        border: 1px solid rgba(239, 68, 68, 0.3);
        box-shadow: 0 0 10px rgba(239, 68, 68, 0.1);
    }

    .status-success .status-glow-badge {
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.3);
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.1);
    }

    .status-title {
        font-size: 16px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 6px;
    }

    .status-desc {
        font-size: 13px;
        color: var(--text-secondary);
        line-height: 1.5;
    }

    /* Code Snippet styling */
    .doc-card {
        background: rgba(15, 22, 36, 0.5);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 24px;
        backdrop-filter: blur(8px);
    }

    .code-container {
        background: rgba(7, 11, 19, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: var(--radius-md);
        padding: 14px;
        font-family: 'Courier New', Courier, monospace;
        font-size: 12px;
        color: #e2e8f0;
        overflow-x: auto;
        margin-top: 12px;
        line-height: 1.5;
    }

    .code-comment { color: #64748b; }
    .code-keyword { color: #f43f5e; }
    .code-string { color: #34d399; }
    .code-value { color: #fbbf24; }
</style>

<div class="versions-grid">
    
    <!-- Left Column: Version Management Form -->
    <div class="card card-primary">
        <h2 class="card-title" style="margin-bottom: 24px;">
            <i class="bi bi-cloud-arrow-up-fill" style="color: var(--primary);"></i> Pembaruan Aplikasi Siswa
        </h2>

        <form action="{{ route('admin.versions.update') }}" method="POST">
            @csrf

            <div class="form-section-title">Konfigurasi Rilis</div>

            <div class="form-row">
                <div class="form-group">
                    <label for="latest_version" class="form-label">Nama Versi (versionName)</label>
                    <input type="text" name="latest_version" id="latest_version" class="form-control" value="{{ $settings['latest_version'] }}" placeholder="Contoh: 4.2.3" required>
                    <small style="color: var(--text-secondary); margin-top: 4px; display: block;">Nama versi tampilan luar yang dipajang di Google Play Store.</small>
                </div>
                <div class="form-group">
                    <label for="latest_version_code" class="form-label">Kode Versi (versionCode)</label>
                    <input type="number" name="latest_version_code" id="latest_version_code" class="form-control" value="{{ $settings['latest_version_code'] }}" placeholder="Contoh: 6" required>
                    <small style="color: var(--text-secondary); margin-top: 4px; display: block;">Angka bulat positif pembanding internal (meningkat di setiap rilis).</small>
                </div>
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label for="download_url" class="form-label">URL Unduhan Aplikasi (Play Store / Direct Link)</label>
                <input type="url" name="download_url" id="download_url" class="form-control" value="{{ $settings['download_url'] }}" placeholder="https://play.google.com/store/apps/details?id=com.mtsn11.cbtapp" required>
                <small style="color: var(--text-secondary); margin-top: 4px; display: block;">Tautan tujuan siswa ketika mengklik tombol update pada dialog aplikasi.</small>
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label for="release_notes" class="form-label">Catatan Rilis (Release Notes)</label>
                <textarea name="release_notes" id="release_notes" class="form-control" rows="5" style="font-family: monospace; font-size: 13px;" placeholder="• Peningkatan keamanan&#10;• Peningkatan perlindungan Proktor&#10;• Sistem IAP Poin Disiplin baru" required>{{ $settings['release_notes'] }}</textarea>
            </div>

            <div class="form-section-title" style="margin-top: 24px;">Aturan Pembaruan</div>

            <div class="form-group" style="margin-top: 8px; margin-bottom: 24px;">
                <label class="checkbox-tile danger-state">
                    <input type="checkbox" name="force_update" value="1" {{ $settings['force_update'] ? 'checked' : '' }}>
                    <span class="checkbox-tile-label" style="color: #ff8b8b;">Wajibkan Pembaruan Aplikasi (Force Update)</span>
                </label>
                <small style="color: var(--text-secondary); margin-top: 6px; display: block; line-height: 1.4;">
                    Jika dicentang, siswa **tidak akan bisa menutup dialog pembaruan** atau masuk ke dalam ujian sebelum mereka selesai memperbarui aplikasi ke versi terbaru.
                </small>
            </div>

            <div style="margin-top: 32px;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="bi bi-save2-fill"></i> Simpan Setelan Rilis Versi
                </button>
            </div>

        </form>
    </div>

    <!-- Right Column: Active Status & Integration Docs -->
    <div>
        
        <!-- Active Status Card -->
        @if($settings['force_update'])
            <div class="status-banner status-danger">
                <div class="status-glow-badge">Force Update Aktif</div>
                <h3 class="status-title">Wajib Pembaruan Diberlakukan</h3>
                <p class="status-desc">
                    Seluruh siswa yang memiliki kode versi aplikasi lebih rendah dari <strong>{{ $settings['latest_version_code'] }}</strong> (Versi {{ $settings['latest_version'] }}) akan dipaksa melakukan pembaruan secara ketat saat membuka aplikasi.
                </p>
            </div>
        @else
            <div class="status-banner status-success">
                <div class="status-glow-badge">Pembaruan Opsional</div>
                <h3 class="status-title">Pembaruan Fleksibel</h3>
                <p class="status-desc">
                    Pembaruan ditawarkan kepada siswa secara opsional. Mereka dapat memilih untuk mengabaikan dialog pembaruan sementara waktu dan tetap mengikuti ujian.
                </p>
            </div>
        @endif

    </div>

</div>
@endsection
