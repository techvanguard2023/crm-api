<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Recurrence;
use Illuminate\Http\Request;

class RecurrenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recurrences = Recurrence::with('service')->get();
        return response()->json($recurrences);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'service_id' => 'required|exists:services,id',
            'type' => 'required|string|max:50',
        ]);

        $recurrence = Recurrence::create($validatedData);

        return response()->json($recurrence, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Recurrence $recurrence)
    {
        return response()->json($recurrence);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Recurrence $recurrence)
    {
        $validatedData = $request->validate([
            'service_id' => 'sometimes|required|exists:services,id',
            'type' => 'sometimes|required|string|max:50',
        ]);

        $recurrence->update($validatedData);

        return response()->json($recurrence);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recurrence $recurrence)
    {
        $recurrence->delete();

        return response()->json(null, 204);
    }
}
