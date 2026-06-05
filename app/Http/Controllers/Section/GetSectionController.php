<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Interfaces\SectionInterface;
use App\Services\Common\LogService;
use Symfony\Component\HttpFoundation\Response;

class GetSectionController extends Controller
{
    /**
     * Get all section data.
     *
     * @return \Illuminate\Http\Response
     */
    public function get(): Response
    {
        try {
            $data = app(SectionInterface::class)->all();

            return response([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $error) {
            LogService::error('Error fetching section data.', [
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
