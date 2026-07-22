<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_id' => $this->request_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'pix_copy_paste' => $this->pix_copy_paste,
            'barcode' => $this->barcode,
            'digitable_line' => $this->digitable_line,
            'your_number' => $this->your_number,
            'payment_method' => $this->payment_method,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'service_name' => $this->whenLoaded('customerService', fn() => $this->customerService->service->name ?? null),
            'due_date' => $this->whenLoaded('customerService', fn() => $this->customerService->next_due_date ?? null),
        ];
    }
}
