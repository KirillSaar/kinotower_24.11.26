@extends('admin.layouts.app')

@section('title', 'Главная')
@section('header', 'Главная')

@section('content')
    <div class="card">
        <div class="card-body">
            Добро пожаловать в админ-панель Kinotower, {{ auth('admin')->user()->name }}!
        </div>
    </div>
@endsection
