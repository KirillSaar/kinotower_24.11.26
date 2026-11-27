<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'active');

        $query = User::with('gender');

        if ($status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        } else {
            $status = 'active';
        }

        $users = $query->orderBy('id')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'status'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return back()->with('success', 'Пользователь удалён.');
    }

    public function restore(User $user): RedirectResponse
    {
        $user->restore();

        return back()->with('success', 'Пользователь восстановлен.');
    }
}
