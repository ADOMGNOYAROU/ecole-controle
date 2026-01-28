<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presence;

class PresenceController extends Controller
{
    public function index()
    {
        return response()->json(Presence::with('eleve')->get());
    }

    public function store(Request $request)
    {
        $presence = Presence::create($request->all());
        return response()->json($presence, 201);
    }

    public function storeBulk(Request $request)
    {
        $presences = $request->all();
        foreach ($presences as $presenceData) {
            Presence::create($presenceData);
        }
        return response()->json(['message' => 'Présences enregistrées'], 201);
    }

    public function show($id)
    {
        $presence = Presence::with('eleve')->find($id);
        if (!$presence) {
            return response()->json(['message' => 'Présence non trouvée'], 404);
        }
        return response()->json($presence);
    }

    public function update(Request $request, $id)
    {
        $presence = Presence::find($id);
        if (!$presence) {
            return response()->json(['message' => 'Présence non trouvée'], 404);
        }
        $presence->update($request->all());
        return response()->json($presence);
    }

    public function destroy($id)
    {
        $presence = Presence::find($id);
        if (!$presence) {
            return response()->json(['message' => 'Présence non trouvée'], 404);
        }
        $presence->delete();
        return response()->json(['message' => 'Présence supprimée']);
    }

    public function getByClasse($classeId)
    {
        $presences = Presence::whereHas('eleve', function($query) use ($classeId) {
            $query->where('classe_id', $classeId);
        })->with('eleve')->get();
        return response()->json($presences);
    }

    public function getByEleve($eleveId)
    {
        $presences = Presence::where('eleve_id', $eleveId)->with('eleve')->get();
        return response()->json($presences);
    }
}
