@extends('admin.layouts.app')

@section('title', 'Жанры фильма')
@section('header', 'Жанры фильма: ' . $film->name)

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.films.categories.create', $film) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Добавить жанр
            </a>
            <a href="{{ route('admin.films.index') }}" class="btn btn-default btn-sm">К списку фильмов</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th style="width: 80px">ID</th>
                    <th>Название</th>
                    <th>Родительский жанр</th>
                    <th style="width: 120px">Действия</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->parent?->name ?? '—' }}</td>
                        <td>
                            <form action="{{ route('admin.films.categories.destroy', [$film, $category]) }}" method="POST"
                                  onsubmit="return confirm('Убрать жанр у фильма?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">У фильма пока нет жанров</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
