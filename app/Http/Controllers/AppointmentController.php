<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Employees;
use App\Models\EmployeeSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use InvalidArgumentException;

class AppointmentController extends Controller
{

    public function availableVeterinarians(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $scheduledAt = Carbon::parse($request->input('scheduled_at'));
        $day = $scheduledAt->dayOfWeek;
        $time = $scheduledAt->format('H:i:s');

        $vetsQuery = Employees::query()
            ->whereRaw('LOWER(role) = ?', ['veterinario'])
            ->whereHas('schedules', function ($q) use ($day, $time) {
                $q->where('active', true)
                    ->where('day_of_week', $day)
                    ->where('start_time', '<=', $time)
                    ->where('end_time', '>', $time);
            })
            ->whereDoesntHave('appointments', function ($q) use ($scheduledAt) {
                $q->where('scheduled_at', $scheduledAt);
            });

        $vets = $vetsQuery->get();
        return response()->json($vets);
    }

    public function scheduleAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pet_id' => 'required|exists:pets,id',
            'service_id' => 'required|exists:services,id',
            'employee_id' => 'nullable|exists:users,id',
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string',
            'size' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $pet = Pet::findOrFail($data['pet_id']);
        $service = Service::findOrFail($data['service_id']);

        $size = $data['size'] ?? $pet->size ?? null;
        if ($size === null) {
            return response()->json(['error' => 'El tamaño de la mascota no está definido.'], 422);
        }

        try {
            $price = $service->getPriceForSize($size);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $scheduledAt = Carbon::parse($data['scheduled_at']);

        if (! empty($data['employee_id'])) {
            $emp = Employees::findOrFail($data['employee_id']);
            if (! $emp->isVeterinarian()) {
                return response()->json(['error' => 'Empleado no es veterinario'], 422);
            }

            $day = $scheduledAt->dayOfWeek;
            $time = $scheduledAt->format('H:i:s');

            $hasSchedule = $emp->schedules()
                ->where('active', true)
                ->where('day_of_week', $day)
                ->where('start_time', '<=', $time)
                ->where('end_time', '>', $time)
                ->exists();

            if (! $hasSchedule) {
                return response()->json(['error' => 'Veterinario no trabaja en esa fecha/hora'], 422);
            }

            $conflict = Appointment::where('employee_id', $emp->id)
                ->where('scheduled_at', $scheduledAt)
                ->exists();

            if ($conflict) {
                return response()->json(['error' => 'Veterinario no disponible en esa hora'], 422);
            }
        }

        $appointment = Appointment::create([
            'pet_id' => $pet->id,
            'client_id' => $pet->client_id,
            'service_id' => $service->id,
            'employee_id' => $data['employee_id'] ?? null,
            'size' => $size,
            'price' => $price,
            'scheduled_at' => $scheduledAt,
            'notes' => $data['notes'] ?? null,
            'status' => 'scheduled',
        ]);

        return response()->json($appointment, 201);
    }

    public function completeAppointment($id)
    {
        $appointment = Appointment::find($id);
        if (! $appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }
        if ($appointment->status === 'canceled') {
            return response()->json(['error' => 'Cannot complete a canceled appointment'], 422);
        }
        $appointment->status = 'completed';
        $appointment->save();
        return response()->json($appointment, 200);
    }

    public function rescheduleAppointment(Request $request, $id)
    {
        $appointment = Appointment::find($id);
        if (! $appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'required|date|after:now',
            'employee_id' => 'nullable|exists:users,id',
            'size' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $scheduledAt = Carbon::parse($data['scheduled_at']);
        $size = $data['size'] ?? $appointment->size;

        if ($size !== $appointment->size) {
            $service = Service::findOrFail($appointment->service_id);
            try {
                $price = $service->getPriceForSize($size);
            } catch (InvalidArgumentException $e) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            $appointment->price = $price;
            $appointment->size = $size;
        }

        if (! empty($data['employee_id'])) {
            $emp = Employees::findOrFail($data['employee_id']);
            if (! $emp->isVeterinarian()) {
                return response()->json(['error' => 'Empleado no es veterinario'], 422);
            }
            $day = $scheduledAt->dayOfWeek;
            $time = $scheduledAt->format('H:i:s');
            $hasSchedule = $emp->schedules()
                ->where('active', true)
                ->where('day_of_week', $day)
                ->where('start_time', '<=', $time)
                ->where('end_time', '>', $time)
                ->exists();
            if (! $hasSchedule) {
                return response()->json(['error' => 'Veterinario no trabaja en esa fecha/hora'], 422);
            }
            $conflict = Appointment::where('employee_id', $emp->id)
                ->where('scheduled_at', $scheduledAt)
                ->where('id', '!=', $appointment->id)
                ->exists();
            if ($conflict) {
                return response()->json(['error' => 'Veterinario no disponible en esa hora'], 422);
            }
            $appointment->employee_id = $emp->id;
        }

        $appointment->scheduled_at = $scheduledAt;
        $appointment->status = 'scheduled';
        $appointment->save();
        return response()->json($appointment, 200);
    }

    public function cancelAppointment($id)
    {
        $appointment = Appointment::find($id);
        if (! $appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }
        $appointment->status = 'canceled';
        $appointment->save();
        return response()->json(['message' => 'Appointment canceled', 'appointment' => $appointment], 200);
    }
}
