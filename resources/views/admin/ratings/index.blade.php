@extends('admin.layouts.app')

@section('title', 'Оценки')
@section('header', 'Оценки')

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('admin.ratings.index') }}" method="GET" class="form-inline">
                <select name="film_id" class="form-control form-control-sm mr-2 mb-1">
                    <option value="">Все фильмы</option>
                    @foreach ($films as $film)
                        <option value="{{ $film->id }}" @selected($filmId == $film->id)>{{ $film->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-secondary btn-sm mr-2 mb-1">
                    <i class="fas fa-filter"></i> Применить
                </button>
                <a href="{{ route('admin.ratings.index') }}" class="btn btn-default btn-sm mb-1">Сбросить</a>

                <span class="ml-auto mb-1">
                    Оценок: <strong>{{ $total }}</strong>
                    @if ($total)
                        , средняя: <strong>{{ number_format($average, 2, ',', '') }}</strong>
                    @endif
                </span>
            </form>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th style="width: 60px">ID</th>
                    <th>Фильм</th>
                    <th>Пользователь</th>
                    <th>Оценка</th>
                    <th>Дата</th>
                    <th style="width: 100px">Действия</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($ratings as $rating)
                    <tr>
                        <td>{{ $rating->id }}</td>
                        <td>
                            <a href="{{ route('admin.films.ratings.index', $rating->film_id) }}">
                                {{ $rating->film?->name }}
                            </a>
                        </td>
                        <td>{{ $rating->user?->fio }}</td>
                        <td><span class="badge badge-primary">{{ $rating->ball }}</span></td>
                        <td>{{ $rating->created_at?->format('d.m.Y H:i') }}</td>
                        <td>
                            <form action="{{ route('admin.ratings.destroy', $rating) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Удалить оценку?')">
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
                        <td colspan="6" class="text-center text-muted">Оценки не найдены</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($ratings->hasPages())
            <div class="card-footer">
                {{ $ratings->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@endsection
