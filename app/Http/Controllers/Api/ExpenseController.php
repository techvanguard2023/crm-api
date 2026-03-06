<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = Expense::orderBy('date', 'desc')->get();
        return response()->json($expenses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'recurrence' => 'required|string|in:monthly,yearly,one_time',
            'category' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:pending,paid,cancelled',
        ]);

        $expense = Expense::create($validated);

        return response()->json($expense, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        return response()->json($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'description' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric',
            'date' => 'sometimes|required|date',
            'recurrence' => 'sometimes|required|string|in:monthly,yearly,one_time',
            'category' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:pending,paid,cancelled',
        ]);

        $expense->update($validated);

        return response()->json($expense);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->json(null, 204);
    }
}
