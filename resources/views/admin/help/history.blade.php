@extends('layouts.admin')

@section('title', 'Riwayat Bantuan Siswa')

@section('header_title', 'Riwayat Bantuan & Keluhan Siswa')

@section('content')
<style>
    .tab-bar {
        display: flex;
        gap: 4px;
        margin-bottom: 24px;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 6px;
    }
    .tab-bar a {
        flex: 1;
        text-align: center;
        padding: 10px 16px;
        border-radius: var(--radius-md);
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary);
        text-decoration: none;
        transition: var(--transition);
    }
    .tab-bar a.active {
        background: var(--primary);
        color: #000;
        box-shadow: 0 0 12px var(--primary-glow);
    }
    .tab-bar a:not(.active):hover {
        background: var(--bg-base);
        color: var(--text-primary);
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
    }
    .history-table th {
        text-align: left;
        padding: 12px 16px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border-color);
    }
    .history-table td {
        padding: 14px 16px;
        font-size: 13px;
        color: var(--text-primary);
        border-bottom: 1px solid rgba(255,255,255,0.04);
        vertical-align: top;
    }
    .history-table tr:last-child td {
        border-bottom: none;
    }
    .history-table tr:hover td {
        background: rgba(16, 185, 129, 0.03);
    }

    .msg-cell {
        max-width: 280px;
        line-height: 1.5;
    }
    .reply-cell {
        max-width: 280px;
        line-height: 1.5;
        color: var(--primary);
    }
    .avatar-sm {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--primary-glow);
        border: 1px solid var(--primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        color: var(--primary);
        flex-shrink: 0;
        margin-right: 10px;
    }
    .student-col {
        display: flex;
        align-items: center;
        white-space: nowrap;
    }
    .time-col {
        font-size: 11px;
        color: var(--text-secondary);
        white-space: nowrap;
    }
    .badge-resolved {
        background: rgba(16, 185, 129, 0.1);
        color: var(--primary);
        border: 1px solid rgba(16, 185, 129, 0.2);
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        gap: 4px;
    }
    .search-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        align-items: center;
    }
    .search-input {
        flex: 1;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 10px 16px;
        color: var(--text-primary);
        font-size: 13px;
        outline: none;
        transition: var(--transition);
    }
    .search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 8px var(--primary-glow);
    }
    .btn-search {
        background: var(--primary);
        color: #000;
        border: none;
        padding: 10px 20px;
        border-radius: var(--radius-md);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: var(--transition);
    }
    .btn-search:hover {
        background: var(--primary-hover);
    }
    .btn-clear-history {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.2);
        padding: 10px 18px;
        border-radius: var(--radius-md);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-clear-history:hover {
        background: var(--danger);
        color: #fff;
    }

    /* Premium Cyberpunk Emerald Pagination Styling */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
        flex-wrap: wrap;
        gap: 16px;
    }

    .pagination-container ul.pagination {
        display: flex;
        padding-left: 0;
        list-style: none;
        border-radius: var(--radius-md);
        gap: 6px;
        margin: 0;
        align-items: center;
    }

    .pagination-container li.page-item {
        margin: 0;
    }

    .pagination-container .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary);
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        text-decoration: none;
        transition: var(--transition);
    }

    .pagination-container .page-item:hover .page-link {
        background-color: var(--bg-base);
        color: var(--text-primary);
        border-color: var(--primary);
    }

    .pagination-container .page-item.active .page-link {
        background-color: var(--primary);
        color: #000;
        border-color: var(--primary);
        box-shadow: 0 0 10px var(--primary-glow);
    }

    .pagination-container .page-item.disabled .page-link {
        color: var(--text-muted);
        pointer-events: none;
        background-color: transparent;
        border-color: var(--border-color);
        opacity: 0.4;
    }

    .pagination-container nav p.text-sm {
        display: none !important; /* Hide redundant text info */
    }
</style>

{{-- Tab Navigation --}}
<div class="tab-bar">
    <a href="{{ route('admin.help.index') }}">
        <i class="bi bi-headset me-1"></i> Antrean Aktif
    </a>
    <a href="{{ route('admin.help.history') }}" class="active">
        <i class="bi bi-clock-history me-1"></i> Riwayat Selesai
        @if($totalResolved > 0)
            <span style="background:var(--primary);color:#000;border-radius:10px;padding:1px 7px;font-size:11px;margin-left:4px;">{{ $totalResolved }}</span>
        @endif
    </a>
</div>

{{-- Search & Clear --}}
<form method="GET" action="{{ route('admin.help.history') }}" class="search-bar">
    <input type="text" name="search" class="search-input"
        placeholder="Cari nama siswa atau isi keluhan..."
        value="{{ request('search') }}">
    <button type="submit" class="btn-search">
        <i class="bi bi-search"></i> Cari
    </button>
    @if(request('search'))
        <a href="{{ route('admin.help.history') }}" class="btn-search" style="background:var(--bg-surface);color:var(--text-secondary);border:1px solid var(--border-color);">
            <i class="bi bi-x-lg"></i> Reset
        </a>
    @endif
</form>

<div class="card card-primary" style="padding: 0; overflow: hidden;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
        <div>
            <h3 style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:2px;">
                Riwayat Keluhan Terselesaikan
            </h3>
            <p style="font-size:12px;color:var(--text-secondary);">
                {{ $histories->total() }} tiket ditemukan
                @if(request('search')) dari pencarian "{{ request('search') }}"@endif
            </p>
        </div>
        @if($totalResolved > 0)
        <form action="{{ route('admin.help.history.clear') }}" method="POST"
              onsubmit="return confirm('Hapus SEMUA {{ $totalResolved }} riwayat bantuan secara permanen? Tindakan ini tidak dapat dibatalkan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-clear-history">
                <i class="bi bi-trash3-fill"></i> Hapus Semua Riwayat
            </button>
        </form>
        @endif
    </div>

    @if($histories->isEmpty())
        <div style="text-align:center; padding: 60px 24px; color: var(--text-secondary);">
            <i class="bi bi-inbox" style="font-size:48px; color:var(--primary); opacity:0.3; display:block; margin-bottom:12px;"></i>
            <h4 style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:6px;">Riwayat Kosong</h4>
            <p style="font-size:13px;">
                @if(request('search'))
                    Tidak ada riwayat yang cocok dengan pencarian "{{ request('search') }}".
                @else
                    Belum ada tiket bantuan yang diselesaikan.
                @endif
            </p>
        </div>
    @else
        <div style="overflow-x: auto;">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Siswa</th>
                        <th>Keluhan</th>
                        <th>Jawaban Proktor</th>
                        <th>Status</th>
                        <th>Waktu Masuk</th>
                        <th>Waktu Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($histories as $i => $item)
                    <tr>
                        <td style="color:var(--text-secondary);font-size:12px;">
                            {{ $histories->firstItem() + $i }}
                        </td>
                        <td>
                            <div class="student-col">
                                <span class="avatar-sm">{{ strtoupper(substr($item->nama_siswa, 0, 1)) }}</span>
                                <div>
                                    <div style="font-weight:600;">{{ $item->nama_siswa }}</div>
                                    <div style="font-size:11px;color:var(--text-secondary);">{{ $item->android_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="msg-cell">{{ $item->pesan_siswa }}</td>
                        <td class="reply-cell">
                            @if($item->balasan_proktor)
                                {{ $item->balasan_proktor }}
                            @else
                                <span style="color:var(--text-secondary);font-style:italic;">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge-resolved">
                                <i class="bi bi-check-circle-fill me-1"></i>SELESAI
                            </span>
                        </td>
                        <td class="time-col">
                            {{ $item->created_at->timezone('Asia/Jakarta')->format('d M Y') }}<br>
                            <span style="color:var(--primary);">{{ $item->created_at->timezone('Asia/Jakarta')->format('H:i:s') }}</span>
                        </td>
                        <td class="time-col">
                            {{ $item->updated_at->timezone('Asia/Jakarta')->format('d M Y') }}<br>
                            <span style="color:var(--primary);">{{ $item->updated_at->timezone('Asia/Jakarta')->format('H:i:s') }}</span><br>
                            <span style="color:var(--text-muted);">{{ $item->created_at->diffForHumans($item->updated_at, true) }} respon</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($histories->hasPages())
        <div class="pagination-container" style="padding: 16px 24px;">
            <div style="font-size: 13px; color: var(--text-secondary);">
                Menampilkan {{ $histories->firstItem() ?? 0 }} - {{ $histories->lastItem() ?? 0 }} dari {{ $histories->total() }} riwayat
            </div>
            <div>
                {{ $histories->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    @endif
</div>
@endsection
