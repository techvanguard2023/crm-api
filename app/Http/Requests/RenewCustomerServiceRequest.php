<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * RenewCustomerServiceRequest
 *
 * Validates incoming request data for renewing a managed customer service.
 * When a service is renewed, a ServiceRenewal record is created to track payment history,
 * and the next_due_date is updated based on the recurrence schedule.
 *
 * Validation Rules:
 * - amount: Required, numeric (payment amount for this renewal)
 * - date: Optional, valid date format (when renewal payment was made; defaults to current date)
 */
class RenewCustomerServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'date' => 'nullable|date',
        ];
    }
}
