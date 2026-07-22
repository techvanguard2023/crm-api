<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentCallbackRequest;
use App\Http\Resources\PaymentResource;
use App\Models\CustomerService;
use App\Models\Payment;
use App\Services\RecurrenceService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * List all payments with service details
     */
    public function index()
    {
        $payments = Payment::with(['customerService.service'])->get();

        return PaymentResource::collection($payments);
    }

    /**
     * Store a payment request (generate boleto/pix info).
     * This is separate from 'renew' which was immediate.
     * Use this if you want to store the barcode BEFORE payment.
     */
    public function store(StorePaymentRequest $request, $id)
    {
        $customerService = CustomerService::findOrFail($id);
        $data = $request->validated();

        $payment = new Payment();
        $payment->customer_service_id = $customerService->id;
        $payment->fill([
            'request_id' => $data['request_id'],
            'amount' => $data['amount'],
            'status' => $data['status'] ?? 'PENDING',
            'barcode' => $data['barcode'] ?? null,
            'pix_copy_paste' => $data['pix_copy_paste'] ?? null,
        ]);
        $payment->save();

        return response()->json($payment, 201);
    }

    /**
     * Webhook/Callback to update payment status.
     * Or a manual update endpoint.
     */
    public function update(UpdatePaymentCallbackRequest $request)
    {
        $data = $request->validated();

        $payment = Payment::where('request_id', $data['codigoSolicitacao'])->firstOrFail();

        $wasAlreadySettled = $this->isSettledStatus($payment->status);

        // Update payment details
        $payment->update([
            'status' => $data['situacao'],
            'amount' => $data['valorTotalRecebido'] ?? $payment->amount,
            'paid_at' => isset($data['dataHoraSituacao']) ?Carbon::parse($data['dataHoraSituacao']) : null,
            'your_number' => $data['seuNumero'] ?? $payment->your_number,
            'payment_method' => $data['origemRecebimento'] ?? $payment->payment_method,
            'our_number' => $data['nossoNumero'] ?? $payment->our_number,
            'barcode' => $data['codigoBarras'] ?? $payment->barcode,
            'digitable_line' => $data['linhaDigitavel'] ?? $payment->digitable_line,
            'txid' => $data['txid'] ?? $payment->txid,
            'pix_copy_paste' => $data['pixCopiaECola'] ?? $payment->pix_copy_paste,
        ]);

        // Only trigger a renewal on the transition into a settled status - if the
        // payment was already settled before this callback, the gateway is just
        // resending/retrying and we must not double-renew.
        if (!$wasAlreadySettled && $this->isSettledStatus($data['situacao'])) {
            $this->processRenewal($payment);
        }

        return response()->json(['message' => 'Payment updated successfully']);
    }

    private function isSettledStatus(?string $status): bool
    {
        return in_array($status, ['RECEBIDO', 'CONFIRMADO', 'MARCADO_RECEBIDO', 'PAGO', 'LIQUIDADO'], true);
    }

    private function processRenewal(Payment $payment)
    {
        $customerService = $payment->customerService;

        // Skip renewal for one_time services
        if ($customerService->recurrence === 'one_time') {
            return;
        }

        RecurrenceService::renewCustomerService($customerService, $payment->amount, $payment->paid_at);
    }
    public function getCustomerByRequestId($requestId)
    {
        $payment = Payment::where('request_id', $requestId)->with('customerService')->firstOrFail();

        // As CustomerService is a pivot, we can access customer_id directly
        $customer = \App\Models\Customer::findOrFail($payment->customerService->customer_id);

        return response()->json($customer);
    }

    /**
     * Get payment details by request_id
     * Returns payment data including service name and due date
     */
    public function getByRequestId($requestId)
    {
        $payment = Payment::where('request_id', $requestId)
            ->with(['customerService.service'])
            ->firstOrFail();

        return new PaymentResource($payment);
    }
}
