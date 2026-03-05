<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerServiceBillingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $customer = $this->customer;
        $service = $this->service;
        $domain = $this->domain;

        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'customer_name' => $customer->name,
            'company_name' => $customer->company_name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'document' => $customer->document,
            'type' => $customer->type,
            'address' => $customer->address,
            'city' => $customer->city,
            'state' => $customer->state,
            'zip_code' => $customer->zip_code,
            'service_id' => $this->service_id,
            'service_name' => $service->name,
            'service_description' => $service->description,
            'price' => $this->price,
            'recurrence' => $this->recurrence,
            'domain_id' => $this->domain_id,
            'domain_name' => $domain ? $domain->name : null,
            'start_date' => $this->start_date,
            'next_due_date' => $this->next_due_date,
        ];
    }
}
