<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Interfaces\SubjectInterface;
use App\Services\Common\LogService;
use App\Services\Teacher\GetTeacherService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GetTeacherController extends Controller
{
    /**
     * Initialize GetTeacherService instance.
     *
     * @var \App\Services\Teacher\GetTeacherService $getTeacherService
     */
    protected GetTeacherService $getTeacherService;

    /**
     * Initialize classes.
     *
     * @param \App\Services\Teacher\GetTeacherService $getTeacherService
     */
    public function __construct(GetTeacherService $getTeacherService)
    {
        $this->getTeacherService = $getTeacherService;
    }

    /**
     * Get all teachers.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        Info($request->all());
        try {
            // Get all subjects
            $subjects = app(SubjectInterface::class)->all();
            $searchKeyword = $request->all();
            $teachers = $this->getTeacherService->getTeachers($searchKeyword);

            $data = [
                'teachers' => $teachers,
                'subjects' => $subjects,
            ];

            return response([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $error) {
            LogService::error('Error fetching teacher data.', [
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