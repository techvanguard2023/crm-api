<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::with('domains', 'services')->get();
        return response()->json($customers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'required|string|max:20',
        ]);

        $customer = Customer::create($validatedData);

        return response()->json($customer, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return response()->json($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:customers,email,' . $customer->id,
            'phone' => 'sometimes|required|string|max:20',
        ]);

        $customer->update($validatedData);

        return response()->json($customer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json(null, 204);
    }

    /**
     * Attach a service to the customer.
     */
    public function addService(Request $request, Customer $customer)
    {
        $validatedData = $request->validate([
            'service_id' => 'required|exists:services,id',
            'price' => 'required|numeric|min:0',
            'recurrence' => 'required|string',
        ]);

        // Attach user to service with pivot data
        // We use syncWithoutDetaching or just attach. syncWithoutDetaching is safer for updates if needed,
        // but attach allows duplicates if the table doesn't have a unique constraint on the pair (usually it does or should).
        // Let's use attach for now, or syncWithoutDetaching to prevent errors if already exists.
        // Given the requirement "Clientes podem ter varios serviços" (Customers can have multiple services),
        // if they can have the SAME service twice (e.g. 2 hosting plans), then we need an ID on pivot (we have it).
        // But usually attach is fine.

        $customer->services()->attach($validatedData['service_id'], [
            'price' => $validatedData['price'],
            'recurrence' => $validatedData['recurrence']
        ]);

        return response()->json(['message' => 'Service attached successfully.'], 201);
    }
}
