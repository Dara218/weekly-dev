<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Common\LogService;
use App\Services\Payment\{
    StripeCheckoutGateway,
    SyncStripeInvoiceService,
};
use Illuminate\Http\{
    Request,
    Response,
};
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Invoice as StripeInvoice;

class HandleStripeWebhookController extends Controller
{
    /**
     * Initialize the Stripe webhook controller.
     *
     * @param \App\Services\Payment\StripeCheckoutGateway $gateway The Stripe gateway.
     * @param \App\Services\Payment\SyncStripeInvoiceService $invoiceService The invoice sync service.
     */
    public function __construct(
        private readonly StripeCheckoutGateway $gateway,
        private readonly SyncStripeInvoiceService $invoiceService,
    ) {
    }

    /**
     * Verify and process an incoming Stripe webhook.
     *
     * @param \Illuminate\Http\Request $request The incoming webhook request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request): Response
    {
        $signature = $request->header('Stripe-Signature');

        if (!is_string($signature) || $signature === '') {
            return response('Missing signature.', Response::HTTP_BAD_REQUEST);
        }

        try {
            $event = $this->gateway->constructWebhookEvent(
                $request->getContent(),
                $signature,
            );
        } catch (\UnexpectedValueException | SignatureVerificationException $error) {
            LogService::error('Invalid Stripe webhook.', [
                'error' => $error->getMessage(),
            ]);

            return response('Invalid payload.', Response::HTTP_BAD_REQUEST);
        }

        try {
            match ($event->type) {
                Event::INVOICE_CREATED,
                Event::INVOICE_FINALIZED,
                Event::INVOICE_UPDATED,
                Event::INVOICE_PAID,
                Event::INVOICE_PAYMENT_SUCCEEDED => $this->handleInvoice($event),
                default => null,
            };
        } catch (\Throwable $error) {
            LogService::error('Error handling Stripe webhook.', [
                'event_id' => $event->id,
                'event_type' => $event->type,
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            return response('Webhook handling failed.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response('OK', Response::HTTP_OK);
    }

    /**
     * Mirror a Stripe Invoice into the local invoices table.
     *
     * @param \Stripe\Event $event The verified Stripe event.
     *
     * @return void
     */
    private function handleInvoice(Event $event): void
    {
        /** @var StripeInvoice $invoice */
        $invoice = $event->data->object;
        $this->invoiceService->syncById((string) $invoice->id);
    }
}
