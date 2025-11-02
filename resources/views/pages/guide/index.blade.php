@extends('layouts.app')

@section('title', 'Hướng dẫn - Pink Novel')
@section('description', 'Hướng dẫn sử dụng Pink Novel - Hướng dẫn')
@section('keywords', 'hướng dẫn, pink novel, truyện, câu hỏi thường gặp, FAQ')

@section('content')
<div class="container py-5 animate__animated animate__fadeIn">
    <!-- Top Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="guide-faq-banner text-center py-3 px-4 mb-4">
                <h2 class="mb-0 fw-bold">Hướng dẫn</h2>
            </div>
        </div>
    </div>
    
    <div class="row">
        @forelse($guides as $index => $guide)
        <div class="col-12 col-md-6 col-lg-4 mb-3">
            <a href="{{ route('guide.show', $guide->slug) }}" class="guide-item-link text-decoration-none">
                <div class="guide-item mb-3 animate__animated animate__fadeInUp" style="animation-delay: {{ $index * 0.05 }}s">
                    <div class="d-flex align-items-center">
                        <div class="guide-number-icon me-3">
                            <span class="guide-number">{{ $index + 1 }}</span>
                        </div>
                        <div class="guide-item-bar flex-grow-1">
                            <span class="guide-item-text">{{ $guide->title }}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fa-solid fa-circle-info fa-3x mb-3 text-muted"></i>
                <p class="lead">Chưa có hướng dẫn nào.</p>
                <p>Vui lòng quay lại sau.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
    /* FAQ Banner */
    .guide-faq-banner {
        background: var(--primary-color-4);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    /* Guide Item */
    .guide-item {
        transition: transform 0.2s ease;
    }
    
    .guide-item:hover {
        transform: translateX(5px);
    }
    
    .guide-item-link {
        display: block;
    }
    
    .guide-item-link:hover {
        text-decoration: none;
    }
    
    /* Number Icon */
    .guide-number-icon {
        width: 50px;
        height: 50px;
        min-width: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-color-3);
        border-radius: 8px;
        transform: rotate(-12deg);
        box-shadow: 0 4px 8px rgba(216, 107, 107, 0.3);
    }
    
    .guide-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        transform: rotate(12deg);
        line-height: 1;
    }
    
    /* Item Bar */
    .guide-item-bar {
        background: var(--primary-color-4);
        border-radius: 8px;
        padding: 12px 16px;
        min-height: 50px;
        display: flex;
        align-items: center;
        transition: background-color 0.2s ease;
    }
    
    .guide-item:hover .guide-item-bar {
        background: var(--primary-color-2);
    }
    
    .guide-item-text {
        color: var(--color-text);
        font-weight: 500;
        font-size: 0.95rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .guide-number-icon {
            width: 45px;
            height: 45px;
            min-width: 45px;
        }
        
        .guide-number {
            font-size: 1.3rem;
        }
        
        .guide-item-bar {
            padding: 10px 14px;
            min-height: 45px;
        }
        
        .guide-item-text {
            font-size: 0.9rem;
        }
    }
</style>
@endpush

