@extends('admin.layouts.app')

@section('title', 'Отзывы')
@section('header', 'Отзывы')

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('admin.reviews.index') }}" method="GET" class="form-inline">
                <select name="film_id" class="form-control form-control-sm mr-2 mb-1">
                    <option value="">Все фильмы</option>
                    @foreach ($films as $film)
                        <option value="{{ $film->id }}" @selected($filmId == $film->id)>{{ $film->name }}</option>
                    @endforeach
                </select>

                <select name="status" class="form-control form-control-sm mr-2 mb-1">
                    <option value="all" @selected($status === 'all')>Все отзывы</option>
                    <option value="pending" @selected($status === 'pending')>Не одобренные</option>
                    <option value="approved" @selected($status === 'approved')>Одобренные</option>
                </select>

                <button type="submit" class="btn btn-secondary btn-sm mr-2 mb-1">
                    <i class="fas fa-filter"></i> Применить
                </button>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-default btn-sm mb-1">Сбросить</a>
            </form>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th style="width: 60px">ID</th>
                    <th>Фильм</th>
                    <th>Пользователь</th>
                    <th>Отзыв</th>
                    <th>Дата</th>
                    <th>Статус</th>
                    <th style="width: 120px">Действия</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($reviews as $review)
                    <tr>
                        <td>{{ $review->id }}</td>
                        <td>
                            <a href="{{ route('admin.films.reviews.index', $review->film_id) }}">
                                {{ $review->film?->name }}
                            </a>
                        </td>
                        <td>{{ $review->user?->fio }}</td>
                        <td style="white-space: pre-line">{{ $review->message }}</td>
                        <td>{{ $review->created_at?->format('d.m.Y H:i') }}</td>
                        <td>
                            @if ($review->is_approved)
                                <span class="badge badge-success">Одобрен</span>
                            @else
                                <span class="badge badge-warning">Ожидает</span>
                            @endif
                        </td>
                        <td>
                            @unless ($review->is_approved)
                                <form action="{{ route('admin.reviews.approved', $review) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm" title="Одобрить">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            @endunless
                            <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Удалить отзыв?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Удалить">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Отзывы не найдены</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($reviews->hasPages())
            <div class="card-footer">
                {{ $reviews->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@endsection
