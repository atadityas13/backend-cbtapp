@extends('layouts.admin')

@section('title', Auth::user()->role === 'admin' ? 'Pengaturan Sistem' : 'Kredensial Akun')

@section('header_title', Auth::user()->role === 'admin' ? 'Konfigurasi CBT & Kelola Proktor' : 'Kredensial Akun Anda')

@section('content')
<style>
    .settings-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    .settings-single {
        max-width: 600px;
        margin: 0 auto;
    }

    @media (max-width: 991px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }
    }

    .form-section-title {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--primary);
        margin-bottom: 16px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 8px;
    }

    .checkbox-tile-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
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

    .checkbox-tile input {
        accent-color: var(--primary);
        width: 20px;
        height: 20px;
    }

    .checkbox-tile-label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .locked-glass {
        position: relative;
        background-color: rgba(15, 22, 36, 0.5);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 32px;
        text-align: center;
        overflow: hidden;
        backdrop-filter: blur(4px);
    }

    .locked-icon {
        width: 64px;
        height: 64px;
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 16px;
        box-shadow: 0 0 20px rgba(239, 68, 68, 0.1);
    }

    .locked-title {
        font-size: 16px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 8px;
    }

    .locked-desc {
        font-size: 13px;
        color: var(--text-secondary);
        max-width: 280px;
        margin: 0 auto;
        line-height: 1.5;
    }
</style>

<div class="{{ Auth::user()->role === 'admin' ? 'settings-grid' : 'settings-single' }}">
    
    @can('manage-proctors')
    <!-- Left column: General Config Form -->
    <div class="card card-primary">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- SECTION 1: Operational Period -->
            <div class="form-section-title">1. Periode Tanggal Ujian Aktif</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="operational_start_date" class="form-label">Tanggal & Jam Mulai Operasional</label>
                    <input type="text" name="operational_start_date" id="operational_start_date" class="form-control" value="{{ $settings['operational_start_date'] }}" placeholder="YYYY-MM-DD HH:MM:SS" required>
                </div>
                <div class="form-group">
                    <label for="operational_end_date" class="form-label">Tanggal & Jam Selesai Operasional</label>
                    <input type="text" name="operational_end_date" id="operational_end_date" class="form-control" value="{{ $settings['operational_end_date'] }}" placeholder="YYYY-MM-DD HH:MM:SS" required>
                </div>
            </div>

            <!-- SECTION 2: Daily Hours -->
            <div class="form-section-title" style="margin-top: 12px;">2. Sesi Waktu Ujian Harian (WIB)</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Sesi Mulai (Jam : Menit)</label>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <input type="number" name="daily_start_hour" class="form-control" value="{{ $settings['daily_start_hour'] }}" min="0" max="23" required>
                        <span>:</span>
                        <input type="number" name="daily_start_minute" class="form-control" value="{{ $settings['daily_start_minute'] }}" min="0" max="59" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Sesi Berakhir (Jam : Menit)</label>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <input type="number" name="daily_end_hour" class="form-control" value="{{ $settings['daily_end_hour'] }}" min="0" max="23" required>
                        <span>:</span>
                        <input type="number" name="daily_end_minute" class="form-control" value="{{ $settings['daily_end_minute'] }}" min="0" max="59" required>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Active Exams -->
            <div class="form-section-title" style="margin-top: 12px;">3. Aktifkan Jenis Ujian</div>
            <div class="checkbox-tile-group">
                <label class="checkbox-tile">
                    <input type="checkbox" name="asesmen_sumatif_active" value="1" {{ $settings['asesmen_sumatif_active'] ? 'checked' : '' }}>
                    <span class="checkbox-tile-label">Asesmen Sumatif</span>
                </label>
                <label class="checkbox-tile">
                    <input type="checkbox" name="asesmen_madrasah_active" value="1" {{ $settings['asesmen_madrasah_active'] ? 'checked' : '' }}>
                    <span class="checkbox-tile-label">Asesmen Madrasah</span>
                </label>
            </div>

            <!-- SECTION 4: Advertisement settings -->
            <div class="form-section-title" style="margin-top: 12px;">4. Pengaturan Banner Iklan / Pengumuman</div>
            <div class="form-group">
                <label class="checkbox-tile" style="margin-bottom: 20px;">
                    <input type="checkbox" name="ad_show" value="1" {{ $settings['ad_show'] ? 'checked' : '' }}>
                    <span class="checkbox-tile-label">Tampilkan Banner Iklan di Aplikasi Siswa</span>
                </label>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="ad_image" class="form-label">URL Gambar Banner</label>
                    <input type="text" name="ad_image" id="ad_image" class="form-control" value="{{ $settings['ad_image'] }}" placeholder="https://example.com/banner.jpg">
                </div>
                <div class="form-group">
                    <label for="ad_image_file" class="form-label">Atau Unggah Gambar Baru</label>
                    <input type="file" name="ad_image_file" id="ad_image_file" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="ad_author" class="form-label">Pembuat Banner / Penanggung Jawab</label>
                    <input type="text" name="ad_author" id="ad_author" class="form-control" value="{{ $settings['ad_author'] }}" placeholder="Nama Pembuat / Guru">
                </div>
                <div class="form-group">
                    <label for="ad_time" class="form-label">Durasi Tampilan Wajib (Detik)</label>
                    <input type="number" name="ad_time" id="ad_time" class="form-control" value="{{ $settings['ad_time'] }}" min="1" max="60" required>
                </div>
            </div>

            <div style="margin-top: 32px;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="bi bi-save2-fill"></i> Simpan Semua Setelan Sistem
                </button>
            </div>

        </form>
    </div>
    @endcan

    <!-- Right column: Proctor Accounts CRUD -->
    <div>
        <!-- Card 1: Ubah Kredensial Akun Anda (Accessible to All Roles) -->
        <div class="card card-primary" style="margin-bottom: 24px;">
            <h2 class="card-title" style="margin-bottom: 20px;">
                <i class="bi bi-shield-lock-fill"></i> Kredensial Akun Anda
            </h2>
            
            <form action="{{ route('admin.profile.update') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="profile_name" class="form-label">Nama Lengkap Anda</label>
                    <input type="text" name="name" id="profile_name" class="form-control" value="{{ auth()->user()->name }}" required>
                </div>

                <div class="form-group">
                    <label for="profile_username" class="form-label">Username Anda</label>
                    <input type="text" name="username" id="profile_username" class="form-control" value="{{ auth()->user()->username }}" required>
                </div>

                <div class="form-group">
                    <label for="profile_password" class="form-label">Password Baru (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" id="profile_password" class="form-control" placeholder="Minimal 6 karakter" autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label for="profile_password_confirmation" class="form-label">Ulangi Password Baru</label>
                    <input type="password" name="password_confirmation" id="profile_password_confirmation" class="form-control" placeholder="Ulangi password baru">
                </div>

                <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; margin-top: 10px;">
                    <i class="bi bi-check-circle-fill"></i> Simpan Perubahan Akun
                </button>
            </form>
        </div>

        @can('manage-proctors')
            <!-- Card content visible ONLY to Super Admins -->
            <div class="card card-primary" style="margin-bottom: 24px;">
                <h2 class="card-title" style="margin-bottom: 20px;">
                    <i class="bi bi-person-plus-fill"></i> Tambah Akun Proktor
                </h2>
                
                <form action="{{ route('admin.proctors.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Nama Lengkap Proktor</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: Anzas Tio Aditya, S.Kom." required>
                    </div>

                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-control" placeholder="username_proktor" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Ulangi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; margin-top: 10px;">
                        Buat Akun Proktor
                    </button>
                </form>
            </div>

            <!-- List of existing proctors -->
            <div class="card card-primary">
                <h2 class="card-title" style="margin-bottom: 20px;">
                    <i class="bi bi-people-fill"></i> Daftar Akun Proktor
                </h2>
                
                <ul class="info-list">
                    @forelse($proctors as $proctor)
                        <li class="info-item" style="padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid var(--border-color);">
                            <div style="min-width: 0; flex-grow: 1;">
                                <div style="font-weight: 700; color: #fff; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $proctor->name }}">
                                    {{ $proctor->name }}
                                </div>
                                <div style="font-size: 12px; color: var(--text-secondary); display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                    <span>@</span><span>{{ $proctor->username }}</span>
                                </div>
                            </div>
                            <div>
                                <form action="{{ route('admin.proctors.destroy', $proctor->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun Proktor {{ $proctor->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm" style="padding: 6px 10px; border-color: rgba(239, 68, 68, 0.2);">
                                        <i class="bi bi-trash3-fill" style="color: var(--danger);"></i>
                                    </button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="no-data" style="padding: 20px; font-size: 13px;">
                            Belum ada akun proktor lain yang dibuat.
                        </li>
                    @endforelse
                </ul>
            </div>

        @else
            <!-- Display locked glass message for standard proctors -->
            <div class="locked-glass">
                <div class="locked-icon">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h3 class="locked-title">Kelola Proktor Terkunci</h3>
                <p class="locked-desc">
                    Halaman pembuatan dan penghapusan akun Proktor hanya dapat diakses oleh Administrator Utama (Super Admin).
                </p>
            </div>
        @endcan
    </div>

</div>

@endsection
