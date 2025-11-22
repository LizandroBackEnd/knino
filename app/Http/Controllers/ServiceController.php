<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\enums\SizeEnum;

class ServiceController extends Controller
{
    public function addService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:100',
            'description' => 'required|string|min:10|max:255',
            'photo' => 'required|image|mimes:jpg,jpeg,png,gif,svg|max:5120',
            'price_by_size' => 'required|array',
        ]);

        $validator->after(function ($validator) use ($request) {
            $this->validatePriceBySize($request->input('price_by_size', []), $validator);
        });

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            Log::debug('ServiceController:addService - photo uploaded', ['hasFile' => true, 'originalName' => $request->file('photo')->getClientOriginalName()]);
            $path = $request->file('photo')->store('images/services', 'public');
            $photoUrl = $path;
            Log::debug('ServiceController:addService - stored photo', ['path' => $path, 'photo_url' => $photoUrl]);
        }

        $service = Service::create([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'price_by_size' => array_map(fn($v) => (float)$v, $request->input('price_by_size', [])),
            'photo_url' => $photoUrl,
        ]);

        return response()->json(['message' => 'Service added successfully', 'service' => $service], 201);
    }

    public function getServices() {
        $services = Service::all();
        return response()->json($services, 200);
    }

    public function getServiceByName($name) {
        // Support partial, case-insensitive search and return multiple matches
        $term = urldecode($name);
        $termLower = mb_strtolower($term, 'UTF-8');
        $services = Service::whereRaw('LOWER(name) LIKE ?', ["%{$termLower}%"])->get();
        return response()->json($services, 200);
    }

    public function updateServiceById(Request $request, $id) {
        $services = Service::find($id);

        if (!$services) {
            return response()->json(['message' => 'Service not found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|min:3|max:100',
            'description' => 'sometimes|required|string|min:10|max:255',
            'photo' => 'sometimes|image|mimes:jpg,jpeg,png,gif,svg|max:5120',
            'price_by_size' => 'sometimes|required|array',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->has('price_by_size')) {
                $this->validatePriceBySize($request->input('price_by_size', []), $validator);
            }
        });

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('name')) {
            $services->name = $request->get('name');
        }
        if ($request->has('description')) {
            $services->description = $request->get('description');
        }
        if ($request->has('price_by_size')) {
            $services->price_by_size = array_map(fn($v) => (float)$v, $request->input('price_by_size'));
        }
        if ($request->hasFile('photo')) {
            Log::debug('ServiceController:updateServiceById - photo uploaded', ['id' => $id, 'originalName' => $request->file('photo')->getClientOriginalName()]);
            if (!empty($services->photo_url)) {
                $oldPath = parse_url($services->photo_url, PHP_URL_PATH);
                if ($oldPath) {
                    $oldPath = ltrim($oldPath, '/');
                    if (str_starts_with($oldPath, 'storage/')) {
                        $oldPath = substr($oldPath, strlen('storage/'));
                    }
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('photo')->store('images/services', 'public');
            $services->photo_url = $path;
            Log::debug('ServiceController:updateServiceById - stored photo', ['id' => $id, 'path' => $path, 'photo_url' => $services->photo_url]);
        }

        $services->save();
        return response()->json(['message' => 'Service updated successfully', 'service' => $services], 200);
    }

    private function validatePriceBySize(array $prices, $validator): void
    {
        $sizes = SizeEnum::values();
        foreach ($sizes as $size) {
            if (! array_key_exists($size, $prices)) {
                $validator->errors()->add('price_by_size', "Falta precio para el tamaño {$size}.");
                continue;
            }
            if (! is_numeric($prices[$size]) || $prices[$size] < 0) {
                $validator->errors()->add('price_by_size', "Precio inválido para el tamaño {$size}.");
            }
        }
    }

    public function deleteServiceById($id) {
        $services = Service::find($id);

        if (!$services) {
            return response()->json(['message' => 'Service not found'], 404);
        }
        $services->delete();
        return response()->json(['message' => 'Service deleted successfully'], 200);
    }
}
