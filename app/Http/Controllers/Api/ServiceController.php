<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::with('customer')->get();
        return response()->json($services);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        $service = Service::create($validatedData);

        return response()->json($service, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        return response()->json($service);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $validatedData = $request->validate([
            'customer_id' => 'sometimes|required|exists:customers,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:255',
        ]);

        $service->update($validatedData);

        return response()->json($service);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete();

        return response()->json(null, 204);
    }
}
