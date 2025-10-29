<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function addService(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:10|max:100',
            'description' => 'required|string|min:20|max:255',
            'price' => 'required|numeric|min:0',
            'photo_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Service::create([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'price' => $request->get('price'),
            'photo_url' => $request->get('photo_url'),
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

    public function getServiceById($id) {
        $service = Service::find($id);

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
            'photo_url' => 'sometimes|required|url',
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
        if ($request->has('photo_url')) {
            $services->photo_url = $request->get('photo_url');
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
