@extends('layouts.app')

@section('title', $story->title . ' - Truyện Pink novel - Đọc Truyện Online Miễn Phí | ' . config('app.name'))

@section('description', Str::limit(strip_tags($story->description), 160))

@section('keyword',
    implode(', ', [
    $story->title,
    'đọc truyện ' . $story->title,
    'truyện online',
    $story->categories->pluck('name')->implode(', '),
    $story->user->name ?? 'tác giả',
    'Truyện Pink novel - Đọc Truyện
    Online Miễn Phí',
    $story->completed ? 'truyện hoàn thành' : 'truyện đang cập nhật',
    'novel',
    'web đọc truyện',
    ]))

    @push('styles')
        <style>
            .card-search {
                background: #e7e7e7;
                border-radius: 10px;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
                text-align: center;
                transition: transform 0.3s ease;
            }

            .search-wrapper {
                max-width: min(500px, 90%);
                margin: 0 auto;
            }

            .search-wrapper .form-control {
                height: 50px;
                border-radius: 25px 0 0 25px;
                border: none;
                padding-left: 20px;
            }

            .search-wrapper .btn {
                border-radius: 0 25px 25px 0;
                padding: 0 25px;
            }

            @media (max-width: 768px) {
                .search-wrapper .form-control {
                    height: 40px;
                }

                .search-wrapper .btn {
                    padding: 0 15px;
                }
            }

            .banner-image-home {
                width: 100%;
                height: 350px;
                object-position: center;
            }

            @media (max-width: 768px) {
                .banner-image-home {
                    height: 250px;
                }
            }

            @media (max-width: 576px) {
                .banner-image-home {
                    height: 200px;
                }
            }
        </style>
    @endpush

@section('content')
    <section id="page-story" class="">
        <div class=""></div>

        @include('components.info_book_home')



        <div class=" mt-4">

            <div class="container">
                @if (isset($story) && $story->has_combo)
                    @include('components.combo_story', ['story' => $story])
                @endif
            </div>

            <div class="" id="chapters">
                @if (!Auth()->check() || (Auth()->check() && Auth()->user()->ban_read == false))
                    @include('components.all_chapter', ['chapters' => $chapters])
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-sad-tear fa-4x text-muted mb-3 animate__animated animate__shakeX"></i>
                        <h5 class="text-danger">Bạn đã bị cấm đọc truyện!</h5>
                    </div>
                @endif
            </div>

            <div class="" id="comments">
                @if (!Auth()->check() || (Auth()->check() && Auth()->user()->ban_comment == false))
                    @include('components.comment', [
                        'pinnedComments' => $pinnedComments,
                        'regularComments' => $regularComments,
                    ])
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-sad-tear fa-4x text-muted mb-3 animate__animated animate__shakeX"></i>
                        <h5 class="text-danger">Bạn đã bị cấm bình luận!</h5>
                    </div>
                @endif
            </div>

        </div>
    </section>

@endsection

@push('scripts')
@endpush
