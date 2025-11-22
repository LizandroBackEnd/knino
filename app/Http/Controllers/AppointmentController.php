<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Employees;
use App\Models\EmployeeSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use InvalidArgumentException;
use App\Models\enums\StatusEnum;

class AppointmentController extends Controller
{

    public function availableVeterinarians(Request $request)
    {
        // Accept either scheduled_at (datetime) or scheduled_date + scheduled_time
        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'sometimes|date',
            'scheduled_date' => 'sometimes|date',
            'scheduled_time' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->filled('scheduled_at')) {
            $scheduledAt = Carbon::parse($request->input('scheduled_at'));
        } elseif ($request->filled('scheduled_date') && $request->filled('scheduled_time')) {
            $scheduledAt = Carbon::parse($request->input('scheduled_date') . ' ' . $request->input('scheduled_time'));
        } else {
            return response()->json(['error' => 'scheduled_at or scheduled_date+scheduled_time is required'], 422);
        }
        $day = $scheduledAt->dayOfWeek;
        $time = $scheduledAt->format('H:i:s');

        $vetsQuery = Employees::query()
            ->whereRaw('LOWER(role) = ?', ['veterinarian'])
            ->whereHas('schedules', function ($q) use ($day, $time) {
                // schedules now use day_of_week_start and day_of_week_end (inclusive range)
                $q->where('active', true)
                    ->where(function($sq) use ($day) {
                        // non-wrapping ranges: start <= end
                        $sq->where(function($r) use ($day) {
                            $r->whereColumn('day_of_week_start', '<=', 'day_of_week_end')
                              ->where('day_of_week_start', '<=', $day)
                              ->where('day_of_week_end', '>=', $day);
                        })
                        // wrapping ranges: start > end (e.g., Thu -> Mon)
                        ->orWhere(function($r2) use ($day) {
                            $r2->whereColumn('day_of_week_start', '>', 'day_of_week_end')
                               ->where(function($a) use ($day) {
                                   $a->where('day_of_week_start', '<=', $day)
                                     ->orWhere('day_of_week_end', '>=', $day);
                               });
                        });
                    })
                    ->where('start_time', '<=', $time)
                    ->where('end_time', '>', $time);
            })
            ->whereDoesntHave('appointments', function ($q) use ($scheduledAt) {
                $q->where('scheduled_at', $scheduledAt);
            });

        $vets = $vetsQuery->get();
        return response()->json($vets);
    }

    /**
     * Return a single appointment with relations for prefilling the reschedule form.
     */
    public function getAppointment($id)
    {
        try {
            $appointment = Appointment::with(['pet.client', 'service', 'employee'])->find($id);
        } catch (\Exception $e) {
            // return exception info in debug while troubleshooting
            $debug = [
                'error' => 'exception_loading_appointment',
                'exception_message' => $e->getMessage(),
                'exception_trace' => substr($e->getTraceAsString(), 0, 1000),
                'auth_user_id' => Auth::id(),
                'requested_id' => $id,
            ];
            Log::debug('getAppointment exception', $debug);
            return response()->json(['debug' => $debug], 500);
        }

        if (! $appointment) {
            $debug = [
                'message' => 'Appointment not found',
                'auth_user_id' => Auth::id(),
                'requested_id' => $id,
            ];
            Log::debug('getAppointment not found', $debug);
            return response()->json(['debug' => $debug], 404);
        }

        $debug = [
            'message' => 'Appointment loaded',
            'auth_user_id' => Auth::id(),
            'requested_id' => $id,
        ];
        // include debug object alongside the appointment so the frontend can inspect it
        return response()->json(['debug' => $debug, 'appointment' => $appointment], 200);
    }

    public function scheduleAppointment(Request $request)
    {
        // Accept scheduled_at or scheduled_date + scheduled_time from the client (form sends local datetime string)
        $validator = Validator::make($request->all(), [
            'pet_id' => 'required|exists:pets,id',
            'service_id' => 'required|exists:services,id',
            'employee_id' => 'nullable|exists:users,id',
            'scheduled_at' => 'sometimes|date',
            'scheduled_date' => 'sometimes|date',
            'scheduled_time' => 'sometimes',
            'notes' => 'nullable|string',
            'size' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // determine scheduled_at from either scheduled_at or scheduled_date + scheduled_time
        if (! empty($data['scheduled_at'])) {
            $scheduledAt = Carbon::parse($data['scheduled_at']);
        } elseif (! empty($request->input('scheduled_date')) && ! empty($request->input('scheduled_time'))) {
            $scheduledAt = Carbon::parse($request->input('scheduled_date') . ' ' . $request->input('scheduled_time'));
        } else {
            return response()->json(['error' => 'scheduled_at o scheduled_date+scheduled_time son requeridos'], 422);
        }

        // ensure scheduled_at is in the future
        if (! $scheduledAt->isFuture()) {
            return response()->json(['error' => 'scheduled_at must be a future date'], 422);
        }

        $pet = Pet::findOrFail($data['pet_id']);
        $service = Service::findOrFail($data['service_id']);

        $size = $data['size'] ?? $pet->size ?? null;
        if ($size === null) {
            return response()->json(['error' => 'Pet size is not defined.'], 422);
        }

        try {
            $price = $service->getPriceForSize($size);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

    // $scheduledAt already parsed above

        if (! empty($data['employee_id'])) {
            $emp = Employees::findOrFail($data['employee_id']);
            if (! $emp->isVeterinarian()) {
                return response()->json(['error' => 'Employee is not a veterinarian'], 422);
            }

            $day = $scheduledAt->dayOfWeek;
            $time = $scheduledAt->format('H:i:s');

            $hasSchedule = $emp->schedules()
                ->where('active', true)
                ->where(function($sq) use ($day) {
                    $sq->where(function($r) use ($day) {
                        $r->whereColumn('day_of_week_start', '<=', 'day_of_week_end')
                          ->where('day_of_week_start', '<=', $day)
                          ->where('day_of_week_end', '>=', $day);
                    })
                    ->orWhere(function($r2) use ($day) {
                        $r2->whereColumn('day_of_week_start', '>', 'day_of_week_end')
                           ->where(function($a) use ($day) {
                               $a->where('day_of_week_start', '<=', $day)
                                 ->orWhere('day_of_week_end', '>=', $day);
                           });
                    });
                })
                ->where('start_time', '<=', $time)
                ->where('end_time', '>', $time)
                ->exists();

            if (! $hasSchedule) {
                return response()->json(['error' => 'Veterinarian does not work at that date/time'], 422);
            }

            $conflict = Appointment::where('employee_id', $emp->id)
                ->where('scheduled_at', $scheduledAt)
                ->exists();

            if ($conflict) {
                return response()->json(['error' => 'Veterinarian not available at that time'], 422);
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
            'status' => StatusEnum::SCHEDULED->value,
        ]);

        return response()->json($appointment, 201);
    }

    public function completeAppointment($id)
    {
        $appointment = Appointment::find($id);
        if (! $appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }
        if ($appointment->status === StatusEnum::CANCELADA->value) {
            return response()->json(['error' => 'Cannot complete a canceled appointment'], 422);
        }
        $appointment->status = StatusEnum::COMPLETED->value;
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
                return response()->json(['error' => 'Employee is not a veterinarian'], 422);
            }
            $day = $scheduledAt->dayOfWeek;
            $time = $scheduledAt->format('H:i:s');
            $hasSchedule = $emp->schedules()
                ->where('active', true)
                ->where(function($sq) use ($day) {
                    $sq->where(function($r) use ($day) {
                        $r->whereColumn('day_of_week_start', '<=', 'day_of_week_end')
                          ->where('day_of_week_start', '<=', $day)
                          ->where('day_of_week_end', '>=', $day);
                    })
                    ->orWhere(function($r2) use ($day) {
                        $r2->whereColumn('day_of_week_start', '>', 'day_of_week_end')
                           ->where(function($a) use ($day) {
                               $a->where('day_of_week_start', '<=', $day)
                                 ->orWhere('day_of_week_end', '>=', $day);
                           });
                    });
                })
                ->where('start_time', '<=', $time)
                ->where('end_time', '>', $time)
                ->exists();
            if (! $hasSchedule) {
                return response()->json(['error' => 'Veterinarian does not work at that date/time'], 422);
            }
            $conflict = Appointment::where('employee_id', $emp->id)
                ->where('scheduled_at', $scheduledAt)
                ->where('id', '!=', $appointment->id)
                ->exists();
            if ($conflict) {
                return response()->json(['error' => 'Veterinarian not available at that time'], 422);
            }
            $appointment->employee_id = $emp->id;
        }

        $appointment->scheduled_at = $scheduledAt;
            $appointment->status = StatusEnum::REPROGRAMADA->value;
        $appointment->save();
        return response()->json($appointment, 200);
    }

    public function cancelAppointment($id)
    {
        $appointment = Appointment::find($id);
        if (! $appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }
        $appointment->status = StatusEnum::CANCELADA->value;
        $appointment->save();
        return response()->json(['message' => 'Appointment canceled', 'appointment' => $appointment], 200);
    }
}
