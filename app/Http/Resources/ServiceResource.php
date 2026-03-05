<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->pivot->id,
            'service_id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->pivot->price,
            'recurrence' => $this->pivot->recurrence,
            'domain_id' => $this->pivot->domain_id,
            'domain_name' => $this->pivot->domain ? $this->pivot->domain->name : null,
            'start_date' => $this->pivot->start_date,
            'next_due_date' => $this->pivot->next_due_date,
        ];
    }
}
