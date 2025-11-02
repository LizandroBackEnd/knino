<?php

namespace App\Http\Controllers;

use App\Models\Breed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BreedController extends Controller
{
    public function addBreed(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:breeds,name',
            'species' => 'required|string|in:perro,gato'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Breed::create([
            'name' => $request->name,
            'species'  => $request->species
        ]);
        return response()->json(['message' => 'Breed added successfully'], 201);
    }

    public function getBreedBySpecies($species) {
        $breeds = Breed::where('species', $species)->get();
        
        if (!$breeds) {
            return response()->json(['message' => 'Breed not found'], 404);

        }
        return response()->json($breeds, 200);
    }
}
