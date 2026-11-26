<?php

namespace App\Http\Controllers\Parents;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Common\LogService;
use App\Services\Payment\GetParentChildrenService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
    JsonResponse,
    Request,
};
use Symfony\Component\HttpFoundation\Response;

class GetMyChildrenController extends Controller
{
    /**
     * Initialize the parent children controller.
     *
     * @param \App\Services\Payment\GetParentChildrenService $childrenService The children listing service.
     */
    public function __construct(
        private readonly GetParentChildrenService $childrenService,
    ) {
    }

    /**
     * Return the students linked to the authenticated parent.
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
            return response()->json([
                'success' => true,
                'data' => $this->childrenService->handle($user),
            ]);
        } catch (AuthorizationException $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        } catch (\Throwable $error) {
            LogService::error('Error fetching parent children.', [
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
