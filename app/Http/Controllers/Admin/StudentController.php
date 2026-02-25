<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('rfid_uid', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $students = $query
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 10));

        return response()->json($students);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|max:20|unique:students',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'year_level' => 'required|string|max:20',
            'department' => 'required|string|max:50',
            'rfid_uid' => 'nullable|string|max:10|unique:students',
            'photo_url' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE', 'SUSPENDED'])],
        ]);

        if ($request->hasFile('photo_url')) {
            $path = $request->file('photo_url')->store('students', 'public');
            $validated['photo_url'] = $path;
        }

        $student = Student::create($validated);

        return response()->json($student, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|max:20|unique:students',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'year_level' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:50',
            'rfid_uid' => 'nullable|string|max:10|unique:students',
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE', 'SUSPENDED'])],
        ]);

        $student = Student::create($validated);

        return response()->json($student, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students')->ignore($student->id),
            ],
            'first_name' => 'required|string|max:100',
            'middle_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'year_level' => 'required|string|max:20',
            'department' => 'required|string|max:50',
            'rfid_uid' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('students')->ignore($student->id),
            ],
            'photo_url' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE', 'SUSPENDED'])],
        ]);
        if ($request->hasFile('photo_url')) {

            if ($student->photo_url) {
                Storage::disk('public')->delete($student->photo_url);
            }

            $path = $request->file('photo_url')->store('students', 'public');
            $validated['photo_url'] = $path;
        }

        $student->update($validated);

        return response()->json($student);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        $hasActiveRental = Rental::where('student_id', $student->id)
            ->where('status', 'ACTIVE')
            ->exists();

        $hasActivePenalty = Rental::where('student_id', $student->id)
            ->where('status', 'EXPIRED')
            ->whereHas('penalty', function ($query) {
                $query->where('status', 'ACTIVE');
            })
            ->exists();

        if ($hasActiveRental || $hasActivePenalty) {
            return response()->json([
                'message' => 'Cannot delete student with active rental or unresolved penalty'
            ], 422);
        }

        if ($student->photo_url) {
            Storage::disk('public')->delete($student->photo_url);
        }

        $student->delete();

        return response()->json(['success' => true]);
    }
}
