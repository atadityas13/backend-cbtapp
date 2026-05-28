@extends('layouts.admin')
 
@yield('title', 'Manajemen Media')
 
@section('header_title', 'Manajemen File Media')
 
@section('content')
<style>
    .split-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }
 
    @media (max-width: 991px) {
        .split-layout {
            grid-template-columns: 1fr;
        }
    }
 
    .media-card {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.3);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
 
    .media-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
 
    .media-card-body {
        padding: 24px;
        flex-grow: 1;
        overflow-y: auto;
        max-height: 500px;
    }
 
    .image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 16px;
    }
 
    .image-item {
        background-color: rgba(7, 11, 19, 0.4);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: var(--transition);
    }
 
    .image-item:hover {
        border-color: rgba(16, 185, 129, 0.3);
        background-color: var(--bg-surface-hover);
        transform: translateY(-2px);
    }
 
    .image-thumbnail {
        width: 100%;
        height: 90px;
        object-fit: cover;
        border-radius: var(--radius-sm);
        margin-bottom: 8px;
        background-color: #000;
    }
 
    .media-name {
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary);
        width: 100%;
        text-align: center;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
        margin-bottom: 12px;
    }
 
    .audio-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
 
    .audio-item {
        background-color: rgba(7, 11, 19, 0.4);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        transition: var(--transition);
    }
 
    .audio-item:hover {
        border-color: rgba(16, 185, 129, 0.3);
        background-color: var(--bg-surface-hover);
    }
 
    .audio-info {
        flex-grow: 1;
        min-width: 0;
    }
 
    .audio-name {
        font-size: 13px;
        font-weight: 600;
        color: #fff;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
        margin-bottom: 6px;
    }
 
    .btn-delete-media {
        background: none;
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: var(--danger);
        padding: 6px 12px;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
 
    .btn-delete-media:hover {
        background-color: var(--danger-glow);
        border-color: rgba(239, 68, 68, 0.4);
        color: var(--danger-hover);
    }
</style>
 
<div class="split-layout">
    
    <!-- Kolom Kiri: Galeri Gambar -->
    <div class="media-card" style="border-top: 3px solid var(--primary);">
        <div class="media-card-header">
            <i class="bi bi-image" style="color: var(--primary);"></i>
            <span>Daftar Gambar Upload</span>
        </div>
        <div class="media-card-body">
            @if(empty($uploadedImageList))
                <div class="text-center" style="padding: 40px; color: var(--text-muted);">
                    <i class="bi bi-images" style="font-size: 36px; display: block; margin-bottom: 12px;"></i>
                    Belum ada gambar yang diupload.
                </div>
            @else
                <div class="image-grid">
                    @foreach($uploadedImageList as $img)
                        <div class="image-item">
                            <img src="{{ asset('uploads/' . $img) }}" class="image-thumbnail" alt="{{ $img }}">
                            <div class="media-name" title="{{ $img }}">{{ $img }}</div>
                            <form action="{{ route('admin.media.destroy') }}" method="POST" onsubmit="return confirm('Hapus gambar ini?');" style="width: 100%;">
                                @csrf
                                <input type="hidden" name="file_name" value="{{ $img }}">
                                <input type="hidden" name="type" value="image">
                                <button type="submit" class="btn-delete-media" style="width: 100%;">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
 
    <!-- Kolom Kanan: Galeri Audio -->
    <div class="media-card" style="border-top: 3px solid var(--warning);">
        <div class="media-card-header">
            <i class="bi bi-music-note-beamed" style="color: var(--warning);"></i>
            <span>Daftar Audio Upload (.mp3)</span>
        </div>
        <div class="media-card-body">
            @if(empty($uploadedAudioList))
                <div class="text-center" style="padding: 40px; color: var(--text-muted);">
                    <i class="bi bi-music-note-list" style="font-size: 36px; display: block; margin-bottom: 12px;"></i>
                    Belum ada audio yang diupload.
                </div>
            @else
                <div class="audio-list">
                    @foreach($uploadedAudioList as $audio)
                        <div class="audio-item">
                            <div class="audio-info">
                                <div class="audio-name" title="{{ $audio }}">{{ $audio }}</div>
                                <audio controls style="height: 32px; width: 100%; max-width: 240px; outline: none; border-radius: 4px; background: #070b13;">
                                    <source src="{{ asset('uploads/audio/' . $audio) }}" type="audio/mpeg">
                                </audio>
                            </div>
                            <form action="{{ route('admin.media.destroy') }}" method="POST" onsubmit="return confirm('Hapus audio ini?');">
                                @csrf
                                <input type="hidden" name="file_name" value="{{ $audio }}">
                                <input type="hidden" name="type" value="audio">
                                <button type="submit" class="btn-delete-media">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
 
</div>
@endsection
