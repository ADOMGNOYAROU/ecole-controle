<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;

class ClasseController extends Controller
{
    public function index()
    {
        return response()->json(Classe::all());
    }

    public function store(Request $request)
    {
        $classe = Classe::create($request->all());
        return response()->json($classe, 201);
    }

    public function show($id)
    {
        $classe = Classe::find($id);
        if (!$classe) {
            return response()->json(['message' => 'Classe non trouvée'], 404);
        }
        return response()->json($classe);
    }

    public function update(Request $request, $id)
    {
        $classe = Classe::find($id);
        if (!$classe) {
            return response()->json(['message' => 'Classe non trouvée'], 404);
        }
        $classe->update($request->all());
        return response()->json($classe);
    }

    public function destroy($id)
    {
        $classe = Classe::find($id);
        if (!$classe) {
            return response()->json(['message' => 'Classe non trouvée'], 404);
        }
        $classe->delete();
        return response()->json(['message' => 'Classe supprimée']);
    }

    public function getEleves($id)
    {
        $classe = Classe::find($id);
        if (!$classe) {
            return response()->json(['message' => 'Classe non trouvée'], 404);
        }
        $eleves = $classe->eleves()->get(['id', 'nom', 'prenom']);
        return response()->json($eleves);
    }
}
