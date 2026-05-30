@extends('layouts.admin')

@yield('title', 'Siswa Terdaftar')

@section('header_title', 'Manajemen Perangkat Siswa')

@section('content')
<style>
    .search-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .search-box {
        position: relative;
        flex-grow: 1;
        max-width: 480px;
    }

    .search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }

    .search-box .form-control {
        padding-left: 44px;
    }

    .token-preview {
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-family: monospace;
        color: var(--text-secondary);
        cursor: pointer;
    }

    .token-preview:hover {
        color: var(--primary);
    }

    /* Modal Styling */
    .custom-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(7, 11, 19, 0.85);
        backdrop-filter: blur(8px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .custom-modal.active {
        display: flex;
    }

    .modal-content {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        width: 100%;
        max-width: 500px;
        padding: 32px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
        position: relative;
        animation: modalFadeIn 0.3s ease-out;
    }

    @keyframes modalFadeIn {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .modal-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: 20px;
        cursor: pointer;
        transition: var(--transition);
    }

    .modal-close:hover {
        color: var(--danger);
    }

    .modal-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 24px;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .modal-title i {
        color: var(--primary);
    }

    .inline-actions {
        display: flex;
        gap: 8px;
    }
</style>

<div class="card card-primary">
    <div class="search-row">
        <!-- Left: Search form -->
        <form action="{{ route('admin.students.index') }}" method="GET" class="search-box">
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama siswa, topik, atau Android ID...">
            <i class="bi bi-search"></i>
        </form>
        
        <div>
            @if(!empty($search))
                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-x-circle"></i> Reset Pencarian
                </a>
            @endif
        </div>
    </div>

    <!-- Responsive Table -->
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Siswa</th>
                    <th>Device & Android</th>
                    <th>Poin Proteksi</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td style="font-weight: 600; color: #fff;">{{ $student->full_name }}</td>
                        <td>
                            @if(!empty($student->device_model))
                                <div><i class="bi bi-phone"></i> {{ $student->device_model }}</div>
                                @if(!empty($student->android_version))
                                    <div style="font-size: 11px; color: var(--text-muted);">Android {{ $student->android_version }}</div>
                                @endif
                            @elseif($student->latestViolation)
                                <div><i class="bi bi-phone"></i> {{ $student->latestViolation->device_model }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">Android {{ $student->latestViolation->android_version }}</div>
                            @else
                                <span class="text-muted" style="font-style: italic; font-size: 12px;">Tidak diketahui</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge" style="background-color: rgba(16, 185, 129, 0.15); color: var(--primary); font-weight: bold; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 13px; padding: 6px 12px;">
                                <i class="bi bi-shield-fill-check"></i> {{ $student->points }} Poin
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="inline-actions" style="justify-content: flex-end;">
                                <!-- Detail Button -->
                                <button type="button" class="btn btn-secondary btn-sm" onclick="openDetailModal({{ json_encode($student) }})" title="Detail Informasi">
                                    <i class="bi bi-eye-fill" style="color: var(--warning);"></i>
                                </button>

                                <!-- Edit Button -->
                                <button type="button" class="btn btn-secondary btn-sm" onclick="openEditModal({{ $student->id }}, '{{ addslashes($student->full_name) }}', {{ $student->points }})" title="Edit Data">
                                    <i class="bi bi-pencil-fill" style="color: var(--primary);"></i>
                                </button>
                                
                                <!-- Delete Button -->
                                <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus registrasi perangkat untuk {{ $student->full_name }}? Siswa harus mendaftar ulang di aplikasinya.');" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm" style="border-color: rgba(239, 68, 68, 0.2);" title="Hapus Registrasi">
                                        <i class="bi bi-trash3-fill" style="color: var(--danger);"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 40px; color: var(--text-muted);">
                            <i class="bi bi-people" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                            Tidak ada data perangkat siswa terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Custom Elegant Pagination Link layout -->
    <div class="pagination-container">
        <div style="font-size: 13px; color: var(--text-secondary);">
            Menampilkan {{ $students->firstItem() ?? 0 }} - {{ $students->lastItem() ?? 0 }} dari {{ $students->total() }} siswa
        </div>
        <div>
            {{ $students->appends(['search' => $search])->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Detail Student Dialog Modal -->
<div class="custom-modal" id="detailModal">
    <div class="modal-content" style="max-width: 600px;">
        <button type="button" class="modal-close" onclick="closeDetailModal()"><i class="bi bi-x"></i></button>
        <h2 class="modal-title">
            <i class="bi bi-person-badge-fill"></i>
            Detail Perangkat Siswa
        </h2>
        
        <div class="table-responsive" style="border: none;">
            <table class="table" style="margin-bottom: 0;">
                <tbody>
                    <tr>
                        <td style="font-weight: 700; width: 40%; color: var(--text-secondary); border-bottom: 1px solid var(--border-color);">Nama Lengkap</td>
                        <td id="detail_full_name" style="color: #fff; border-bottom: 1px solid var(--border-color); font-weight: 600;">-</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: var(--text-secondary); border-bottom: 1px solid var(--border-color);">Android ID</td>
                        <td id="detail_android_id" style="color: #fff; border-bottom: 1px solid var(--border-color); font-family: monospace;">-</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: var(--text-secondary); border-bottom: 1px solid var(--border-color);">Topik Notifikasi</td>
                        <td style="border-bottom: 1px solid var(--border-color);"><span class="badge badge-success" id="detail_topic">-</span></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: var(--text-secondary); border-bottom: 1px solid var(--border-color);">Tipe / Model Device</td>
                        <td id="detail_device_model" style="color: #fff; border-bottom: 1px solid var(--border-color);">-</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: var(--text-secondary); border-bottom: 1px solid var(--border-color);">Versi Android</td>
                        <td id="detail_android_version" style="color: #fff; border-bottom: 1px solid var(--border-color);">-</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: var(--text-secondary); border-bottom: 1px solid var(--border-color);">Poin Proteksi</td>
                        <td style="border-bottom: 1px solid var(--border-color);">
                            <span class="badge" id="detail_points" style="background-color: rgba(16, 185, 129, 0.15); color: var(--primary); font-weight: bold; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 13px; padding: 4px 8px;">-</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: var(--text-secondary); border-bottom: 1px solid var(--border-color);">Alarm Mute Lifetime</td>
                        <td id="detail_alarm_mute" style="color: #fff; border-bottom: 1px solid var(--border-color); font-weight: 600;">-</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: var(--text-secondary); border-bottom: 1px solid var(--border-color);">Waktu Registrasi</td>
                        <td id="detail_reg_time" style="color: #fff; border-bottom: 1px solid var(--border-color);">-</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: var(--text-secondary); border-bottom: none; vertical-align: top; padding-top: 12px;">FCM Token</td>
                        <td style="border-bottom: none; padding-top: 12px;">
                            <div class="token-preview" id="detail_fcm_token" style="max-width: 300px; word-break: break-all; white-space: normal; cursor: pointer; color: var(--text-secondary);" title="Klik untuk menyalin token lengkap" onclick="copyTokenFromDetail()">
                                -
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
            <button type="button" class="btn btn-secondary" onclick="closeDetailModal()">Tutup</button>
        </div>
    </div>
</div>

<!-- Edit Student Dialog Modal -->
<div class="custom-modal" id="editModal">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeEditModal()"><i class="bi bi-x"></i></button>
        <h2 class="modal-title">
            <i class="bi bi-pencil-square"></i>
            Edit Perangkat Siswa
        </h2>
        
        <form id="editForm" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="modal_name" class="form-label">Nama Lengkap Siswa</label>
                <input type="text" name="full_name" id="modal_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="modal_points" class="form-label">Poin Proteksi</label>
                <input type="number" name="points" id="modal_points" class="form-control" min="0" required>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 28px;">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');
    const modalName = document.getElementById('modal_name');
    const modalPoints = document.getElementById('modal_points');

    const detailModal = document.getElementById('detailModal');
    let activeToken = '';

    function openEditModal(id, name, points) {
        // Set action url
        editForm.action = `/admin/students/${id}/update`;
        
        // Populate inputs
        modalName.value = name;
        modalPoints.value = points;
        
        // Open modal
        editModal.classList.add('active');
    }

    function closeEditModal() {
        editModal.classList.remove('active');
    }

    function openDetailModal(student) {
        document.getElementById('detail_full_name').textContent = student.full_name;
        document.getElementById('detail_android_id').textContent = student.android_id;
        document.getElementById('detail_topic').textContent = student.topic;
        document.getElementById('detail_device_model').textContent = student.device_model || 'Tidak diketahui';
        document.getElementById('detail_android_version').textContent = student.android_version ? 'Android ' + student.android_version : 'Tidak diketahui';
        document.getElementById('detail_points').innerHTML = '<i class="bi bi-shield-fill-check"></i> ' + student.points + ' Poin';
        document.getElementById('detail_alarm_mute').textContent = student.alarm_muted_lifetime ? 'Aktif (Muted)' : 'Tidak Aktif';
        document.getElementById('detail_reg_time').textContent = student.registration_timestamp || student.created_at || '-';
        
        const fcmPreview = document.getElementById('detail_fcm_token');
        fcmPreview.textContent = student.fcm_token;
        activeToken = student.fcm_token;

        detailModal.classList.add('active');
    }

    function closeDetailModal() {
        detailModal.classList.remove('active');
    }

    function copyTokenFromDetail() {
        if (activeToken) {
            navigator.clipboard.writeText(activeToken);
            alert('Token disalin ke clipboard!');
        }
    }
    
    // Close modals on click outside content
    editModal.addEventListener('click', (e) => {
        if (e.target === editModal) closeEditModal();
    });

    detailModal.addEventListener('click', (e) => {
        if (e.target === detailModal) closeDetailModal();
    });
</script>
@endsection
