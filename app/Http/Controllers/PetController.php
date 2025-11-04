<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\enums\SexEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PetController extends Controller
{
    public function addPet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'birth_date' => 'required|date',
            'color' => 'required|string',
            'photo' => 'required|image|mimes:jpg,jpeg,png,gif,svg|max:5120',
            'species' => 'required|in:' . implode(',', \App\Models\enums\SpeciesEnum::values()),
            'sex' => 'required|in:' . implode(',', SexEnum::values()),
            'breed_id' => 'required|exists:breeds,id',
            'client_id' => 'required|exists:clients,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            Log::debug('PetController:addPet - photo uploaded', ['originalName' => $request->file('photo')->getClientOriginalName()]);
            $photoPath = $request->file('photo')->store('images/pets', 'public');
            Log::debug('PetController:addPet - stored photo', ['path' => $photoPath]);
        }

        Pet::create([
            'name' => $request->get('name'),
            'birth_date' => $request->get('birth_date'),
            'color' => $request->get('color'),
            'photo_url' => $photoPath,
            'species' => $request->get('species'),
            'sex' => $request->get('sex'),
            'breed_id' => $request->get('breed_id'),
            'client_id' => $request->get('client_id'),

        ]);

        return response()->json(['message' => 'Pet added successfully'], 201);
    }

    public function getPets()
    {
        $pets = Pet::with(['breed', 'client'])->get();

        if ($pets->isEmpty()) {
            return response()->json(['message' => 'No pets found'], 404);
        }
        return response()->json($pets, 200);
    }

    public function getPetByName($name)
    {
        $pet = Pet::with(['breed', 'client'])->whereRaw('UPPER(name) = ?', [strtoupper($name)])->first();

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
            'photo' => 'sometimes|image|mimes:jpg,jpeg,png,gif,svg|max:5120',
            'species' => 'sometimes|required|in:' . implode(',', \App\Models\enums\SpeciesEnum::values()),
            'sex' => 'sometimes|required|in:' . implode(',', SexEnum::values()),
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
        if ($request->hasFile('photo')) {
            Log::debug('PetController:updatePetById - photo uploaded', ['id' => $id, 'originalName' => $request->file('photo')->getClientOriginalName()]);
            if (!empty($pets->photo_url)) {
                $oldPath = parse_url($pets->photo_url, PHP_URL_PATH);
                if ($oldPath) {
                    $oldPath = ltrim($oldPath, '/');
                    if (str_starts_with($oldPath, 'storage/')) {
                        $oldPath = substr($oldPath, strlen('storage/'));
                    }
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('photo')->store('images/pets', 'public');
            $pets->photo_url = $path;
            Log::debug('PetController:updatePetById - stored photo', ['id' => $id, 'path' => $path]);
        }
        if ($request->has('species')) {
            $pets->species =  $request->get('species');
        }
        if ($request->has('sex')) {
            $pets->sex = $request->get('sex');
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
