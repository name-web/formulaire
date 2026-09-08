<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientClientController extends Controller
{
    public function index(Request $request): View
    {
        $query = Client::clients()
            ->withCount('projects')
            ->whereHas('projects', fn ($q) => $q->where('status', 'submitted'));

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('name LIKE ? ESCAPE ?', ["%{$search}%", '\\'])
                    ->orWhereRaw('company_name LIKE ? ESCAPE ?', ["%{$search}%", '\\']);
            });
        }

        $clients = $query->latest()->paginate(10)->withQueryString();

        return view('admin.client-clients.index', compact('clients'));
    }

    public function show(Client $client): View
    {
        $client->load(['projects', 'comments.user']);

        return view('admin.client-clients.show', compact('client'));
    }
}
