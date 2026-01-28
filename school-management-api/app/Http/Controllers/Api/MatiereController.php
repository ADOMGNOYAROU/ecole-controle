<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Matiere;

class MatiereController extends Controller
{
    public function index()
    {
        return response()->json(Matiere::all());
    }

    public function store(Request $request)
    {
        $matiere = Matiere::create($request->all());
        return response()->json($matiere, 201);
    }

    public function show($id)
    {
        $matiere = Matiere::find($id);
        if (!$matiere) {
            return response()->json(['message' => 'Matière non trouvée'], 404);
        }
        return response()->json($matiere);
    }

    public function update(Request $request, $id)
    {
        $matiere = Matiere::find($id);
        if (!$matiere) {
            return response()->json(['message' => 'Matière non trouvée'], 404);
        }
        $matiere->update($request->all());
        return response()->json($matiere);
    }

    public function destroy($id)
    {
        $matiere = Matiere::find($id);
        if (!$matiere) {
            return response()->json(['message' => 'Matière non trouvée'], 404);
        }
        $matiere->delete();
        return response()->json(['message' => 'Matière supprimée']);
    }
}
