@extends('admin.layouts.app')

@section('title', 'Добавление жанра к фильму')
@section('header', 'Добавление жанра: ' . $film->name)

@section('content')
    <div class="card">
        <form action="{{ route('admin.films.categories.store', $film) }}" method="POST">
            @csrf

            <div class="card-body">
                <div class="form-group">
                    <label for="category_id">Жанр</label>
                    <select id="category_id" name="category_id"
                            class="form-control @error('category_id') is-invalid @enderror">
                        <option value="">— Выберите жанр —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Добавить</button>
                <a href="{{ route('admin.films.categories.index', $film) }}" class="btn btn-default">Отмена</a>
            </div>
        </form>
    </div>
@endsection
