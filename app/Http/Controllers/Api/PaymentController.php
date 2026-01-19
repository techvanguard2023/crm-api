<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerService;
use App\Models\Payment;
use App\Models\ServiceRenewal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Store a payment request (generate boleto/pix info).
     * This is separate from 'renew' which was immediate.
     * Use this if you want to store the barcode BEFORE payment.
     */
    public function store(Request $request, $id)
    {
        $customerService = CustomerService::findOrFail($id);

        $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'request_id' => 'required|string', // codigoSolicitacao
            'barcode' => 'nullable|string',
            'pix_copy_paste' => 'nullable|string',
            'status' => 'nullable|string',
            // Add other fields as necessary
        ]);
        
        $payment = new Payment();
        $payment->customer_service_id = $customerService->id;
        $payment->fill([
             'request_id' => $validatedData['request_id'],
             'amount' => $validatedData['amount'],
             'status' => $validatedData['status'] ?? 'PENDING',
             'barcode' => $validatedData['barcode'] ?? null,
             'pix_copy_paste' => $validatedData['pix_copy_paste'] ?? null,
        ]);
        $payment->save();

        return response()->json($payment, 201);
    }
    
    /**
     * Webhook/Callback to update payment status.
     * Or a manual update endpoint.
     */
    public function update(Request $request)
    {
        // Assuming the bank sends the body structure provided by user
        // We find by request_id (codigoSolicitacao)
        
        $data = $request->validate([
            'codigoSolicitacao' => 'required|string',
            'situacao' => 'required|string',
            'valorTotalRecebido' => 'nullable|numeric', // String in example, but castable
            'dataHoraSituacao' => 'nullable|string',
            // ... capture other fields to update
            'seuNumero' => 'nullable|string',
            'origemRecebimento' => 'nullable|string',
            'nossoNumero' => 'nullable|string',
            'codigoBarras' => 'nullable|string',
            'linhaDigitavel' => 'nullable|string',
            'txid' => 'nullable|string',
            'pixCopiaECola' => 'nullable|string',
        ]);
        
        $payment = Payment::where('request_id', $data['codigoSolicitacao'])->firstOrFail();
        
        // Update payment details
        $payment->update([
            'status' => $data['situacao'],
            'amount' => $data['valorTotalRecebido'] ?? $payment->amount,
            'paid_at' => isset($data['dataHoraSituacao']) ? Carbon::parse($data['dataHoraSituacao']) : null,
            'your_number' => $data['seuNumero'] ?? $payment->your_number,
            'payment_method' => $data['origemRecebimento'] ?? $payment->payment_method,
            'our_number' => $data['nossoNumero'] ?? $payment->our_number,
            'barcode' => $data['codigoBarras'] ?? $payment->barcode,
            'digitable_line' => $data['linhaDigitavel'] ?? $payment->digitable_line,
            'txid' => $data['txid'] ?? $payment->txid,
            'pix_copy_paste' => $data['pixCopiaECola'] ?? $payment->pix_copy_paste,
        ]);
        
        // Check if status is RECEBIDO and not processed yet (we could add a 'processed' flag or check if renewal exists)
        if ($data['situacao'] === 'RECEBIDO') {
             $this->processRenewal($payment);
        }
        
        return response()->json(['message' => 'Payment updated successfully']);
    }

    private function processRenewal(Payment $payment)
    {
        $customerService = $payment->customerService;
        
        // Avoid duplicate renewals for the same payment
        // (Logic depends on business rules, maybe we check if a renewal exists linked to this payment? 
        //  Our ServiceRenewal doesn't have payment_id yet, but we can infer or add it.
        //  For now, let's just proceed assuming the webhook comes ONCE or update is idempotent enough)
        
        // Calculate next date (reuse logic? extract to service?)
        $baseDate = $customerService->next_due_date ?? $customerService->start_date ?? now();
        $baseDate = Carbon::parse($baseDate);
         
        // If the payment date is significantly after next_due_date, maybe we should base it on payment date?
        // But usually sticking to cycle is safer.
        
        $newDueDate = $baseDate->copy();
        
        switch ($customerService->recurrence) {
            case 'monthly': $newDueDate->addMonth(); break;
            case 'quarterly': $newDueDate->addMonths(3); break;
            case 'semiannual': $newDueDate->addMonths(6); break;
            case 'yearly': $newDueDate->addYear(); break;
            default: $newDueDate->addMonth(); break;
        }

        ServiceRenewal::create([
            'customer_service_id' => $customerService->id,
            'amount' => $payment->amount,
            'renewed_at' => $payment->paid_at ?? now(),
            'renews_until' => $newDueDate
        ]);
        
        $customerService->update(['next_due_date' => $newDueDate]);
    }
}
