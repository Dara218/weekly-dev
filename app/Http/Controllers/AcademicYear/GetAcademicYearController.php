<?php

namespace App\Http\Controllers\AcademicYear;

use App\Http\Controllers\Controller;
use App\Interfaces\AcademicYearInterface;
use App\Services\Common\LogService;
use Symfony\Component\HttpFoundation\Response;

class GetAcademicYearController extends Controller
{
    /**
     * Get all academic year data.
     *
     * @return \Illuminate\Http\Response
     */
    public function get(): Response
    {
        try {
            $data = app(AcademicYearInterface::class)->all();

            return response([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $error) {
            LogService::error('Error fetching academic year data.', [
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
