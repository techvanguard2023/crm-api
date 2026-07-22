<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\RecurrenceService;

class ExpenseController extends Controller
{
    /**
     * Get expense metrics.
     */
    public function metrics()
    {
        $expenses = Expense::all();

        $totalGeral = $expenses->sum('amount');
        $totalPago = $expenses->where('status', 'paid')->sum('amount');
        $totalPendente = $expenses->where('status', 'pending')->sum('amount');
        $totalCategorias = $expenses->pluck('category')->filter()->unique()->count();

        return response()->json([
            'total_geral' => number_format($totalGeral, 2, '.', ''),
            'total_pago' => number_format($totalPago, 2, '.', ''),
            'total_pendente' => number_format($totalPendente, 2, '.', ''),
            'total_categorias' => $totalCategorias,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = Expense::orderBy('date', 'desc')->get();
        return ExpenseResource::collection($expenses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        $expense = Expense::create($request->validated());

        return (new ExpenseResource($expense))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        return new ExpenseResource($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());

        return new ExpenseResource($expense);
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
