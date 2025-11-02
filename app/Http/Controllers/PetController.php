<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PetController extends Controller
{
    public function addPet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'birth_date' => 'required|date',
            'color' => 'required|string',
            'photo_url' => 'required|url',
            'species' => 'required|in:perro,gato',
            'breed_id' => 'required|exists:breeds,id',
            'client_id' => 'required|exists:clients,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Pet::create([
            'name' => $request->get('name'),
            'birth_date' => $request->get('birth_date'),
            'color' => $request->get('color'),
            'photo_url' => $request->get('photo_url'),
            'species' => $request->get('species'),
            'breed_id' => $request->get('breed_id'),
            'client_id' => $request->get('client_id'),

        ]);

        return response()->json(['message' => 'Pet added successfully'], 201);
    }

    public function getPets()
    {
        $pets = Pet::all();

        if ($pets->isEmpty()) {
            return response()->json(['message' => 'No pets found'], 404);
        }
        return response()->json($pets, 200);
    }

    public function getPetByName($name)
    {
        $pet = Pet::whereRaw('UPPER(name) = ?', [strtoupper($name)])->first();

        if (!$pet) {
            return response()->json(['message' => 'Pet not found'], 404);
        }
        return response()->json($pet, 200);
    }

    public function updatePetById(Request $request, $id)
    {
        $pets = Pet::find($id);

        if (!$pets) {
            return response()->json(['message' => 'Pet not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string',
            'birth_date' => 'sometimes|required|date',
            'color' => 'sometimes|required|string',
            'photo_url' => 'sometimes|required|url',
            'species' => 'sometimes|required|in:perro,gato',
            'breed_id' => 'sometimes|required|exists:breeds,id',
            'client_id' => 'sometimes|required|exists:clients,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('name')) {
            $pets->name =  $request->get('name');
        }
        if ($request->has('birth_date')) {
            $pets->birth_date =  $request->get('birth_date');
        }
        if ($request->has('color')) {
            $pets->color =  $request->get('color');
        }
        if ($request->has('photo_url')) {
            $pets->photo_url =  $request->get('photo_url');
        }
        if ($request->has('species')) {
            $pets->species =  $request->get('species');
        }
        if ($request->has('breed_id')) {
            $pets->breed_id =  $request->get('breed_id');
        }
        if ($request->has('client_id')) {
            $pets->client_id =  $request->get('client_id');
        }

    // guardar cambios en el modelo
    $pets->save();
    return response()->json(['message' => 'Pet updated successfully'], 200);

    }

    public function deletePetById($id) {
        $pet = Pet::find($id);

        if (!$pet) {
            return response()->json(['message' => 'Pet not found'], 404);
        }

        $pet->delete();
        return response()->json(['message' => 'Pet deleted successfully'], 200);
    }
}
