<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientCommentController extends Controller
{
    public function store(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $client->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Commentaire ajouté.');
    }

    public function destroy(Client $client, ClientComment $comment): RedirectResponse
    {
        abort_unless($comment->client_id === $client->id, 404);

        if ($comment->user_id !== auth()->id()) {
            abort(403, 'Vous ne pouvez supprimer que vos propres commentaires.');
        }

        $comment->delete();

        return back()->with('success', 'Commentaire supprimé.');
    }
}
