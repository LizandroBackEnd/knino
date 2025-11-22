<?php

namespace App\Http\Controllers;

use App\Models\Employees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeSchedule;

class EmployeesController extends Controller
{
    public function addEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'last_name_primary' => 'required|string|max:255',
            'last_name_secondary' => 'nullable|string|max:255',
            'phone' => 'required|string|max:10',
            // employees are stored in the users table
            'email' => 'required|string|email|max:255|unique:users,email',
            'schedules' => 'required|array|min:1',
            'schedules.*.day_of_week_start' => 'required|integer|min:0|max:6',
            'schedules.*.day_of_week_end' => 'required|integer|min:0|max:6',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i',
            'schedules.*.active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        DB::beginTransaction();
        try {
            $employee = Employees::create([
                'name' => $data['name'],
                'last_name_primary' => $data['last_name_primary'],
                'last_name_secondary' => $data['last_name_secondary'] ?? null,
                'phone' => $data['phone'],
                'email' => $data['email'],
            ]);

            // create schedules (ensure no overlap in day range + time)
            foreach ($data['schedules'] as $s) {
                $startDay = $s['day_of_week_start'];
                $endDay = $s['day_of_week_end'];
                $startTime = $s['start_time'];
                $endTime = $s['end_time'];

                if (strtotime($startTime) >= strtotime($endTime)) {
                    throw new \Exception('start_time must be before end_time for a schedule');
                }

                $overlap = EmployeeSchedule::where('user_id', $employee->id)
                    ->where(function ($q) use ($startDay, $endDay) {
                        $q->whereBetween('day_of_week_start', [$startDay, $endDay])
                          ->orWhereBetween('day_of_week_end', [$startDay, $endDay])
                          ->orWhere(function ($q2) use ($startDay, $endDay) {
                              $q2->where('day_of_week_start', '<', $startDay)
                                 ->where('day_of_week_end', '>', $endDay);
                          });
                    })
                    ->where(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                    })
                    ->exists();

                if ($overlap) {
                    throw new \Exception('Schedule overlaps with an existing schedule for this employee');
                }

                EmployeeSchedule::create([
                    'user_id' => $employee->id,
                    'day_of_week_start' => $startDay,
                    'day_of_week_end' => $endDay,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'active' => $s['active'] ?? true,
                ]);
            }

            DB::commit();
            // return created employee with id so frontend can continue
            return response()->json($employee, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Could not create employee', 'detail' => $e->getMessage()], 500);
        }
    }

    public function getEmployees()
    {
        $employees = Employees::all();

        if ($employees->isEmpty()) {
            return response()->json(['message' => 'No employees found'], 404);
        }
        return response()->json($employees, 200);
    }

    public function getEmployeeByEmail($email)
    {
        $employee = Employees::where('email', $email)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
        return response()->json($employee, 200);
    }

    public function updateEmployeeById(Request $request, $id)
    {
    $employee = Employees::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'last_name_primary' => 'sometimes|required|string|max:255',
            'last_name_secondary' => 'sometimes|nullable|string|max:255',
            'phone' => 'sometimes|required|string|max:10',
            // ensure uniqueness against users table and exclude current record
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id . ',id',
            'schedules' => 'required|array|min:1',
            'schedules.*.day_of_week_start' => 'required|integer|min:0|max:6',
            'schedules.*.day_of_week_end' => 'required|integer|min:0|max:6',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i',
            'schedules.*.active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if (isset($data['name'])) {
            $employee->name = $data['name'];
        }
        if (isset($data['last_name_primary'])) {
            $employee->last_name_primary = $data['last_name_primary'];
        }
        if (array_key_exists('last_name_secondary', $data)) {
            $employee->last_name_secondary = $data['last_name_secondary'];
        }
        if (isset($data['phone'])) {
            $employee->phone = $data['phone'];
        }
        if (isset($data['email'])) {
            $employee->email = $data['email'];
        }

        DB::beginTransaction();
        try {
            $employee->save();

            // replace schedules: delete existing and create new ones (with overlap checks)
            EmployeeSchedule::where('user_id', $employee->id)->delete();
            foreach ($data['schedules'] as $s) {
                $startDay = $s['day_of_week_start'];
                $endDay = $s['day_of_week_end'];
                $startTime = $s['start_time'];
                $endTime = $s['end_time'];

                if (strtotime($startTime) >= strtotime($endTime)) {
                    throw new \Exception('start_time must be before end_time for a schedule');
                }

                $overlap = EmployeeSchedule::where('user_id', $employee->id)
                    ->where(function ($q) use ($startDay, $endDay) {
                        $q->whereBetween('day_of_week_start', [$startDay, $endDay])
                          ->orWhereBetween('day_of_week_end', [$startDay, $endDay])
                          ->orWhere(function ($q2) use ($startDay, $endDay) {
                              $q2->where('day_of_week_start', '<', $startDay)
                                 ->where('day_of_week_end', '>', $endDay);
                          });
                    })
                    ->where(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                    })
                    ->exists();

                if ($overlap) {
                    throw new \Exception('Schedule overlaps with an existing schedule for this employee');
                }

                EmployeeSchedule::create([
                    'user_id' => $employee->id,
                    'day_of_week_start' => $startDay,
                    'day_of_week_end' => $endDay,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'active' => $s['active'] ?? true,
                ]);
            }

            DB::commit();
            return response()->json($employee, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Could not update employee', 'detail' => $e->getMessage()], 500);
        }
    }

    public function deleteEmployeeById($id)
    {
        $employee = Employees::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
        $employee->delete();
        return response()->json(['message' => 'Employee deleted successfully'], 200);
    }

    /**
     * List schedules for a specific employee
     */
    public function getSchedules($userId)
    {
        $employee = Employees::find($userId);
        if (! $employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
        $schedules = $employee->schedules()->get();
        return response()->json($schedules, 200);
    }

    /**
     * Add a schedule for an employee.
     * body: day_of_week_start (0-6), day_of_week_end (0-6), start_time (HH:MM), end_time (HH:MM), active (bool)
     */
    public function addSchedule(Request $request, $userId)
    {
        $employee = Employees::find($userId);
        if (! $employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'day_of_week_start' => 'required|integer|min:0|max:6',
            'day_of_week_end' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $startDay = $data['day_of_week_start'];
        $endDay = $data['day_of_week_end'];
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];

        if (strtotime($startTime) >= strtotime($endTime)) {
            return response()->json(['error' => 'start_time must be before end_time'], 422);
        }

        // prevent overlapping schedules for the same user/day range + time
        $overlap = EmployeeSchedule::where('user_id', $userId)
            ->where(function ($q) use ($startDay, $endDay) {
                $q->whereBetween('day_of_week_start', [$startDay, $endDay])
                  ->orWhereBetween('day_of_week_end', [$startDay, $endDay])
                  ->orWhere(function ($q2) use ($startDay, $endDay) {
                      $q2->where('day_of_week_start', '<', $startDay)
                         ->where('day_of_week_end', '>', $endDay);
                  });
            })
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })
            ->exists();

        if ($overlap) {
            return response()->json(['error' => 'Schedule overlaps with an existing schedule for this employee'], 422);
        }

        $schedule = EmployeeSchedule::create([
            'user_id' => $userId,
            'day_of_week_start' => $startDay,
            'day_of_week_end' => $endDay,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'active' => $data['active'] ?? true,
        ]);

        return response()->json($schedule, 201);
    }

    /**
     * Update an existing schedule.
     */
    public function updateSchedule(Request $request, $id)
    {
        $schedule = EmployeeSchedule::find($id);
        if (! $schedule) {
            return response()->json(['message' => 'Schedule not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'day_of_week_start' => 'sometimes|required|integer|min:0|max:6',
            'day_of_week_end' => 'sometimes|required|integer|min:0|max:6',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i',
            'active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $startDay = $data['day_of_week_start'] ?? $schedule->day_of_week_start;
        $endDay = $data['day_of_week_end'] ?? $schedule->day_of_week_end;
        $start = $data['start_time'] ?? $schedule->start_time;
        $end = $data['end_time'] ?? $schedule->end_time;

        if (isset($data['start_time']) || isset($data['end_time']) || isset($data['day_of_week_start']) || isset($data['day_of_week_end'])) {
            if (strtotime($start) >= strtotime($end)) {
                return response()->json(['error' => 'start_time must be before end_time'], 422);
            }

            $overlap = EmployeeSchedule::where('user_id', $schedule->user_id)
                ->where('id', '!=', $schedule->id)
                ->where(function ($q) use ($startDay, $endDay) {
                    $q->whereBetween('day_of_week_start', [$startDay, $endDay])
                      ->orWhereBetween('day_of_week_end', [$startDay, $endDay])
                      ->orWhere(function ($q2) use ($startDay, $endDay) {
                          $q2->where('day_of_week_start', '<', $startDay)
                             ->where('day_of_week_end', '>', $endDay);
                      });
                })
                ->where(function ($q) use ($start, $end) {
                    $q->where('start_time', '<', $end)
                      ->where('end_time', '>', $start);
                })
                ->exists();

            if ($overlap) {
                return response()->json(['error' => 'Schedule overlaps with an existing schedule for this employee'], 422);
            }
        }

        // fill allowed fields and save
        $schedule->fill($data);
        $schedule->save();
        return response()->json($schedule, 200);
    }

    /**
     * Delete a schedule (hard delete). Could change to soft-disable by setting active=false.
     */
    public function deleteSchedule($id)
    {
        $schedule = EmployeeSchedule::find($id);
        if (! $schedule) {
            return response()->json(['message' => 'Schedule not found'], 404);
        }
        $schedule->delete();
        return response()->json(['message' => 'Schedule deleted'], 200);
    }
}
