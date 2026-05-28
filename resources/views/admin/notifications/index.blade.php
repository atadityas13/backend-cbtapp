@extends('layouts.admin')

@yield('title', 'Kirim Notifikasi Push')

@section('header_title', 'Kirim Firebase Push Notification')

@section('content')
<style>
    .split-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    @media (max-width: 991px) {
        .split-layout {
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
</style>

<div class="split-layout">
    
    <!-- Left: Notification Sender Form -->
    <div class="card card-primary">
        <form action="{{ route('admin.notifications.send') }}" method="POST" enctype="multipart/form-data">
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
                <label for="judul" class="form-label">Judul Notifikasi</label>
                <input type="text" name="judul" id="judul" class="form-control" placeholder="Contoh: Pengumuman Penting Ujian Sumatif" required oninput="updatePreview()">
            </div>

            <div class="form-group">
                <label for="deskripsi" class="form-label">Deskripsi / Isi Pesan</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="4" placeholder="Masukkan detail informasi yang ingin dikirimkan..." required oninput="updatePreview()"></textarea>
            </div>

            <!-- Section 3: Optional Media & Actions -->
            <div class="form-section-title">3. Lampiran Gambar, Audio & Link (Opsional)</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="gambar_url" class="form-label">URL Gambar Banner</label>
                    <input type="text" name="gambar_url" id="gambar_url" class="form-control" placeholder="https://example.com/image.jpg" oninput="updatePreview()">
                </div>
                <div class="form-group">
                    <label for="gambar_file" class="form-label">Atau Unggah Gambar Lokal</label>
                    <input type="file" name="gambar_file" id="gambar_file" class="form-control" accept="image/*" onchange="previewLocalImage(this)">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="custom_sound" class="form-label">Nama Sound Aplikasi (e.g. sirens)</label>
                    <input type="text" name="custom_sound" id="custom_sound" class="form-control" placeholder="default">
                </div>
                <div class="form-group">
                    <label for="audio_file" class="form-label">Atau Unggah File Audio (.mp3)</label>
                    <input type="file" name="audio_file" id="audio_file" class="form-control" accept="audio/mpeg">
                </div>
            </div>

            <div class="form-group">
                <label for="link" class="form-label">URL Tindakan (Link Terbuka Ketika Diklik)</label>
                <input type="text" name="link" id="link" class="form-control" placeholder="https://mtsn11majalengka.sch.id">
            </div>

            <!-- Section 4: Advanced Parameters -->
            <div class="form-section-title">4. Parameter Pengiriman & Kategori</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="prioritas" class="form-label">Prioritas Pengiriman</label>
                    <select name="prioritas" id="prioritas" class="form-control">
                        <option value="high" selected>High (Bangunkan HP Segera)</option>
                        <option value="normal">Normal (Hemat Daya)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="jeda_pengiriman" class="form-label">Jeda Antar Kirim (Massal - Detik)</label>
                    <input type="number" name="jeda_pengiriman" id="jeda_pengiriman" class="form-control" value="0" min="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category" class="form-label">Kategori Pesan</label>
                    <select name="category" id="category" class="form-control">
                        <option value="normal" selected>Normal (Notifikasi Standar)</option>
                        <option value="exam_alert">Exam Alert (Teks Berjalan)</option>
                    </select>
                </div>
                <div class="form-group" id="durationField" style="display: none;">
                    <label for="duration" class="form-label">Durasi Teks Berjalan (Detik)</label>
                    <input type="number" name="duration" id="duration" class="form-control" value="30" min="5" max="300">
                </div>
            </div>

            <div style="margin-top: 32px;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="bi bi-send-fill"></i> Kirim Notifikasi Sekarang
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
                    <i class="bi bi-google-play"></i>
                    <span>GOOGLE PLAY SERVICES &bull; SEKARANG</span>
                </div>
                <div class="preview-body">
                    <div class="preview-app-title">CBT APP SERVICES</div>
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
        const imageUrlInput = document.getElementById('gambar_url').value;

        previewTitle.textContent = titleInput.trim() !== '' ? titleInput : 'Judul Notifikasi Baru';
        previewDesc.textContent = descInput.trim() !== '' ? descInput : 'Deskripsi ringkas notifikasi yang dikirimkan oleh proktor akan muncul di sini.';

        if (imageUrlInput.trim() !== '') {
            previewImg.src = imageUrlInput;
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
