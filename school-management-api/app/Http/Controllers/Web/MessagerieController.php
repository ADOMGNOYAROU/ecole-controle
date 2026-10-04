<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use App\Services\MessagerieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MessagerieController extends Controller
{
    public function __construct(private readonly MessagerieService $messagerie) {}

    public function index(): View
    {
        $user = Auth::user();

        $conversations = $this->messagerie->interlocuteursPossibles($user)
            ->map(function (User $interlocuteur) use ($user) {
                $dernierMessage = Message::query()
                    ->where(fn ($q) => $q->where('expediteur_id', $user->id)->where('destinataire_id', $interlocuteur->id))
                    ->orWhere(fn ($q) => $q->where('expediteur_id', $interlocuteur->id)->where('destinataire_id', $user->id))
                    ->latest()
                    ->first();

                $nonLus = Message::query()
                    ->where('expediteur_id', $interlocuteur->id)
                    ->where('destinataire_id', $user->id)
                    ->where('lu', false)
                    ->count();

                return [
                    'interlocuteur' => $interlocuteur,
                    'dernier_message' => $dernierMessage,
                    'non_lus' => $nonLus,
                ];
            })
            ->sortByDesc(fn ($c) => $c['dernier_message']?->created_at ?? Carbon::createFromTimestamp(0))
            ->values();

        return view('messagerie.index', compact('conversations'));
    }

    public function show(User $utilisateur): View
    {
        $user = Auth::user();

        abort_unless($this->messagerie->peuventEchanger($user, $utilisateur), 403, 'Vous ne pouvez pas contacter cet utilisateur.');

        $messages = Message::query()
            ->where(fn ($q) => $q->where('expediteur_id', $user->id)->where('destinataire_id', $utilisateur->id))
            ->orWhere(fn ($q) => $q->where('expediteur_id', $utilisateur->id)->where('destinataire_id', $user->id))
            ->oldest()
            ->get();

        Message::query()
            ->where('expediteur_id', $utilisateur->id)
            ->where('destinataire_id', $user->id)
            ->where('lu', false)
            ->update(['lu' => true]);

        return view('messagerie.show', ['interlocuteur' => $utilisateur, 'messages' => $messages]);
    }

    public function store(User $utilisateur): RedirectResponse
    {
        $user = Auth::user();

        abort_unless($this->messagerie->peuventEchanger($user, $utilisateur), 403, 'Vous ne pouvez pas contacter cet utilisateur.');

        $donnees = request()->validate([
            'contenu' => 'required|string|max:2000',
        ]);

        $message = Message::create([
            'expediteur_id' => $user->id,
            'destinataire_id' => $utilisateur->id,
            'contenu' => $donnees['contenu'],
        ]);

        Notification::create([
            'user_id' => $utilisateur->id,
            'titre' => 'Nouveau message de '.$user->name,
            'message' => Str::limit($message->contenu, 100),
            'type' => 'message',
            'lien' => route('messagerie.show', $user),
        ]);

        return redirect()->route('messagerie.show', $utilisateur);
    }
}
