<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Common\LogService;
use App\Services\Payment\PayStripeInvoiceService;
use Illuminate\Http\{
    JsonResponse,
    Request,
};
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class PayStripeInvoiceController extends Controller
{
    /**
     * Initialize the pay-invoice controller.
     *
     * @param \App\Services\Payment\PayStripeInvoiceService $payInvoiceService The pay service.
     */
    public function __construct(
        private readonly PayStripeInvoiceService $payInvoiceService,
    ) {
    }

    /**
     * Return the Stripe hosted invoice URL for an unpaid mirrored invoice.
     *
     * @param \Illuminate\Http\Request $request The incoming request.
     * @param \App\Models\Invoice $invoice The local invoice.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, Invoice $invoice): JsonResponse
    {
        Gate::authorize('pay', $invoice);

        try {
            $result = $this->payInvoiceService->handle($invoice);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (ValidationException $error) {
            throw $error;
        } catch (\Throwable $error) {
            LogService::error('Error starting Stripe invoice payment.', [
                'invoice_id' => $invoice->id,
                'error' => $error->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error. Try again later.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
