@extends('layouts.admin')

@section('title', 'Daftar Pelanggaran')

@section('header_title', 'Pelanggaran & Log Ban Siswa')

@section('content')
<style>
    .filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .filter-left {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .filter-btn {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
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

    .filter-btn.active.banned {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border-color: rgba(239, 68, 68, 0.3);
    }

    .search-box {
        position: relative;
        width: 100%;
        max-width: 320px;
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

    .btn-pardon {
        background-color: rgba(16, 185, 129, 0.15);
        color: var(--primary);
        border: 1px solid rgba(16, 185, 129, 0.2);
        padding: 6px 12px;
        border-radius: var(--radius-sm);
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: var(--transition);
    }

    .btn-pardon:hover {
        background-color: var(--primary);
        color: #000;
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.3);
    }
</style>

<div class="card card-danger">
    <div class="filter-bar">
        <!-- Status Filter Buttons -->
        <div class="filter-left">
            <a href="{{ route('admin.violations.index', ['status' => 'BANNED', 'search' => $search]) }}" class="filter-btn banned {{ $statusFilter === 'BANNED' ? 'active' : '' }}">
                <i class="bi bi-shield-slash-fill"></i> Terkunci (Ban Aktif)
            </a>
            <a href="{{ route('admin.violations.index', ['status' => 'UNBANNED', 'search' => $search]) }}" class="filter-btn {{ $statusFilter === 'UNBANNED' ? 'active' : '' }}">
                <i class="bi bi-shield-check"></i> Sudah Dilepas
            </a>
            <a href="{{ route('admin.violations.index', ['status' => 'ALL', 'search' => $search]) }}" class="filter-btn {{ $statusFilter === 'ALL' ? 'active' : '' }}">
                Semua Riwayat
            </a>
        </div>

        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            @if($statusFilter === 'BANNED')
                <form id="bulkUnbanForm" action="{{ route('admin.violations.bulk-unban') }}" method="POST" onsubmit="return confirmBulkUnban(event);">
                    @csrf
                    <input type="hidden" name="ids" id="bulkUnbanIds">
                    <button type="submit" class="btn btn-warning btn-sm" id="bulkUnbanBtn" disabled style="background-color: var(--warning); color: #000; border: none; font-weight: 600; padding: 8px 16px; border-radius: var(--radius-md);">
                        <i class="bi bi-unlock-fill me-1"></i> Buka Kunci Terpilih (<span id="selectedBanCount">0</span>)
                    </button>
                </form>
            @endif

            <!-- Search input box -->
            <form action="{{ route('admin.violations.index') }}" method="GET" class="search-box">
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama siswa atau alasan...">
                <i class="bi bi-search"></i>
            </form>
        </div>
    </div>

    <!-- Responsive Violations Table -->
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    @if($statusFilter === 'BANNED')
                        <th width="40" class="text-center">
                            <input type="checkbox" id="selectAllBans" style="cursor: pointer;" title="Pilih Semua">
                        </th>
                    @endif
                    <th>Nama Siswa</th>
                    <th>Detail Pelanggaran (Keluar Aplikasi)</th>
                    <th>Spesifikasi Perangkat</th>
                    <th>Status Kunci</th>
                    <th>Waktu Kejadian</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($violations as $violation)
                    <tr>
                        @if($statusFilter === 'BANNED')
                            <td class="text-center">
                                <input type="checkbox" class="unban-check" value="{{ $violation->id }}" data-name="{{ addslashes($violation->student_name) }}" style="cursor: pointer;">
                            </td>
                        @endif
                        <td style="font-weight: 600; color: #fff;">
                            {{ $violation->student_name }}
                            @if(isset($violation->total_bans) && $violation->total_bans > 1)
                                <br>
                                <span class="badge badge-warning" style="font-size: 0.7em; cursor: pointer; margin-top: 4px;" onclick="openHistoryModal({{ $violation->id }})">
                                    <i class="bi bi-clock-history"></i> Riwayat Ban: {{ $violation->total_bans }}x
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--danger); margin-bottom: 4px;">
                                {{ $violation->reason }}
                            </div>
                            <div style="font-size: 12px; color: var(--text-secondary);">
                                Durasi Ban Otomatis: {{ $violation->duration_minutes }} Menit
                            </div>
                        </td>
                        <td>
                            <div><i class="bi bi-phone"></i> {{ $violation->device_model }}</div>
                            <div style="font-size: 12px; color: var(--text-secondary);">Android {{ $violation->android_version }}</div>
                        </td>
                        <td>
                            @if($violation->status === 'BANNED')
                                <span class="badge badge-danger"><i class="bi bi-lock-fill"></i> Terkunci</span>
                            @else
                                <span class="badge badge-success"><i class="bi bi-unlock-fill"></i> Dilepas</span>
                            @endif
                        </td>
                        <td style="font-size: 13px; color: var(--text-secondary);">
                            @if($violation->created_at)
                                {{ $violation->created_at->format('d M Y, H:i') }} WIB
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $violation->created_at->diffForHumans() }}</div>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">
                            @if($violation->status === 'BANNED')
                                <form action="{{ route('admin.violations.unban', $violation->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin melepas status ban (Pardon) untuk {{ $violation->student_name }}?');" style="display: inline-block;">
                                    @csrf
                                    <button type="submit" class="btn-pardon">
                                        <i class="bi bi-shield-check"></i>
                                        Pardon (Buka Ban)
                                    </button>
                                </form>
                            @else
                                <span style="font-size: 12px; color: var(--text-muted); font-style: italic;">Selesai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $statusFilter === 'BANNED' ? 7 : 6 }}" class="text-center" style="padding: 40px; color: var(--text-muted);">
                            <i class="bi bi-emoji-sunglasses" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                            Tidak ada data pelanggaran siswa yang terdaftar untuk kategori ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination links -->
    <div class="pagination-container">
        <div style="font-size: 13px; color: var(--text-secondary);">
            Menampilkan {{ $violations->firstItem() ?? 0 }} - {{ $violations->lastItem() ?? 0 }} dari {{ $violations->total() }} pelanggaran
        </div>
        <div>
            {{ $violations->appends(['status' => $statusFilter, 'search' => $search])->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAllBans');
        const bulkBtn = document.getElementById('bulkUnbanBtn');
        const countSpan = document.getElementById('selectedBanCount');
        const bulkIdsInput = document.getElementById('bulkUnbanIds');
        const checkboxes = document.querySelectorAll('.unban-check');

        if (!bulkBtn) return;

        function updateBulkUI() {
            const checked = document.querySelectorAll('.unban-check:checked');
            const selectedIds = Array.from(checked).map(cb => cb.value);
            
            if (countSpan) countSpan.textContent = checked.length;
            bulkBtn.disabled = checked.length === 0;
            bulkIdsInput.value = selectedIds.join(',');

            if (selectAll) {
                selectAll.checked = checked.length === checkboxes.length && checkboxes.length > 0;
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                updateBulkUI();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkUI);
        });

        window.confirmBulkUnban = function (event) {
            const checked = document.querySelectorAll('.unban-check:checked');
            if (checked.length === 0) {
                event.preventDefault();
                return false;
            }

            const names = Array.from(checked).map(cb => cb.getAttribute('data-name'));
            const confirmMsg = `Apakah Anda yakin ingin membuka blokir untuk ${checked.length} siswa berikut?\n\n` + names.join(', ') + `\n\nAplikasi di HP mereka akan otomatis terbuka.`;

            if (!confirm(confirmMsg)) {
                event.preventDefault();
                return false;
            }

            // Disable button to prevent double submit
            bulkBtn.disabled = true;
            bulkBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses...';
            return true;
        };

        window.openHistoryModal = function (id) {
            const modal = document.getElementById('modalHistory-' + id);
            if (modal) modal.classList.add('active');
        };

        window.closeHistoryModal = function (id) {
            const modal = document.getElementById('modalHistory-' + id);
            if (modal) modal.classList.remove('active');
        };
    });
</script>

@if($statusFilter === 'UNBANNED')
    @foreach($violations as $violation)
        @if(isset($violation->history) && count($violation->history) > 1)
            <!-- Elegant Glassmorphic Modal for History -->
            <div class="custom-modal" id="modalHistory-{{ $violation->id }}">
                <div class="modal-content" style="max-width: 600px;">
                    <button type="button" class="modal-close" onclick="closeHistoryModal({{ $violation->id }})"><i class="bi bi-x"></i></button>
                    <h2 class="modal-title">
                        <i class="bi bi-clock-history"></i>
                        Riwayat Pelanggaran: {{ $violation->student_name }}
                    </h2>
                    
                    <div style="max-height: 400px; overflow-y: auto; margin-top: 16px;">
                        @foreach($violation->history as $index => $hist)
                            <div style="background-color: rgba(7, 11, 19, 0.4); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                                <div style="text-align: left;">
                                    <div style="font-weight: 700; color: var(--danger); margin-bottom: 4px;">{{ $hist->reason }}</div>
                                    <small style="color: var(--text-muted);"><i class="bi bi-calendar-event"></i> {{ $hist->created_at ? $hist->created_at->format('d M Y, H:i') . ' WIB' : '-' }}</small>
                                    @if($index === 0)
                                        <span class="badge badge-success" style="font-size: 0.75em; margin-left: 8px;">Terbaru</span>
                                    @endif
                                </div>
                                <div class="badge badge-danger">{{ $hist->duration_minutes }} Mnt</div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; margin-top: 24px; gap: 12px;">
                        <button type="button" class="btn btn-secondary" onclick="closeHistoryModal({{ $violation->id }})">Tutup</button>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endif

@endsection
