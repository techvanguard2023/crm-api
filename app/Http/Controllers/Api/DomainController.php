<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DomainResource;
use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DomainController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $domains = Domain::with('customer')->get();
        return DomainResource::collection($domains);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255|unique:domains,name',
            'status' => 'required|string|max:50',
            'expired_at' => 'nullable|date',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), 422);
        }

        $domain = Domain::create($validatedData->validated());

        return (new DomainResource($domain))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Domain $domain)
    {
        return new DomainResource($domain);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Domain $domain)
    {
        $validatedData = Validator::make($request->all(), [
            'customer_id' => 'sometimes|required|exists:customers,id',
            'name' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string|max:50',
            'expired_at' => 'nullable|date',
        ]);

        $domain->update($validatedData->validated());

        return new DomainResource($domain);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Domain $domain)
    {
        $domain->delete();

        return response()->json(null, 204);
    }
}
