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
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'required|string|max:20',
            'type' => 'nullable|string',
            'document' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'country' => 'nullable|string',
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
            'company_name' => 'nullable|string|max:255',
            'email' => 'sometimes|required|email|unique:customers,email,' . $customer->id,
            'phone' => 'sometimes|required|string|max:20',
            'type' => 'nullable|string',
            'document' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'country' => 'nullable|string',
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
            'start_date' => 'required|date',
            'next_due_date' => 'required|date',
        ]);

        $customer->services()->attach($validatedData['service_id'], [
            'price' => $validatedData['price'],
            'recurrence' => $validatedData['recurrence'],
            'start_date' => $validatedData['start_date'],
            'next_due_date' => $validatedData['next_due_date']
        ]);

        return response()->json(['message' => 'Service attached successfully.'], 201);
    }
    /**
     * Get customers who have at least one active service.
     */
    public function withServices()
    {
        $customers = Customer::has('services')->with('services')->get();
        return response()->json($customers);
    }

    /**
     * Get customers filtered by a specific service type (service ID).
     */
    public function byServiceType($serviceId)
    {
        $customers = Customer::whereHas('services', function ($query) use ($serviceId) {
            $query->where('services.id', $serviceId);
        })->with(['services' => function ($query) use ($serviceId) {
            $query->where('services.id', $serviceId);
        }])->get();

        return response()->json($customers);
    }
}
