@extends('admin.layouts.app')

@php($isEdit = $film->exists)

@section('title', $isEdit ? 'Изменение фильма' : 'Новый фильм')
@section('header', $isEdit ? 'Изменение фильма' : 'Новый фильм')

@section('content')
    <div class="card">
        <form action="{{ $isEdit ? route('admin.films.update', $film) : route('admin.films.store') }}" method="POST">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="card-body">
                <div class="form-group">
                    <label for="name">Название</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $film->name) }}"
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="country_id">Страна</label>
                    <select id="country_id" name="country_id"
                            class="form-control @error('country_id') is-invalid @enderror">
                        <option value="">— Выберите страну —</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}"
                                @selected(old('country_id', $film->country_id) == $country->id)>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('country_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="duration">Длительность (мин)</label>
                        <input type="number" id="duration" name="duration" min="1"
                               value="{{ old('duration', $film->duration) }}"
                               class="form-control @error('duration') is-invalid @enderror">
                        @error('duration')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="year_of_issue">Год выпуска</label>
                        <input type="number" id="year_of_issue" name="year_of_issue" min="1901" max="2155"
                               value="{{ old('year_of_issue', $film->year_of_issue) }}"
                               class="form-control @error('year_of_issue') is-invalid @enderror">
                        @error('year_of_issue')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="age">Возраст</label>
                        <select id="age" name="age" class="form-control @error('age') is-invalid @enderror">
                            <option value="">— Выберите —</option>
                            @foreach ($ages as $age)
                                <option value="{{ $age }}" @selected((string) old('age', $film->age) === (string) $age)>
                                    {{ $age }}+
                                </option>
                            @endforeach
                        </select>
                        @error('age')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="link_img">Ссылка на постер</label>
                    <input type="text" id="link_img" name="link_img" value="{{ old('link_img', $film->link_img) }}"
                           class="form-control @error('link_img') is-invalid @enderror">
                    @error('link_img')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="link_kinopoisk">Ссылка на Кинопоиск</label>
                    <input type="text" id="link_kinopoisk" name="link_kinopoisk"
                           value="{{ old('link_kinopoisk', $film->link_kinopoisk) }}"
                           class="form-control @error('link_kinopoisk') is-invalid @enderror">
                    @error('link_kinopoisk')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="link_video">Ссылка на видео</label>
                    <input type="text" id="link_video" name="link_video" value="{{ old('link_video', $film->link_video) }}"
                           class="form-control @error('link_video') is-invalid @enderror">
                    @error('link_video')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Сохранить' : 'Добавить' }}</button>
                <a href="{{ route('admin.films.index') }}" class="btn btn-default">Отмена</a>
            </div>
        </form>
    </div>
@endsection
