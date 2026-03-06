<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Expense;
use App\Models\ServiceRenewal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CustomerService;

class DashboardController extends Controller
{
    /**
     * Get consolidated metrics for the dashboard.
     */
    public function index()
    {
        $now = now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // 1. Basic Counts
        $totalCustomers = Customer::count();
        $totalDomains = Domain::count();
        $totalActiveServices = CustomerService::count();

        // 2. Financial Metrics & Annual Projection (Revenue)
        $services = CustomerService::all();
        
        $monthlyRevenueTotals = array_fill(1, 12, 0.0);
        $totalYearlyRevenueProjection = 0.0;

        foreach ($services as $service) {
            $price = (float) $service->price;
            $recurrence = $service->recurrence;
            $dueDate = Carbon::parse($service->next_due_date);

            $tempDate = $dueDate->copy();
            
            while ($tempDate->year > $currentYear) {
                $tempDate = $this->subtractRecurrence($tempDate, $recurrence);
            }
            while ($tempDate->year < $currentYear) {
                $tempDate = $this->addRecurrence($tempDate, $recurrence);
            }

            while ($tempDate->year == $currentYear) {
                $monthlyRevenueTotals[$tempDate->month] += $price;
                $totalYearlyRevenueProjection += $price;
                $tempDate = $this->addRecurrence($tempDate, $recurrence);
                
                if ($tempDate->year == $currentYear && $recurrence == 'one_time') break;
            }
        }

        // 3. Expenses Metrics (Monthly and Yearly)
        $expenses = Expense::whereYear('date', $currentYear)->get();
        $monthlyExpenseTotals = array_fill(1, 12, 0.0);
        $totalYearlyExpenses = 0.0;

        foreach ($expenses as $expense) {
            $amount = (float) $expense->amount;
            $monthlyExpenseTotals[$expense->date->month] += $amount;
            $totalYearlyExpenses += $amount;
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
                'revenue' => number_format($monthlyRevenueTotals[$i], 2, '.', ''),
                'expenses' => number_format($monthlyExpenseTotals[$i], 2, '.', ''),
                'net' => number_format($monthlyRevenueTotals[$i] - $monthlyExpenseTotals[$i], 2, '.', '')
            ];
        }

        $grossMonth = $monthlyRevenueTotals[$currentMonth];
        $expensesMonth = $monthlyExpenseTotals[$currentMonth];
        $netMonth = $grossMonth - $expensesMonth;

        $grossYear = $totalYearlyRevenueProjection;
        $expensesYear = $totalYearlyExpenses;
        $netYear = $grossYear - $expensesYear;

        return response()->json([
            'counts' => [
                'total_customers' => $totalCustomers,
                'total_domains' => $totalDomains,
                'total_active_services' => $totalActiveServices,
            ],
            'financial' => [
                'gross_month' => number_format($grossMonth, 2, '.', ''),
                'expenses_month' => number_format($expensesMonth, 2, '.', ''),
                'net_month' => number_format($netMonth, 2, '.', ''),
                'gross_year' => number_format($grossYear, 2, '.', ''),
                'expenses_year' => number_format($expensesYear, 2, '.', ''),
                'net_year' => number_format($netYear, 2, '.', ''),
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
