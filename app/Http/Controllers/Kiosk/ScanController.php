<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;

class ScanController extends Controller
{
    public function scan(Request $request)
    {
        $request->validate([
            'rfid_uid' => 'required|string',
        ]);

        $student = Student::where('rfid_uid', $request->rfid_uid)->first();

        if (!$student) {
            return response()->json(['error' => 'STUDENT_NOT_FOUND'], 404);
        }

        if ($student->status === 'SUSPENDED') {
            return response()->json([
                'status' => 'suspended',
                'student' => [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                ]
            ]);
        }

        if ($student->status !== 'ACTIVE') {
            return response()->json(['error' => 'STUDENT_INACTIVE'], 403);
        }

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'year_level' => $student->year_level,
                'department' => $student->department,
            ]
        ]);
    }
}
