<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RenewCustomerServiceRequest;
use App\Models\CustomerService;
use App\Models\Payment;
use App\Http\Resources\CustomerServiceBillingResource;
use App\Services\RecurrenceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerServiceController extends Controller
{
    /**
     * Renew a managed service (register payment).
     */
    public function renew(RenewCustomerServiceRequest $request, $id)
    {
        $data = $request->validated();

        $customerService = CustomerService::findOrFail($id);

        $renewedAt = $data['date'] ? Carbon::parse($data['date']) : now();

        $renewal = RecurrenceService::renewCustomerService($customerService, $data['amount'], $renewedAt);

        return response()->json([
            'message' => 'Service renewed successfully.',
            'new_due_date' => $renewal->renews_until->toDateString(),
            'service' => $customerService->fresh()
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
     * Get services due today.
     */
    public function dueToday()
    {
        $today = now()->startOfDay();

        $services = CustomerService::with(['customer', 'service', 'domain'])
            ->whereDate('next_due_date', $today)
            ->orderBy('customer_id', 'asc')
            ->get();

        if ($services->count() == 0) {
            return response()->json([
                'message' => 'No services due today.',
            ]);
        }

        $data = $services->map(function ($cs) {
            return [
                'id' => $cs->id,
                'customer_id' => $cs->customer_id,
                'customer_name' => $cs->customer->name ?? 'N/A',
                'service_id' => $cs->service_id,
                'service_name' => $cs->service->name ?? 'N/A',
                'domain_name' => $cs->domain->name ?? null,
                'price' => $cs->price,
                'due_date' => $cs->next_due_date,
                'recurrence' => $cs->recurrence,
                'status' => $cs->status,
                'company_name' => $cs->customer->company_name ?? 'N/A',
                'email' => $cs->customer->email ?? 'N/A',
                'phone' => $cs->customer->phone ?? 'N/A',
                'type' => $cs->customer->type ?? 'N/A',
                'document' => $cs->customer->document ?? 'N/A',
                'address' => $cs->customer->address ?? 'N/A',
                'city' => $cs->customer->city ?? 'N/A',
                'state' => $cs->customer->state ?? 'N/A',
                'zip_code' => $cs->customer->zip_code ?? 'N/A',
                'country' => $cs->customer->country ?? 'N/A',

            ];
        });

        return response()->json($data);
    }

    /**
     * Get overdue services based on recurrence type:
     * - monthly/yearly: next_due_date in the past
     * - one_time: no payment record exists
     */
    public function overdue()
    {
        $today = now()->startOfDay();

        // Get services with recurring billing (not one_time) where next_due_date is in the past
        $recurringOverdue = CustomerService::with(['customer', 'service', 'domain'])
            ->whereIn('recurrence', ['monthly', 'quarterly', 'semiannual', 'yearly'])
            ->where('next_due_date', '<', $today)
            ->orderBy('next_due_date', 'asc')
            ->get();

        // Get one_time services without payment record
        $oneTimeServices = CustomerService::with(['customer', 'service', 'domain'])
            ->where('recurrence', 'one_time')
            ->where('next_due_date', '<', $today)
            ->get();

        $oneTimeOverdue = $oneTimeServices->filter(function ($cs) {
            $hasPayment = Payment::where('customer_service_id', $cs->id)->exists();
            return !$hasPayment;
        });

        // Merge both collections
        $services = $recurringOverdue->concat($oneTimeOverdue);

        if ($services->count() == 0) {
            return response()->json([
                'message' => 'No overdue services.',
            ]);
        }

        $data = $services->map(function ($cs) {
            $daysOverdue = $cs->next_due_date->diffInDays(now());
            return [
                'id' => $cs->id,
                'customer_id' => $cs->customer_id,
                'customer_name' => $cs->customer->name ?? 'N/A',
                'company_name' => $cs->customer->company_name ?? 'N/A',
                'service_id' => $cs->service_id,
                'service_name' => $cs->service->name ?? 'N/A',
                'domain_name' => $cs->domain->name ?? null,
                'price' => $cs->price,
                'due_date' => $cs->next_due_date,
                'days_overdue' => $daysOverdue,
                'recurrence' => $cs->recurrence,
                'email' => $cs->customer->email ?? 'N/A',
                'phone' => $cs->customer->phone ?? 'N/A',
                'type' => $cs->customer->type ?? 'N/A',
                'document' => $cs->customer->document ?? 'N/A',
                'address' => $cs->customer->address ?? 'N/A',
                'city' => $cs->customer->city ?? 'N/A',
                'state' => $cs->customer->state ?? 'N/A',
                'zip_code' => $cs->customer->zip_code ?? 'N/A',
                'country' => $cs->customer->country ?? 'N/A',
            ];
        });

        return response()->json($data->values());
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
