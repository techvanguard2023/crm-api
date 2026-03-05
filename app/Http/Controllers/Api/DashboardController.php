<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\CustomerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get consolidated metrics for the dashboard.
     */
    public function index()
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfYear = $now->copy()->startOfYear();
        $endOfYear = $now->copy()->endOfYear();

        // 1. Basic Counts
        $totalCustomers = Customer::count();
        $totalDomains = Domain::count();
        $totalActiveServices = CustomerService::count();

        // 2. Financial Metrics & Annual Projection
        $services = CustomerService::all();
        
        $monthlyTotals = array_fill(1, 12, 0);
        $totalYearlyProjection = 0;
        $currentMonth = $now->month;
        $currentYear = $now->year;

        foreach ($services as $service) {
            $price = (float) $service->price;
            $recurrence = $service->recurrence;
            $dueDate = Carbon::parse($service->next_due_date);

            // We want to project all occurrences in the current year
            // Even if the next_due_date is in the past, or in the future
            
            // Start from the first occurrence in the current year
            $tempDate = $dueDate->copy();
            
            // Go back to the first possible date in the current year based on recurrence
            while ($tempDate->year > $currentYear) {
                $tempDate = $this->subtractRecurrence($tempDate, $recurrence);
            }
            while ($tempDate->year < $currentYear) {
                $tempDate = $this->addRecurrence($tempDate, $recurrence);
            }

            // Now project forward within the current year
            while ($tempDate->year == $currentYear) {
                $monthlyTotals[$tempDate->month] += $price;
                $totalYearlyProjection += $price;
                $tempDate = $this->addRecurrence($tempDate, $recurrence);
                
                // Safety break for unknown recurrences that don't advance date
                if ($tempDate->year == $currentYear && $recurrence == 'one_time') break;
            }
        }

        $monthNames = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];

        $annualProjection = [];
        for ($i = 1; $i <= 12; $i++) {
            $annualProjection[] = [
                'month_number' => $i,
                'month_name' => $monthNames[$i],
                'total' => number_format($monthlyTotals[$i], 2, '.', '')
            ];
        }

        return response()->json([
            'counts' => [
                'total_customers' => $totalCustomers,
                'total_domains' => $totalDomains,
                'total_active_services' => $totalActiveServices,
            ],
            'financial' => [
                'to_receive_current_month' => number_format($monthlyTotals[$currentMonth], 2, '.', ''),
                'to_receive_current_year' => number_format($totalYearlyProjection, 2, '.', ''),
            ],
            'annual_projection' => $annualProjection
        ]);
    }

    private function addRecurrence(Carbon $date, $recurrence)
    {
        $newDate = $date->copy();
        switch ($recurrence) {
            case 'monthly': return $newDate->addMonth();
            case 'quarterly': return $newDate->addMonths(3);
            case 'semiannual': return $newDate->addMonths(6);
            case 'yearly': return $newDate->addYear();
            default: return $newDate->addMonth(); // Fallback
        }
    }

    private function subtractRecurrence(Carbon $date, $recurrence)
    {
        $newDate = $date->copy();
        switch ($recurrence) {
            case 'monthly': return $newDate->subMonth();
            case 'quarterly': return $newDate->subMonths(3);
            case 'semiannual': return $newDate->subMonths(6);
            case 'yearly': return $newDate->subYear();
            default: return $newDate->subMonth(); // Fallback
        }
    }
}
