<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerService;
use App\Models\ServiceRenewal;
use App\Http\Resources\CustomerServiceBillingResource;
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

    /**
     * Get services ready to be billed (pending, due today, or upcoming).
     */
    public function readyToBill(Request $request)
    {
        $services = CustomerService::with(['customer', 'service', 'domain'])
            ->orderBy('next_due_date', 'asc')
            ->get();

        $data = $services->map(function ($cs) {
            $dueDate = Carbon::parse($cs->next_due_date)->startOfDay();
            $today = now()->startOfDay();

            if ($dueDate->isPast() && !$dueDate->isToday()) {
                $status = 'pending'; // Overdue / Pendente
            } elseif ($dueDate->isToday()) {
                $status = 'due_today'; // Vence hoje
            } else {
                $status = 'upcoming'; // A vencer
            }

            return [
                'id' => $cs->id,
                'customer_name' => $cs->customer->name ?? 'N/A',
                'service_name' => $cs->service->name ?? 'N/A',
                'domain_name' => $cs->domain->name ?? null,
                'amount' => $cs->price,
                'due_date' => $cs->next_due_date,
                'status' => $status,
                'recurrence' => $cs->recurrence,
            ];
        });

        // Optional: filter only pending/due_today if requested by frontend
        // Currently returning all so frontend can categorize them via the 'status' field.

        return response()->json($data);
    }

    /**
     * Get metrics for the ready-to-bill screen.
     */
    public function readyToBillMetrics()
    {
        $today = now()->startOfDay();

        // 1. Total de clientes pendentes (unique customers with overdue services)
        $pendingCustomers = CustomerService::where('next_due_date', '<', $today)
            ->distinct('customer_id')
            ->count('customer_id');

        // 2. Total de serviços a vencer (upcoming/due today)
        $servicesDue = CustomerService::where('next_due_date', '>=', $today)->count();

        // 3. Total em valor R$ previsto (Pendentes + Todos os que vencem até o fim do mês atual)
        // Or if 'previsto' means all active pending + next_due_date in current month:
        $expectedAmountQuery = CustomerService::where('next_due_date', '<', $today)
            ->orWhereBetween('next_due_date', [
                $today->copy()->startOfMonth(), 
                $today->copy()->endOfMonth()
            ])->sum('price');

        return response()->json([
            'pending_customers' => $pendingCustomers,
            'services_due' => $servicesDue,
            'expected_amount' => number_format($expectedAmountQuery, 2, '.', ''),
        ]);
    }
}
