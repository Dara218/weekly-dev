<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Common\LogService;
use App\Services\Payment\PayOverdueInvoicesService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
    JsonResponse,
    Request,
};
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class PayOverdueInvoicesController extends Controller
{
    /**
     * Initialize the pay-overdue controller.
     *
     * @param \App\Services\Payment\PayOverdueInvoicesService $service The service.
     */
    public function __construct(
        private readonly PayOverdueInvoicesService $service,
    ) {
    }

    /**
     * Return a Stripe hosted URL for the student's overdue invoices.
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
            $result = $this->service->handle(
                $user,
                $request->integer('student_id') ?: null,
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (ValidationException $error) {
            throw $error;
        } catch (AuthorizationException $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        } catch (\Throwable $error) {
            LogService::error('Error starting overdue invoice payment.', [
                'user_id' => $user->id,
                'error' => $error->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error. Try again later.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
