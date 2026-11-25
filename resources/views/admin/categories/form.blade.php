@extends('admin.layouts.app')

@php($isEdit = $category->exists)

@section('title', $isEdit ? 'Изменение жанра' : 'Новый жанр')
@section('header', $isEdit ? 'Изменение жанра' : 'Новый жанр')

@section('content')
    <div class="card">
        <form action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
              method="POST">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="card-body">
                <div class="form-group">
                    <label for="name">Название</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $category->name) }}"
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="parent_id">Родительский жанр</label>
                    <select id="parent_id" name="parent_id" class="form-control @error('parent_id') is-invalid @enderror">
                        <option value="">— Нет —</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}"
                                @selected(old('parent_id', $category->parent_id) == $parent->id)>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Сохранить' : 'Добавить' }}</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-default">Отмена</a>
            </div>
        </form>
    </div>
@endsection
