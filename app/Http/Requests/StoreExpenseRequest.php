<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreExpenseRequest
 *
 * Validates incoming request data for creating a new expense record.
 *
 * Validation Rules:
 * - name: Required, string, max 255 characters (e.g., "Server Hosting", "Software License")
 * - description: Required, string, max 255 characters
 * - amount: Required, numeric (accepts decimal values for currency)
 * - date: Required, valid date format (YYYY-MM-DD)
 * - recurrence: Required, string (e.g., "once", "monthly", "yearly")
 * - category: Required, string (e.g., "infrastructure", "software", "licenses")
 * - status: Required, string (e.g., "pending", "paid")
 */
class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'recurrence' => 'required|string',
            'category' => 'required|string',
            'status' => 'required|string|in:pending,paid',
        ];
    }
}
