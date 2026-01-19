<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerService;
use App\Models\ServiceRenewal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerServiceController extends Controller
{
    /**
     * Renew a managed service (register payment).
     */
    public function renew(Request $request, $id)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0',
            'date' => 'nullable|date',
        ]);

        $customerService = CustomerService::findOrFail($id);

        $renewedAt = $validatedData['date'] ? Carbon::parse($validatedData['date']) : now();
        
        // Calculate next due date based on current next_due_date or start_date
        $baseDate = $customerService->next_due_date ?? $customerService->start_date ?? now();
        $baseDate = Carbon::parse($baseDate);
        
        $newDueDate = $baseDate->copy();

        switch ($customerService->recurrence) {
            case 'monthly':
                $newDueDate->addMonth();
                break;
            case 'quarterly':
                $newDueDate->addMonths(3);
                break;
            case 'semiannual':
                $newDueDate->addMonths(6);
                break;
            case 'yearly':
                $newDueDate->addYear();
                break;
            default:
                // If recurrence is unknown or 'one_time', maybe we shouldn't update date?
                // For now, let's assume monthly default or no change if not matched.
                // Or maybe just add month as fallback? 
                // Let's add Month as fallback for now to ensure movement.
                $newDueDate->addMonth(); 
                break;
        }

        // Create history record
        ServiceRenewal::create([
            'customer_service_id' => $customerService->id,
            'amount' => $validatedData['amount'],
            'renewed_at' => $renewedAt,
            'renews_until' => $newDueDate,
        ]);

        // Update active service state
        $customerService->update([
            'next_due_date' => $newDueDate,
        ]);

        return response()->json([
            'message' => 'Service renewed successfully.',
            'new_due_date' => $newDueDate->toDateString(),
            'service' => $customerService
        ]);
    }
}
