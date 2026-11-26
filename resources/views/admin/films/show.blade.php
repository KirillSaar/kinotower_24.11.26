@extends('admin.layouts.app')

@section('title', $film->name)
@section('header', $film->name)

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                @if ($film->link_img)
                    <div class="col-md-3 mb-3">
                        <img src="{{ $film->link_img }}" alt="{{ $film->name }}" class="img-fluid">
                    </div>
                @endif
                <div class="col">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Страна</dt>
                        <dd class="col-sm-9">{{ $film->country->name }}</dd>

                        <dt class="col-sm-3">Год выпуска</dt>
                        <dd class="col-sm-9">{{ $film->year_of_issue }}</dd>

                        <dt class="col-sm-3">Длительность</dt>
                        <dd class="col-sm-9">{{ $film->duration }} мин</dd>

                        <dt class="col-sm-3">Возраст</dt>
                        <dd class="col-sm-9">{{ $film->age }}+</dd>

                        <dt class="col-sm-3">Жанры</dt>
                        <dd class="col-sm-9">
                            @forelse ($film->categories as $category)
                                <span class="badge badge-info">{{ $category->name }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </dd>

                        <dt class="col-sm-3">Кинопоиск</dt>
                        <dd class="col-sm-9">
                            @if ($film->link_kinopoisk)
                                <a href="{{ $film->link_kinopoisk }}" target="_blank" rel="noopener">{{ $film->link_kinopoisk }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Видео</dt>
                        <dd class="col-sm-9">
                            <a href="{{ $film->link_video }}" target="_blank" rel="noopener">{{ $film->link_video }}</a>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.films.edit', $film) }}" class="btn btn-warning">Изменить</a>
            <a href="{{ route('admin.films.categories.index', $film) }}" class="btn btn-secondary">Жанры фильма</a>
            <a href="{{ route('admin.films.index') }}" class="btn btn-default">К списку</a>
        </div>
    </div>
@endsection
