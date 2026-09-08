<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(Request $request): View
    {
        $query = ClientComment::with(['client', 'user']);

        if ($request->filled('search')) {
            $query->whereHas('client', fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'));
        }

        if ($request->filled('type')) {
            $type = $request->type;
            $query->whereHas('client', fn ($q) => $q->where('status', $type));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $comments = $query->latest()->paginate(15)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('admin.comments.index', compact('comments', 'users'));
    }
}
