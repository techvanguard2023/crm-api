<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdatePaymentCallbackRequest
 *
 * Validates incoming webhook/callback data from payment gateway providers.
 * External payment processors send status updates using gateway-specific field names.
 *
 * Validation Rules (Gateway Field Names):
 * - codigoSolicitacao: Required, string (request ID from payment gateway)
 * - situacao: Required, string (payment status: RECEBIDO, CONFIRMADO, PAGO, LIQUIDADO, etc.)
 * - valorTotalRecebido: Optional, numeric (total amount received)
 * - dataHoraSituacao: Optional, timestamp (when status changed)
 * - seuNumero: Optional, string (payment reference number)
 * - origemRecebimento: Optional, string (payment method used)
 * - nossoNumero: Optional, string (our reference number)
 * - codigoBarras: Optional, string (boleto barcode)
 * - linhaDigitavel: Optional, string (boleto digitable line)
 * - txid: Optional, string (Pix transaction ID)
 * - pixCopiaECola: Optional, string (Pix copy-paste code)
 */
class UpdatePaymentCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigoSolicitacao' => 'required|string',
            'situacao' => 'required|string',
            'valorTotalRecebido' => 'nullable|numeric',
            'dataHoraSituacao' => 'nullable|date',
            'seuNumero' => 'nullable|string',
            'origemRecebimento' => 'nullable|string',
            'nossoNumero' => 'nullable|string',
            'codigoBarras' => 'nullable|string',
            'linhaDigitavel' => 'nullable|string',
            'txid' => 'nullable|string',
            'pixCopiaECola' => 'nullable|string',
        ];
    }
}
