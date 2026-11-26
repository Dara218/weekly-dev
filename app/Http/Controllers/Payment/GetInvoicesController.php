<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Common\LogService;
use App\Services\Payment\GetInvoicesService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
    JsonResponse,
    Request,
};
use Symfony\Component\HttpFoundation\Response;

class GetInvoicesController extends Controller
{
    /**
     * Initialize the invoice controller.
     *
     * @param \App\Services\Payment\GetInvoicesService $getInvoicesService The invoice listing service.
     */
    public function __construct(
        private readonly GetInvoicesService $getInvoicesService,
    ) {
    }

    /**
     * Return invoices visible to the authenticated payer.
     *
     * @param \Illuminate\Http\Request $request The incoming request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if(!$user instanceof User, Response::HTTP_UNAUTHORIZED);

        try {
            $result = $this->getInvoicesService->handle(
                $user,
                $request->integer(
                    'per_page',
                    (int) config('constant.stripe.invoices.default_per_page'),
                ),
                $request->integer('student_id') ?: null,
            );

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'meta' => $result['meta'],
            ]);
        } catch (AuthorizationException $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        } catch (\Throwable $error) {
            LogService::error('Error fetching invoices.', [
                'user_id' => $user->id,
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error. Try again later.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
