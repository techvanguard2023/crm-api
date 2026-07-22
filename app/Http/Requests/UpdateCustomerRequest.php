<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateCustomerRequest
 *
 * Validates incoming request data for updating an existing customer.
 *
 * Validation Rules:
 * - name: Optional, string, max 255 characters
 * - email: Optional, valid email format, must be unique in customers table (excluding current customer)
 * - phone: Optional, string, max 20 characters
 * - company_name: Optional, string, max 255 characters
 * - type: Optional, string
 * - document: Optional, string (tax ID, ID number, etc.)
 * - address: Optional, string
 * - city: Optional, string
 * - state: Optional, string
 * - zip_code: Optional, string
 * - country: Optional, string
 */
class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')->id;

        return [
            'name' => 'sometimes|required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'sometimes|required|email|unique:customers,email,' . $customerId,
            'phone' => 'sometimes|required|string|max:20',
            'type' => 'nullable|string',
            'document' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'country' => 'nullable|string',
        ];
    }
}
