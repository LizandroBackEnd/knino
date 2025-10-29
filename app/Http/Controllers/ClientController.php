<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function addClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:5|max:20',
            'last_name_primary' => 'required|string|min:5|max:20',
            'last_name_secondary' => 'required|string|min:5|max:20',
            'phone' => 'required|string|min:10|max:10',
            'email' => 'required|string|min:5|max:20',
            'address' => 'required|string|min:10|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Client::create([
            'name' => $request->name,
            'last_name_primary' => $request->last_name_primary,
            'last_name_secondary' => $request->last_name_secondary,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);
        return response()->json(['message' => 'Client added successfully'], 201);
    }

    public function getClients()
    {
        $clients = Client::all();

        if (!$clients) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
        return response()->json($clients, 200);
    }

    public function getClientByEmail($email)
    {
        $client = Client::where('email', $email)->first();

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }
        return response()->json($client, 200);
    }

    public function updateClientById(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|min:5|max:20',
            'last_name_primary' => 'sometimes|required|string|min:5|max:20',
            'last_name_secondary' => 'sometimes|required|string|min:5|max:20',
            'phone' => 'sometimes|required|string|min:10|max:10',
            'email' => 'sometimes|required|string|min:5|max:20|unique:users,email,' . $id,
            'address' => 'sometimes|required|string|min:10|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('name')) {
            $client->name = $request->get('name');
        }
        if ($request->has('last_name_primary')) {
            $client->last_name_primary = $request->get('last_name_primary');
        }
        if ($request->has('last_name_secondary')) {
            $client->last_name_secondary = $request->get('last_name_secondary');
        }
        if ($request->has('phone')) {
            $client->phone = $request->get('phone');
        }
        if ($request->has('email')) {
            $client->email = $request->get('email');
        }
        if ($request->has('address')) {
            $client->address = $request->get('address');
        }

        $client->update();
        return response()->json(['message' => 'Client updated successfully'], 200);
    }

    public function deleteClientById($id) {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }
        $client->delete();
        return response()->json(['message' => 'Client deleted successfully'], 200);
    }
}
