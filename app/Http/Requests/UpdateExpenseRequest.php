<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateExpenseRequest
 *
 * Validates incoming request data for updating an existing expense record.
 * All fields are optional, allowing partial updates.
 *
 * Validation Rules:
 * - name: Optional, string, max 255 characters
 * - description: Optional, string, max 255 characters
 * - amount: Optional, numeric (accepts decimal values for currency)
 * - date: Optional, valid date format (YYYY-MM-DD)
 * - recurrence: Optional, string (e.g., "once", "monthly", "yearly")
 * - category: Optional, string
 * - status: Optional, string (e.g., "pending", "paid")
 */
class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'date' => 'sometimes|required|date',
            'recurrence' => 'sometimes|required|string',
            'category' => 'sometimes|required|string',
            'status' => 'sometimes|required|string|in:pending,paid',
        ];
    }
}
