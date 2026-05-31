@extends('layouts.admin')

@section('title', 'Bantuan Siswa')

@section('header_title', 'Pusat Bantuan & Keluhan Siswa')

@section('content')
<style>
    .help-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 24px;
        margin-top: 16px;
    }

    .help-card {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 24px;
        position: relative;
        overflow: hidden;
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 4px 25px -5px rgba(0, 0, 0, 0.4);
    }

    .help-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px -5px rgba(0, 0, 0, 0.5);
    }

    .help-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
    }

    .help-card.pending {
        border-color: rgba(239, 68, 68, 0.2);
    }
    .help-card.pending::before {
        background: linear-gradient(90deg, var(--danger) 0%, #f87171 100%);
        box-shadow: 0 2px 10px rgba(239, 68, 68, 0.4);
    }

    .help-card.answered {
        border-color: rgba(16, 185, 129, 0.2);
    }
    .help-card.answered::before {
        background: linear-gradient(90deg, var(--primary) 0%, #34d399 100%);
        box-shadow: 0 2px 10px rgba(16, 185, 129, 0.4);
    }

    .card-header-help {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .student-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .student-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background-color: var(--primary-glow);
        border: 2px solid var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: var(--primary);
        font-size: 16px;
    }

    .student-avatar.pending {
        background-color: var(--danger-glow);
        border-color: var(--danger);
        color: var(--danger);
    }

    .student-details h4 {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .student-details p {
        font-size: 12px;
        color: var(--text-secondary);
    }

    .badge-status {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .badge-status.pending {
        background-color: var(--danger-glow);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.2);
        animation: pulse-glow 2s infinite;
    }

    .badge-status.answered {
        background-color: var(--primary-glow);
        color: var(--primary);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .message-box {
        background-color: var(--bg-base);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 16px;
        margin-bottom: 16px;
        font-size: 13px;
        line-height: 1.5;
        color: var(--text-primary);
        position: relative;
    }

    .message-box::after {
        content: '';
        position: absolute;
        left: 20px;
        top: -8px;
        border-width: 0 8px 8px 8px;
        border-style: solid;
        border-color: transparent transparent var(--border-color) transparent;
    }

    .reply-box {
        background-color: rgba(16, 185, 129, 0.05);
        border: 1px solid rgba(16, 185, 129, 0.15);
        border-radius: var(--radius-md);
        padding: 14px;
        margin-bottom: 16px;
        font-size: 13px;
        line-height: 1.5;
        color: var(--primary);
    }

    .reply-box strong {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }

    .reply-form {
        margin-top: 16px;
        display: none;
        animation: fadeIn 0.25s ease-out;
    }

    .form-control-help {
        width: 100%;
        background-color: var(--bg-base);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        color: var(--text-primary);
        padding: 10px 14px;
        font-family: inherit;
        font-size: 13px;
        resize: none;
        outline: none;
        transition: var(--transition);
    }

    .form-control-help:focus {
        border-color: var(--primary);
        box-shadow: 0 0 10px var(--primary-glow);
    }

    .card-actions-help {
        display: flex;
        gap: 12px;
        margin-top: 16px;
    }

    .btn-help {
        flex-grow: 1;
        padding: 10px 16px;
        border-radius: var(--radius-md);
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: var(--transition);
        border: none;
    }

    .btn-help-primary {
        background-color: var(--primary);
        color: #000;
    }

    .btn-help-primary:hover {
        background-color: var(--primary-hover);
        box-shadow: 0 0 12px var(--primary-glow);
    }

    .btn-help-secondary {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--primary);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .btn-help-secondary:hover {
        background-color: var(--primary);
        color: #000;
    }

    .btn-help-danger {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .btn-help-danger:hover {
        background-color: var(--danger);
        color: #fff;
        box-shadow: 0 0 12px var(--danger-glow);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes pulse-glow {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    .empty-state {
        text-align: center;
        padding: 60px 24px;
        color: var(--text-secondary);
    }

    .empty-state i {
        font-size: 56px;
        color: var(--primary);
        opacity: 0.3;
        display: block;
        margin-bottom: 16px;
    }

    .empty-state p {
        font-size: 14px;
    }
</style>

@php
    $resolvedCount = \App\Models\CbtBantuanProktor::where('status', 'RESOLVED')->count();
@endphp

{{-- Tab Navigation --}}
<div style="display:flex;gap:4px;margin-bottom:24px;background:var(--bg-surface);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:6px;">
    <a href="{{ route('admin.help.index') }}"
       style="flex:1;text-align:center;padding:10px 16px;border-radius:var(--radius-md);font-size:13px;font-weight:600;background:var(--primary);color:#000;box-shadow:0 0 12px var(--primary-glow);text-decoration:none;">
        <i class="bi bi-headset me-1"></i> Antrean Aktif
        @if($helps->count() > 0)
            <span style="background:rgba(0,0,0,0.2);border-radius:10px;padding:1px 7px;font-size:11px;margin-left:4px;">{{ $helps->count() }}</span>
        @endif
    </a>
    <a href="{{ route('admin.help.history') }}"
       style="flex:1;text-align:center;padding:10px 16px;border-radius:var(--radius-md);font-size:13px;font-weight:600;color:var(--text-secondary);text-decoration:none;transition:var(--transition);"
       onmouseover="this.style.background='var(--bg-base)';this.style.color='var(--text-primary)'"
       onmouseout="this.style.background='';this.style.color='var(--text-secondary)'">
        <i class="bi bi-clock-history me-1"></i> Riwayat Selesai
        @if($resolvedCount > 0)
            <span style="background:var(--primary);color:#000;border-radius:10px;padding:1px 7px;font-size:11px;margin-left:4px;">{{ $resolvedCount }}</span>
        @endif
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 24px; padding: 16px; border-radius: var(--radius-md);">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
@endif

<div class="card card-primary" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Antrean Keluhan Aktif</h3>
            <p style="font-size: 13px; color: var(--text-secondary);">Melayani keluhan teknis siswa selama ujian secara langsung.</p>
        </div>
        <div style="font-size: 13px; color: var(--primary); font-weight: 600; display: flex; align-items: center; gap: 8px; background-color: var(--primary-glow); padding: 8px 16px; border-radius: 20px;">
            <i class="bi bi-patch-check-fill"></i> Sistem Komunikasi Real-Time Aktif
        </div>
    </div>
</div>

<div class="help-grid">
    @forelse($helps as $help)
        <div class="help-card {{ strtolower($help->status) }}">
            <div>
                <!-- Card Header -->
                <div class="card-header-help">
                    <div class="student-info">
                        <div class="student-avatar {{ strtolower($help->status) }}">
                            {{ strtoupper(substr($help->nama_siswa, 0, 1)) }}
                        </div>
                        <div class="student-details">
                            <h4>{{ $help->nama_siswa }}</h4>
                            <p><i class="bi bi-person-badge"></i> {{ $help->username }}</p>
                        </div>
                    </div>
                    <span class="badge-status {{ strtolower($help->status) }}">
                        @if($help->status === 'PENDING')
                            <i class="bi bi-hourglass-split"></i> PENDING
                        @else
                            <i class="bi bi-chat-left-dots"></i> JAWAB
                        @endif
                    </span>
                </div>

                <!-- Keluhan Siswa -->
                <div class="message-box">
                    {{ $help->pesan_siswa }}
                    <div style="font-size: 10px; color: var(--text-muted); margin-top: 8px; text-align: right;">
                        <i class="bi bi-clock"></i> {{ $help->created_at->timezone('Asia/Jakarta')->format('H:i:s') }} ({{ $help->created_at->diffForHumans() }})
                    </div>
                </div>

                <!-- Balasan Sebelumnya (jika ada) -->
                @if($help->status === 'ANSWERED' && !empty($help->balasan_proktor))
                    <div class="reply-box">
                        <strong>Jawaban Anda:</strong>
                        {{ $help->balasan_proktor }}
                    </div>
                @endif

                <!-- Form Balas (Hidden by default) -->
                <form id="replyForm-{{ $help->id }}" action="{{ route('admin.help.reply', $help->id) }}" method="POST" class="reply-form">
                    @csrf
                    <textarea name="balasan_proktor" rows="3" class="form-control-help" placeholder="Ketik balasan untuk siswa... (Maks 500 karakter)" required>{{ $help->balasan_proktor }}</textarea>
                    <div style="display: flex; gap: 8px; margin-top: 8px;">
                        <button type="submit" class="btn-help btn-help-primary">
                            <i class="bi bi-send-fill"></i> Kirim Jawaban
                        </button>
                        <button type="button" class="btn-help btn-help-danger" onclick="toggleReplyForm({{ $help->id }})" style="flex-grow: 0; padding: 10px;">
                            Batal
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card Actions -->
            <div class="card-actions-help" id="actions-{{ $help->id }}">
                @if($help->status === 'PENDING')
                    <button class="btn-help btn-help-primary" onclick="toggleReplyForm({{ $help->id }})">
                        <i class="bi bi-reply-fill"></i> Balas Keluhan
                    </button>
                @else
                    <button class="btn-help btn-help-secondary" onclick="toggleReplyForm({{ $help->id }})">
                        <i class="bi bi-pencil-fill"></i> Ubah Balasan
                    </button>
                @endif

                <form action="{{ route('admin.help.resolve', $help->id) }}" method="POST" style="flex-grow: 1; display: flex;">
                    @csrf
                    <button type="submit" class="btn-help btn-help-danger" style="width: 100%;">
                        <i class="bi bi-check-lg"></i> Selesaikan
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="card" style="grid-column: 1 / -1; margin-bottom: 0;">
            <div class="empty-state">
                <i class="bi bi-emoji-smile"></i>
                <h4 style="font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">Semua Bersih & Aman</h4>
                <p>Tidak ada antrean keluhan aktif saat ini. Semua siswa sedang ujian dengan lancar!</p>
            </div>
        </div>
    @endforelse
</div>

<script>
    // ──────────────────────────────────────────────
    // STATE TRACKING
    // ──────────────────────────────────────────────
    let isTyping = false;          // true saat proktor sedang mengetik di form
    let anyFormOpen = false;       // true saat ada form reply yang sedang terbuka
    let lastKnownCount = {{ $helps->count() }};  // jumlah tiket saat halaman dimuat
    let pollInterval = null;

    // ──────────────────────────────────────────────
    // TOGGLE FORM BALAS
    // ──────────────────────────────────────────────
    function toggleReplyForm(id) {
        const form = document.getElementById('replyForm-' + id);
        const actions = document.getElementById('actions-' + id);
        
        if (form.style.display === 'block') {
            form.style.display = 'none';
            actions.style.display = 'flex';
            anyFormOpen = false;
        } else {
            form.style.display = 'block';
            actions.style.display = 'none';
            anyFormOpen = true;
            const ta = form.querySelector('textarea');
            ta.focus();
        }
    }

    // ──────────────────────────────────────────────
    // PROTEKSI DOUBLE-SUBMIT (Set-based, lebih ketat)
    // ──────────────────────────────────────────────
    const _submittedForms = new Set();

    function guardForm(formEl) {
        formEl.addEventListener('submit', function(e) {
            // Cegah submit jika form ini sudah pernah dikirim dalam sesi ini
            if (_submittedForms.has(formEl.id)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
            // Tandai form sebagai sudah dikirim SEBELUM request berangkat
            _submittedForms.add(formEl.id);
            const btn = formEl.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Mengirim...';
        });
    }

    // ──────────────────────────────────────────────
    // CHIME AUDIO
    // ──────────────────────────────────────────────
    function playSciFiChime() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            
            const osc1 = audioCtx.createOscillator();
            const gain1 = audioCtx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(523.25, audioCtx.currentTime);
            osc1.frequency.exponentialRampToValueAtTime(783.99, audioCtx.currentTime + 0.12);
            gain1.gain.setValueAtTime(0.12, audioCtx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.55);
            osc1.connect(gain1);
            gain1.connect(audioCtx.destination);
            
            const osc2 = audioCtx.createOscillator();
            const gain2 = audioCtx.createGain();
            osc2.type = 'triangle';
            osc2.frequency.setValueAtTime(1046.50, audioCtx.currentTime + 0.08);
            gain2.gain.setValueAtTime(0.08, audioCtx.currentTime + 0.08);
            gain2.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.65);
            osc2.connect(gain2);
            gain2.connect(audioCtx.destination);
            
            osc1.start(); osc1.stop(audioCtx.currentTime + 0.55);
            osc2.start(audioCtx.currentTime + 0.08); osc2.stop(audioCtx.currentTime + 0.65);
        } catch (e) {
            console.log("Audio Context blocked or not supported", e);
        }
    }

    // ──────────────────────────────────────────────
    // SMART POLLING — hanya reload jika ada tiket baru
    // DAN tidak ada form yang sedang terbuka/diketik
    // ──────────────────────────────────────────────
    function pollForNewTickets() {
        // Jangan polling jika proktor sedang mengetik / form terbuka
        if (isTyping || anyFormOpen) return;

        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data && typeof data.pending_count !== 'undefined') {
                const newCount = data.pending_count;
                if (newCount > lastKnownCount && !isTyping && !anyFormOpen) {
                    playSciFiChime();
                    // Tunggu sebentar agar proktor sempat membaca notifikasi chime
                    setTimeout(() => {
                        if (!isTyping && !anyFormOpen) {
                            window.location.reload();
                        }
                    }, 2000);
                }
                lastKnownCount = newCount;
            }
        })
        .catch(() => {
            // Jika endpoint tidak mendukung JSON (blade biasa), fallback ke reload
            // HANYA jika tidak ada form yang terbuka
            if (!isTyping && !anyFormOpen) {
                window.location.reload();
            }
        });
    }

    // ──────────────────────────────────────────────
    // INISIALISASI
    // ──────────────────────────────────────────────
    document.addEventListener("DOMContentLoaded", function() {
        // Pasang proteksi double-submit ke semua form reply
        document.querySelectorAll('.reply-form').forEach(guardForm);

        // Pantau fokus textarea — pause polling saat mengetik
        document.querySelectorAll('.form-control-help').forEach(function(ta) {
            ta.addEventListener('focus', function() { isTyping = true; });
            ta.addEventListener('blur',  function() {
                // Beri jeda 3 detik setelah kehilangan fokus sebelum polling aktif kembali
                setTimeout(() => { isTyping = false; }, 3000);
            });
            ta.addEventListener('input', function() { isTyping = true; });
        });

        // Mainkan chime jika ada tiket PENDING saat halaman dimuat
        const pendingItems = document.querySelectorAll('.help-card.pending');
        if (pendingItems.length > 0) {
            document.body.addEventListener('click', function playOnce() {
                playSciFiChime();
                document.body.removeEventListener('click', playOnce);
            }, { once: true });
        }

        // Mulai polling setiap 20 detik (lebih longgar dari 15 agar tidak tergesa)
        pollInterval = setInterval(pollForNewTickets, 20000);
    });
</script>

@endsection
