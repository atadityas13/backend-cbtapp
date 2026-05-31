@extends('layouts.admin')

@section('title', 'Notifikasi Push Terjadwal')

@section('header_title', 'Notifikasi Terjadwal & Rutin')

@section('content')
<style>
    .split-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-bottom: 30px;
    }

    @media (max-width: 991px) {
        .split-layout {
            grid-template-columns: 1fr;
        }
    }

    .filter-btn {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        padding: 10px 20px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .filter-btn:hover {
        color: var(--text-primary);
        background-color: var(--bg-surface-hover);
    }

    .filter-btn.active {
        background-color: var(--primary-glow);
        color: var(--primary);
        border-color: rgba(16, 185, 129, 0.3);
    }

    .tabs-container {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 16px;
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

    .target-box {
        background-color: rgba(7, 11, 19, 0.4);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 16px;
        margin-bottom: 20px;
    }

    .target-toggle {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
    }

    .toggle-option {
        flex: 1;
        background-color: var(--bg-base);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        padding: 10px;
        border-radius: var(--radius-sm);
        text-align: center;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        transition: var(--transition);
    }

    .toggle-option.active {
        background-color: var(--primary-glow);
        color: var(--primary);
        border-color: rgba(16, 185, 129, 0.3);
    }

    .preview-card {
        background-color: #0f172a;
        border: 1px solid #1e293b;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.4);
    }

    .preview-header {
        background-color: rgba(255, 255, 255, 0.05);
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        color: #94a3b8;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .preview-body {
        padding: 16px;
    }

    .preview-app-title {
        font-weight: 700;
        font-size: 12px;
        color: #10b981;
        margin-bottom: 4px;
    }

    .preview-notif-title {
        font-weight: 700;
        font-size: 14px;
        color: #ffffff;
        margin-bottom: 4px;
    }

    .preview-notif-desc {
        font-size: 13px;
        color: #cbd5e1;
        line-height: 1.4;
    }

    .preview-img-container {
        margin-top: 12px;
        border-radius: var(--radius-sm);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.05);
        display: none;
    }

    .preview-img {
        width: 100%;
        max-height: 140px;
        object-fit: cover;
    }

    .day-checkboxes {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 10px;
        margin-top: 8px;
    }

    .day-item {
        background-color: var(--bg-base);
        border: 1px solid var(--border-color);
        padding: 8px 12px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        user-select: none;
        transition: var(--transition);
    }

    .day-item:hover {
        border-color: rgba(16, 185, 129, 0.3);
        background-color: rgba(16, 185, 129, 0.05);
    }

    .day-item input[type="checkbox"] {
        cursor: pointer;
        accent-color: var(--primary);
    }

    .btn-toggle-active {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
    }
</style>

<div class="tabs-container">
    <a href="{{ route('admin.notifications.index') }}" class="filter-btn">
        <i class="bi bi-send-fill"></i> Kirim Instan (Sekarang)
    </a>
    <a href="{{ route('admin.notifications.scheduled.index') }}" class="filter-btn active">
        <i class="bi bi-alarm-fill"></i> Notifikasi Terjadwal & Rutin
    </a>
</div>

<div class="split-layout">
    
    <!-- Left: Schedule Builder Form -->
    <div class="card card-primary">
        <form action="{{ route('admin.notifications.scheduled.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Section 1: Target Selection -->
            <div class="form-section-title">1. Target Penerima Notifikasi</div>
            <div class="target-box">
                <div class="target-toggle">
                    <div class="toggle-option active" id="toggleTopic" onclick="switchTarget('topic')">
                        <i class="bi bi-broadcast"></i> Kirim Massal (Topik)
                    </div>
                    <div class="toggle-option" id="toggleToken" onclick="switchTarget('token')">
                        <i class="bi bi-phone-fill"></i> Kirim Per-Siswa (Token)
                    </div>
                </div>

                <!-- Topic Selector -->
                <div class="form-group" id="groupTopic">
                    <label for="topik" class="form-label">Pilih Topik Terdaftar</label>
                    <select name="topik" id="topik" class="form-control">
                        @foreach($topics as $topic)
                            <option value="{{ $topic }}" {{ $topic === 'cbt_notif' ? 'selected' : '' }}>{{ $topic }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Token Selector -->
                <div class="form-group" id="groupToken" style="display: none;">
                    <label for="fcm_token" class="form-label">Pilih Perangkat Siswa</label>
                    <select name="fcm_token" id="fcm_token" class="form-control">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->fcm_token }}">{{ $student->full_name }} ({{ $student->android_id }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Section 2: Notification Content -->
            <div class="form-section-title">2. Isi Konten Notifikasi</div>
            
            <div class="form-group">
                <label for="judul" class="form-label">Judul Notifikasi (Gunakan `|` untuk merotasi judul secara berurutan)</label>
                <input type="text" name="judul" id="judul" class="form-control" placeholder="Contoh: Mulai Sesi 1 | Sesi Ujian Aktif | Ujian Telah Dibuka" required oninput="updatePreview()">
                <small style="color: var(--text-muted); display: block; margin-top: 4px;">
                    Pisahkan dengan tanda `|` agar pesan dirotasi bergiliran setiap harinya agar tidak monoton.
                </small>
            </div>

            <div class="form-group">
                <label for="deskripsi" class="form-label">Deskripsi / Isi Pesan (Gunakan `|` untuk merotasi isi secara berurutan)</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="4" placeholder="Contoh: Selamat menempuh ujian. | Harap tertib saat ujian. | Masuk ke aplikasi CBT." required oninput="updatePreview()"></textarea>
                <small style="color: var(--text-muted); display: block; margin-top: 4px;">
                    Samakan jumlah pilihan deskripsi dengan jumlah pilihan judul di atas.
                </small>
            </div>

            <!-- Section 3: Penjadwalan Rutin -->
            <div class="form-section-title">3. Atur Jadwal Waktu & Hari Kirim</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="schedule_time" class="form-label">Jam Pengiriman (WIB)</label>
                    <input type="time" name="schedule_time" id="schedule_time" class="form-control" value="07:30" required>
                </div>
                <div class="form-group" style="display: flex; align-items: flex-end; padding-bottom: 4px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="selectAllDays()">
                        <i class="bi bi-check-all"></i> Pilih Semua Hari
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Pilih Hari Pengiriman Rutin</label>
                <div class="day-checkboxes">
                    @php
                        $days = [
                            'Monday' => 'Senin',
                            'Tuesday' => 'Selasa',
                            'Wednesday' => 'Rabu',
                            'Thursday' => 'Kamis',
                            'Friday' => 'Jumat',
                            'Saturday' => 'Sabtu',
                            'Sunday' => 'Minggu'
                        ];
                    @endphp
                    @foreach($days as $eng => $ind)
                        <label class="day-item">
                            <input type="checkbox" name="days_of_week[]" value="{{ $eng }}" class="day-cb" checked>
                            <span>{{ $ind }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Section 4: Optional Media & Actions -->
            <div class="form-section-title">4. Lampiran Gambar, Audio & Link (Opsional)</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="gambar_url" class="form-label">Pilih Gambar (Hasil Upload)</label>
                    <select name="gambar_url" id="gambar_url" class="form-control" onchange="updatePreview()">
                        <option value="">-- Tanpa Gambar / Gunakan Upload Baru --</option>
                        @foreach($uploadedImageList as $img)
                            <option value="{{ asset('uploads/' . $img) }}">{{ $img }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="gambar_file" class="form-label">Atau Unggah Gambar Baru</label>
                    <input type="file" name="gambar_file" id="gambar_file" class="form-control" accept="image/*" onchange="previewLocalImage(this)">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Pilih Daftar Suara / Audio (Akan Diacak Secara Pintar Tanpa Duplikat Berurutan)</label>
                <div class="day-checkboxes" style="grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));">
                    <label class="day-item">
                        <input type="checkbox" name="custom_sound[]" value="default" class="sound-cb" checked>
                        <span>default</span>
                    </label>
                    <label class="day-item">
                        <input type="checkbox" name="custom_sound[]" value="mulai_ujian" class="sound-cb">
                        <span>Mulai Ujian</span>
                    </label>
                    <label class="day-item">
                        <input type="checkbox" name="custom_sound[]" value="belajar" class="sound-cb">
                        <span>Belajar</span>
                    </label>
                    @if(!empty($uploadedAudioList))
                        @foreach($uploadedAudioList as $audio)
                            <label class="day-item" title="{{ $audio }}">
                                <input type="checkbox" name="custom_sound[]" value="{{ asset('uploads/audio/' . $audio) }}" class="sound-cb">
                                <span>{{ Str::limit($audio, 12) }}</span>
                            </label>
                        @endforeach
                    @endif
                </div>
                <small style="color: var(--text-muted); display: block; margin-top: 6px;">
                    <i class="bi bi-info-circle"></i> Anda bisa mencentang beberapa audio kustom sekaligus untuk merotasi suara alarm secara pintar setiap harinya.
                </small>
            </div>

            <div class="form-group">
                <label for="audio_file" class="form-label">Atau Unggah File Suara Baru (.mp3) - Otomatis Ditambahkan ke Daftar Suara Aktif</label>
                <input type="file" name="audio_file" id="audio_file" class="form-control" accept="audio/mpeg">
            </div>

            <div class="form-group">
                <label for="link" class="form-label">URL Tindakan (Link Terbuka Ketika Diklik)</label>
                <input type="text" name="link" id="link" class="form-control" placeholder="https://mtsn11majalengka.sch.id">
            </div>

            <!-- Section 5: Advanced Parameters -->
            <div class="form-section-title">5. Parameter Pengiriman & Kategori</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="prioritas" class="form-label">Prioritas Pengiriman</label>
                    <select name="prioritas" id="prioritas" class="form-control">
                        <option value="high" selected>High (Bangunkan HP Segera)</option>
                        <option value="normal">Normal (Hemat Daya)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="category" class="form-label">Kategori Pesan</label>
                    <select name="category" id="category" class="form-control">
                        <option value="normal" selected>Normal (Notifikasi Standar)</option>
                        <option value="exam_alert">Exam Alert (Teks Berjalan)</option>
                    </select>
                </div>
            </div>

            <div class="form-group" id="durationField" style="display: none;">
                <label for="duration" class="form-label">Durasi Teks Berjalan (Detik)</label>
                <input type="number" name="duration" id="duration" class="form-control" value="30" min="5" max="300">
            </div>

            <div style="margin-top: 32px;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="bi bi-calendar-plus-fill"></i> Simpan Jadwal Notifikasi Rutin
                </button>
            </div>

        </form>
    </div>

    <!-- Right: Real-time Smartphone Push Preview -->
    <div>
        <div style="position: sticky; top: 94px;">
            <div class="form-section-title" style="margin-bottom: 20px;">Pratinjau Di Layar HP Siswa</div>
            
            <div class="preview-card">
                <div class="preview-header">
                    <img src="{{ asset('assets/cbt_logo.png') }}" alt="CBT Logo" style="width: 14px; height: 14px; object-fit: contain; vertical-align: middle;">
                    <span style="font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">CBTApp &bull; SEKARANG</span>
                </div>
                <div class="preview-body">
                    <div class="preview-app-title">CBTApp</div>
                    <div class="preview-notif-title" id="previewTitle">Judul Notifikasi Baru</div>
                    <div class="preview-notif-desc" id="previewDesc">Deskripsi ringkas notifikasi yang dikirimkan oleh proktor akan muncul di sini.</div>
                    
                    <div class="preview-img-container" id="previewImgContainer">
                        <img src="" class="preview-img" id="previewImg" alt="Pratinjau Banner">
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Schedule List Table Card -->
<div class="card card-primary" style="margin-top: 24px;">
    <div class="form-section-title"><i class="bi bi-table"></i> Daftar Jadwal Notifikasi Aktif</div>
    
    <div class="table-responsive">
        <table class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th>Judul & Pesan</th>
                    <th>Target</th>
                    <th>Jadwal Kirim</th>
                    <th>Kategori / Audio</th>
                    <th>Status</th>
                    <th>Terakhir Dikirim</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $sched)
                    <tr>
                        <td style="max-width: 250px;">
                            <div style="font-weight: 700; color: #fff; margin-bottom: 4px;">{{ $sched->judul }}</div>
                            <div style="font-size: 12px; color: var(--text-secondary); white-space: normal; word-break: break-all;">
                                {{ Str::limit($sched->deskripsi, 80) }}
                            </div>
                            @if($sched->gambar_url)
                                <div style="margin-top: 6px;">
                                    <span class="badge badge-success" style="font-size: 0.75em; background-color: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2);">
                                        <i class="bi bi-image"></i> Gambar Lampiran
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($sched->fcm_token)
                                <span class="badge badge-warning" style="background-color: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2); color: var(--warning);">
                                    <i class="bi bi-person-fill"></i> Target Khusus (Siswa)
                                </span>
                            @else
                                <span class="badge badge-primary" style="background-color: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2); color: #3b82f6;">
                                    <i class="bi bi-broadcast"></i> Topik: {{ $sched->topik ?: 'cbt_notif' }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--primary); font-size: 14px; margin-bottom: 4px;">
                                <i class="bi bi-clock-fill"></i> {{ $sched->schedule_time }} WIB
                            </div>
                            <div style="font-size: 11px; color: var(--text-secondary); white-space: normal;">
                                @php
                                    $dayTranslations = [
                                        'Monday' => 'Senin',
                                        'Tuesday' => 'Selasa',
                                        'Wednesday' => 'Rabu',
                                        'Thursday' => 'Kamis',
                                        'Friday' => 'Jumat',
                                        'Saturday' => 'Sabtu',
                                        'Sunday' => 'Minggu',
                                        'ALL' => 'Setiap Hari'
                                    ];
                                    $renderedDays = array_map(function($d) use ($dayTranslations) {
                                        return $dayTranslations[$d] ?? $d;
                                    }, $sched->days_of_week ?? []);
                                @endphp
                                {{ implode(', ', $renderedDays) }}
                            </div>
                        </td>
                        <td>
                            <div>
                                <span class="badge badge-secondary" style="font-size: 0.8em; text-transform: uppercase;">
                                    {{ $sched->category }}
                                </span>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px; word-break: break-all; white-space: normal;">
                                <i class="bi bi-volume-up-fill"></i> 
                                @if(is_array($sched->custom_sound))
                                    @php
                                        $soundNames = array_map(function($s) {
                                            return $s === 'default' ? 'default' : basename($s);
                                        }, $sched->custom_sound);
                                    @endphp
                                    {{ implode(', ', $soundNames) }}
                                @else
                                    {{ $sched->custom_sound ? basename($sched->custom_sound) : 'default' }}
                                @endif
                            </div>
                        </td>
                        <td>
                            <form action="{{ route('admin.notifications.scheduled.toggle', $sched->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-toggle-active" title="Klik untuk mengubah status">
                                    @if($sched->is_active)
                                        <span class="badge badge-success" style="cursor: pointer; box-shadow: 0 0 8px rgba(16,185,129,0.2);">
                                            <i class="bi bi-check-circle-fill"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge badge-danger" style="cursor: pointer; background-color: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: var(--danger);">
                                            <i class="bi bi-dash-circle-fill"></i> Nonaktif
                                        </span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td style="font-size: 12px; color: var(--text-secondary);">
                            @if($sched->last_sent_at)
                                {{ $sched->last_sent_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                <div style="font-size: 10px; color: var(--text-muted);">
                                    {{ $sched->last_sent_at->timezone('Asia/Jakarta')->diffForHumans() }}
                                </div>
                            @else
                                <span style="font-style: italic; color: var(--text-muted);">Belum pernah</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <form action="{{ route('admin.notifications.scheduled.destroy', $sched->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal notifikasi ini?');" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 6px 10px; border-radius: var(--radius-sm);">
                                    <i class="bi bi-trash-fill"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 40px; color: var(--text-muted);">
                            <i class="bi bi-alarm" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                            Belum ada jadwal notifikasi rutin yang dikonfigurasi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const groupTopic = document.getElementById('groupTopic');
    const groupToken = document.getElementById('groupToken');
    const toggleTopic = document.getElementById('toggleTopic');
    const toggleToken = document.getElementById('toggleToken');

    const inputTopic = document.getElementById('topik');
    const inputToken = document.getElementById('fcm_token');

    const previewTitle = document.getElementById('previewTitle');
    const previewDesc = document.getElementById('previewDesc');
    const previewImg = document.getElementById('previewImg');
    const previewImgContainer = document.getElementById('previewImgContainer');

    function switchTarget(type) {
        if (type === 'topic') {
            toggleTopic.classList.add('active');
            toggleToken.classList.remove('active');
            groupTopic.style.display = 'block';
            groupToken.style.display = 'none';
            inputToken.value = ''; // clear token input
        } else {
            toggleToken.classList.add('active');
            toggleTopic.classList.remove('active');
            groupTopic.style.display = 'none';
            groupToken.style.display = 'block';
        }
    }

    function updatePreview() {
        const titleInput = document.getElementById('judul').value;
        const descInput = document.getElementById('deskripsi').value;
        const imageUrlSelect = document.getElementById('gambar_url').value;

        previewTitle.textContent = titleInput.trim() !== '' ? titleInput : 'Judul Notifikasi Baru';
        previewDesc.textContent = descInput.trim() !== '' ? descInput : 'Deskripsi ringkas notifikasi yang dikirimkan oleh proktor akan muncul di sini.';

        if (imageUrlSelect.trim() !== '') {
            previewImg.src = imageUrlSelect;
            previewImgContainer.style.display = 'block';
        } else {
            previewImgContainer.style.display = 'none';
        }
    }

    function previewLocalImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImgContainer.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function selectAllDays() {
        const checkboxes = document.querySelectorAll('.day-cb');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
        });
    }

    // Dynamic duration field toggle
    const categorySelect = document.getElementById('category');
    const durationField = document.getElementById('durationField');
    if (categorySelect && durationField) {
        categorySelect.addEventListener('change', function () {
            if (this.value === 'exam_alert') {
                durationField.style.display = 'block';
            } else {
                durationField.style.display = 'none';
            }
        });
    }
</script>
@endsection
