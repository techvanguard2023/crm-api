<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $services = ServiceResource::collection($this->whenLoaded('services'));
        
        $total = 0;
        if ($this->relationLoaded('services')) {
            $total = $this->services->sum('pivot.price');
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'customer_name' => $this->name, // Alias para compatibilidade com n8n
            'company_name' => $this->company_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'type' => $this->type,
            'document' => $this->document,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'country' => $this->country,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'domains' => $this->whenLoaded('domains'),
            'services' => $services,
            'totals' => [
                'services_total' => number_format($total, 2, '.', ''),
            ],
        ];
    }
}
