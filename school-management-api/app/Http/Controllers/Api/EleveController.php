<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Eleve;

class EleveController extends Controller
{
    public function index()
    {
        return response()->json(Eleve::with('classe')->get());
    }

    public function store(Request $request)
    {
        $eleve = Eleve::create($request->all());
        return response()->json($eleve, 201);
    }

    public function show($id)
    {
        $eleve = Eleve::with('classe')->find($id);
        if (!$eleve) {
            return response()->json(['message' => 'Élève non trouvé'], 404);
        }
        return response()->json($eleve);
    }

    public function update(Request $request, $id)
    {
        $eleve = Eleve::find($id);
        if (!$eleve) {
            return response()->json(['message' => 'Élève non trouvé'], 404);
        }
        $eleve->update($request->all());
        return response()->json($eleve);
    }

    public function destroy($id)
    {
        $eleve = Eleve::find($id);
        if (!$eleve) {
            return response()->json(['message' => 'Élève non trouvé'], 404);
        }
        $eleve->delete();
        return response()->json(['message' => 'Élève supprimé']);
    }
}
