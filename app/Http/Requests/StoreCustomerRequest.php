<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreCustomerRequest
 *
 * Validates incoming request data for creating a new customer.
 *
 * Validation Rules:
 * - name: Required, string, max 255 characters
 * - email: Required, valid email format, must be unique in customers table
 * - phone: Required, string, max 20 characters
 * - company_name: Optional, string, max 255 characters
 * - type: Optional, string
 * - document: Optional, string (tax ID, ID number, etc.)
 * - address: Optional, string
 * - city: Optional, string
 * - state: Optional, string
 * - zip_code: Optional, string
 * - country: Optional, string
 */
class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'required|string|max:20',
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
