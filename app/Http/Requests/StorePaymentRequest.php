<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StorePaymentRequest
 *
 * Validates incoming request data for creating a new payment request.
 * Associates a payment with a customer service (customer_service_id from route).
 *
 * Validation Rules:
 * - request_id: Required, string (unique payment request identifier)
 * - amount: Required, numeric (payment amount)
 * - status: Optional, string (default: "PENDING", e.g., "PENDING", "CONFIRMED", "FAILED")
 * - barcode: Optional, string (boleto barcode if applicable)
 * - pix_copy_paste: Optional, string (Pix copy-paste code if applicable)
 * - digitable_line: Optional, string (boleto digitable line if applicable)
 * - your_number: Optional, string (payment reference number)
 */
class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'nullable|string',
            'barcode' => 'nullable|string',
            'pix_copy_paste' => 'nullable|string',
            'digitable_line' => 'nullable|string',
            'your_number' => 'nullable|string',
        ];
    }
}
