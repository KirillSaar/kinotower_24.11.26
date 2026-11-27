@extends('admin.layouts.app')

@section('title', 'Пользователи')
@section('header', 'Пользователи')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="btn-group">
                <a href="{{ route('admin.users.index', ['status' => 'active']) }}"
                   class="btn btn-sm {{ $status === 'active' ? 'btn-primary' : 'btn-default' }}">Активные</a>
                <a href="{{ route('admin.users.index', ['status' => 'deleted']) }}"
                   class="btn btn-sm {{ $status === 'deleted' ? 'btn-primary' : 'btn-default' }}">Удалённые</a>
                <a href="{{ route('admin.users.index', ['status' => 'all']) }}"
                   class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-default' }}">Все</a>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th style="width: 60px">ID</th>
                    <th>ФИО</th>
                    <th>Email</th>
                    <th>Дата рождения</th>
                    <th>Пол</th>
                    <th>Регистрация</th>
                    <th>Статус</th>
                    <th style="width: 120px">Действия</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->fio }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->birthday?->format('d.m.Y') ?? '—' }}</td>
                        <td>{{ $user->gender->name }}</td>
                        <td>{{ $user->created_at?->format('d.m.Y H:i') }}</td>
                        <td>
                            @if ($user->trashed())
                                <span class="badge badge-danger">Удалён {{ $user->deleted_at->format('d.m.Y') }}</span>
                            @else
                                <span class="badge badge-success">Активен</span>
                            @endif
                        </td>
                        <td>
                            @if ($user->trashed())
                                <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Восстановить пользователя?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm" title="Восстановить">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Удалить пользователя?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Пользователи не найдены</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-footer">
                {{ $users->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@endsection
