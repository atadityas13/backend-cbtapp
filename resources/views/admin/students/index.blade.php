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
                    <th>Topik</th>
                    <th>Android ID</th>
                    <th>Nama Device</th>
                    <th>FCM Token</th>
                    <th>Waktu Registrasi</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td style="font-weight: 600; color: #fff;">{{ $student->full_name }}</td>
                        <td>
                            <span class="badge badge-success">{{ $student->topic }}</span>
                        </td>
                        <td style="font-family: monospace; font-size: 13px;">{{ $student->android_id }}</td>
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
                            <div class="token-preview" title="Klik untuk menyalin token lengkap" onclick="navigator.clipboard.writeText('{{ $student->fcm_token }}'); alert('Token disalin ke clipboard!');">
                                {{ $student->fcm_token }}
                            </div>
                        </td>
                        <td style="font-size: 13px; color: var(--text-secondary);">
                            {{ $student->registration_timestamp }}
                        </td>
                        <td class="text-right">
                            <div class="inline-actions" style="justify-content: flex-end;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="openEditModal({{ $student->id }}, '{{ addslashes($student->full_name) }}', '{{ $student->topic }}', '{{ $student->android_id }}')">
                                    <i class="bi bi-pencil-fill" style="color: var(--primary);"></i>
                                </button>
                                
                                <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus registrasi perangkat untuk {{ $student->full_name }}? Siswa harus mendaftar ulang di aplikasinya.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm" style="border-color: rgba(239, 68, 68, 0.2);">
                                        <i class="bi bi-trash3-fill" style="color: var(--danger);"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 40px; color: var(--text-muted);">
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
                <label for="modal_topic" class="form-label">Topik Notifikasi (e.g. cbt_notif)</label>
                <input type="text" name="topic" id="modal_topic" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="modal_android_id" class="form-label">Android ID</label>
                <input type="text" name="android_id" id="modal_android_id" class="form-control" required>
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
    const modalTopic = document.getElementById('modal_topic');
    const modalAndroidId = document.getElementById('modal_android_id');

    function openEditModal(id, name, topic, androidId) {
        // Set action url
        editForm.action = `/admin/students/${id}/update`;
        
        // Populate inputs
        modalName.value = name;
        modalTopic.value = topic;
        modalAndroidId.value = androidId;
        
        // Open modal
        editModal.classList.add('active');
    }

    function closeEditModal() {
        editModal.classList.remove('active');
    }
    
    // Close modal on click outside content
    editModal.addEventListener('click', (e) => {
        if (e.target === editModal) {
            closeEditModal();
        }
    });
</script>
@endsection
