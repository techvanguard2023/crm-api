<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Price;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prices = Price::with('service')->get();
        return response()->json($prices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'service_id' => 'required|exists:services,id',
            'price' => 'required|numeric|min:0',
        ]);

        $price = Price::create($validatedData);

        return response()->json($price, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Price $price)
    {
        return response()->json($price);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Price $price)
    {
        $validatedData = $request->validate([
            'service_id' => 'sometimes|required|exists:services,id',
            'price' => 'sometimes|required|numeric|min:0',
        ]);

        $price->update($validatedData);

        return response()->json($price);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Price $price)
    {
        $price->delete();

        return response()->json(null, 204);
    }
}
