@extends('admin.layouts.app')

@section('title', 'Фильмы')
@section('header', 'Фильмы')

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('admin.films.index') }}" method="GET" class="form-inline">
                <select name="country_id" class="form-control form-control-sm mr-2 mb-1">
                    <option value="">Все страны</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}" @selected(request('country_id') == $country->id)>
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>

                <select name="category_id" class="form-control form-control-sm mr-2 mb-1">
                    <option value="">Все жанры</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-secondary btn-sm mr-2 mb-1">
                    <i class="fas fa-filter"></i> Применить
                </button>
                <a href="{{ route('admin.films.index') }}" class="btn btn-default btn-sm mr-2 mb-1">Сбросить</a>

                <a href="{{ route('admin.films.create') }}" class="btn btn-primary btn-sm ml-auto mb-1">
                    <i class="fas fa-plus"></i> Добавить фильм
                </a>
            </form>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th style="width: 60px">ID</th>
                    <th>Название</th>
                    <th>Страна</th>
                    <th>Жанры</th>
                    <th>Год</th>
                    <th>Длит.</th>
                    <th>Возраст</th>
                    <th style="width: 220px">Действия</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($films as $film)
                    <tr>
                        <td>{{ $film->id }}</td>
                        <td>{{ $film->name }}</td>
                        <td>{{ $film->country->name }}</td>
                        <td>
                            @forelse ($film->categories as $category)
                                <span class="badge badge-info">{{ $category->name }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        <td>{{ $film->year_of_issue }}</td>
                        <td>{{ $film->duration }} мин</td>
                        <td>{{ $film->age }}+</td>
                        <td>
                            <a href="{{ route('admin.films.show', $film) }}" class="btn btn-info btn-sm" title="Просмотр">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.films.categories.index', $film) }}" class="btn btn-secondary btn-sm" title="Жанры">
                                <i class="fas fa-tags"></i>
                            </a>
                            <a href="{{ route('admin.films.edit', $film) }}" class="btn btn-warning btn-sm" title="Изменить">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.films.destroy', $film) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Удалить фильм?')">
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
                        <td colspan="8" class="text-center text-muted">Фильмы не найдены</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($films->hasPages())
            <div class="card-footer">
                {{ $films->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@endsection
