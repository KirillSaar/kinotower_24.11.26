@extends('admin.layouts.app')

@php($isEdit = $country->exists)

@section('title', $isEdit ? 'Изменение страны' : 'Новая страна')
@section('header', $isEdit ? 'Изменение страны' : 'Новая страна')

@section('content')
    <div class="card">
        <form action="{{ $isEdit ? route('admin.countries.update', $country) : route('admin.countries.store') }}"
              method="POST">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="card-body">
                <div class="form-group">
                    <label for="name">Название</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $country->name) }}"
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Сохранить' : 'Добавить' }}</button>
                <a href="{{ route('admin.countries.index') }}" class="btn btn-default">Отмена</a>
            </div>
        </form>
    </div>
@endsection
