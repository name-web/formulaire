<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $query = Client::withCount('projects');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%");
        }

        $clients = $query->latest()->paginate(15)->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    public function show(Client $client): View
    {
        $client->load('projects');

        return view('admin.clients.show', compact('client'));
    }
}
