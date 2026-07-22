<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * AttachServiceToCustomerRequest
 *
 * Validates incoming request data for attaching a service to a customer.
 * This creates a relationship record in the customer_service pivot table.
 *
 * Validation Rules:
 * - service_id: Required, integer, must exist in services table
 * - price: Required, numeric (service price for this customer)
 * - recurrence: Required, string (billing cycle: "once", "monthly", "yearly", etc.)
 * - start_date: Required, valid date format (when service starts)
 * - next_due_date: Required, valid date format (when next payment is due)
 */
class AttachServiceToCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => 'required|integer|exists:services,id',
            'price' => 'required|numeric|min:0.01',
            'recurrence' => 'required|string',
            'start_date' => 'required|date',
            'next_due_date' => 'required|date',
        ];
    }
}
