<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Requests\AttachServiceToCustomerRequest;
use App\Models\Customer;
use App\Http\Resources\CustomerResource;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = $this->loadCustomersWithRelations(Customer::query());

        return CustomerResource::collection($customers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        return new CustomerResource($customer);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $this->loadSingleCustomerWithRelations($customer);

        return new CustomerResource($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return new CustomerResource($customer);
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
    public function addService(AttachServiceToCustomerRequest $request, Customer $customer)
    {
        $data = $request->validated();

        $customer->services()->attach($data['service_id'], [
            'price' => $data['price'],
            'recurrence' => $data['recurrence'],
            'start_date' => $data['start_date'],
            'next_due_date' => $data['next_due_date']
        ]);

        return response()->json(['message' => 'Service attached successfully.'], 201);
    }
    /**
     * Get customers who have at least one active service.
     */
    public function withServices()
    {
        $customers = $this->loadCustomersWithRelations(Customer::has('services'));

        return CustomerResource::collection($customers);
    }

    /**
     * Get customers filtered by a specific service type (service ID).
     * Returns all services and domains associated with the customer.
     */
    public function byServiceType($serviceId)
    {
        $query = Customer::whereHas('services', function ($q) use ($serviceId) {
            $q->where('services.id', $serviceId);
        })->with(['domains', 'services']);

        $customers = $this->loadCustomersWithRelations($query);

        return CustomerResource::collection($customers);
    }

    /**
     * Load customers with domains and services (with domain relation on pivots).
     */
    private function loadCustomersWithRelations($query)
    {
        $customers = $query->with(['domains', 'services'])->get();
        $customers->pluck('services')->flatten()->pluck('pivot')->each->load('domain');

        return $customers;
    }

    /**
     * Load a single customer with domains and services (with domain relation on pivots).
     */
    private function loadSingleCustomerWithRelations(Customer $customer): void
    {
        $customer->load(['domains', 'services']);
        $customer->services->pluck('pivot')->each->load('domain');
    }
}
