<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    public function addService(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:10|max:100',
            'description' => 'required|string|min:20|max:255',
            'price' => 'required|numeric|min:0',
            'photo' => 'required|image|mimes:jpg,jpeg,png,gif,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            Log::debug('ServiceController:addService - photo uploaded', ['hasFile' => true, 'originalName' => $request->file('photo')->getClientOriginalName()]);
            $path = $request->file('photo')->store('images/services', 'public');
            // Save the relative disk path in the DB (e.g. "images/services/xxxx.png").
            // The Service model accessor will expose a public URL for the frontend.
            $photoUrl = $path;
            Log::debug('ServiceController:addService - stored photo', ['path' => $path, 'photo_url' => $photoUrl]);
        }

        Service::create([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'price' => $request->get('price'),
            'photo_url' => $photoUrl,
        ]);
        return response()->json(['message' => 'Service added successfully'], 201);
    }

    public function getServices() {
        $services = Service::all();

        if ($services->isEmpty()) {
            return response()->json(['message' => 'No services found'], 404);
        }
        return response()->json($services, 200);
    }

    public function getServiceByName($name) {
        $service = Service::whereRaw('UPPER(name) = ?', [strtoupper($name)])->first();

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }
        return response()->json($service, 200);
    }

    public function updateServiceById(Request $request, $id) {
        $services = Service::find($id);

        if (!$services) {
            return response()->json(['message' => 'Service not found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|min:10|max:100',
            'description' => 'sometimes|required|string|min:20|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'photo' => 'sometimes|image|mimes:jpg,jpeg,png,gif,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('name')) {
            $services->name = $request->get('name');
        }
        if ($request->has('description')) {
            $services->description = $request->get('description');
        }
        if ($request->has('price')) {
            $services->price = $request->get('price');
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
            // Store relative path; the model accessor will return the public URL.
            $services->photo_url = $path;
            Log::debug('ServiceController:updateServiceById - stored photo', ['id' => $id, 'path' => $path, 'photo_url' => $services->photo_url]);
        }

        $services->update();
        return response()->json(['message' => 'Service updated successfully'], 200);
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
