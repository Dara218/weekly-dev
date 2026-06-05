<?php

namespace App\Http\Controllers\Classes;

use App\Http\Controllers\Controller;
use App\Interfaces\ClassesInterface;
use App\Services\Common\LogService;
use Symfony\Component\HttpFoundation\Response;

class GetClassesController extends Controller
{
    /**
     * Get all classes data.
     *
     * @return \Illuminate\Http\Response
     */
    public function get(): Response
    {
        try {
            $data = app(ClassesInterface::class)->all();

            return response([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $error) {
            LogService::error('Error fetching classes.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            return response([
                'success' => false,
                'message' => 'Internal server error. Try again later.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
