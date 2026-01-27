<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Common\LogService;
use App\Services\Student\StudentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GetStudentController extends Controller
{
    /**
     * Initialize StudentService instance.
     *
     * @var \App\Services\Student\StudentService $studentService
     */
    protected StudentService $studentService;

    /**
     * Initialize classes.
     *
     * @param \App\Services\Student\StudentService $studentService
     */
    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    /**
     * Get the students data based on the search keywords.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Database\Eloquent\Collection
     */
    public function get(Request $request)
    {
        try {
            $searchKeyword = $request->all();

            // Info($searchKeyword);

            return $this->studentService->getStudents($searchKeyword);
        } catch (\Exception $error) {
            LogService::error('Error fetching students data.', [
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
