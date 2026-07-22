<?php

namespace App\Services;

use App\Models\CustomerService;
use App\Models\ServiceRenewal;
use Carbon\Carbon;

class RecurrenceService
{
    public const VALID_RECURRENCES = ['monthly', 'quarterly', 'semiannual', 'yearly', 'one_time'];

    public static function addRecurrence(Carbon $date, string $recurrence): Carbon
    {
        $newDate = $date->copy();
        switch ($recurrence) {
            case 'monthly':
                return $newDate->addMonth();
            case 'quarterly':
                return $newDate->addMonths(3);
            case 'semiannual':
                return $newDate->addMonths(6);
            case 'yearly':
                return $newDate->addYear();
            case 'one_time':
                return $newDate;
            default:
                return $newDate->addMonth();
        }
    }

    public static function subtractRecurrence(Carbon $date, string $recurrence): Carbon
    {
        $newDate = $date->copy();
        switch ($recurrence) {
            case 'monthly':
                return $newDate->subMonth();
            case 'quarterly':
                return $newDate->subMonths(3);
            case 'semiannual':
                return $newDate->subMonths(6);
            case 'yearly':
                return $newDate->subYear();
            case 'one_time':
                return $newDate;
            default:
                return $newDate->subMonth();
        }
    }

    public static function nextDueDate(Carbon $baseDate, string $recurrence): Carbon
    {
        return self::addRecurrence($baseDate->copy(), $recurrence);
    }

    /**
     * Register a renewal for a CustomerService: creates the ServiceRenewal
     * history record and advances next_due_date. Shared by manual renewal
     * (CustomerServiceController::renew) and the payment webhook
     * (PaymentController::processRenewal) so both stay in sync.
     */
    public static function renewCustomerService(CustomerService $customerService, float $amount, ?Carbon $renewedAt = null): ServiceRenewal
    {
        $renewedAt ??= now();

        $baseDate = $customerService->next_due_date ?? $customerService->start_date ?? now();
        $baseDate = Carbon::parse($baseDate);

        $newDueDate = self::addRecurrence($baseDate, $customerService->recurrence);

        $renewal = ServiceRenewal::create([
            'customer_service_id' => $customerService->id,
            'amount' => $amount,
            'renewed_at' => $renewedAt,
            'renews_until' => $newDueDate,
        ]);

        $customerService->update(['next_due_date' => $newDueDate]);

        return $renewal;
    }
}
