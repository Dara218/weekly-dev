<?php

namespace App\Http\Controllers\Parents;

use App\Http\Controllers\Controller;
use App\Interfaces\ParentInterface;
use App\Services\Common\LogService;
use Symfony\Component\HttpFoundation\Response;

class GetParentsController extends Controller
{
    /**
     * Initialize ParentInterface instance.
     *
     * @var \App\Interfaces\ParentInterface $parentInterface
     */
    protected ParentInterface $parentInterface;

    /**
     * Initialize classes.
     *
     * @param \App\Interfaces\ParentInterface $parentInterface
     */
    public function __construct(ParentInterface $parentInterface)
    {
        $this->parentInterface = $parentInterface;
    }

    /**
     * Get all parents.
     *
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        try {
            $parents = $this->parentInterface
                ->all()
                ->load('user');

            return response([
                'succses' => true,
                'data' => $parents,
            ]);
        } catch (\Exception $error) {
            LogService::error('Error fetching parents data.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            return response([
                'succses' => false,
                'message' => 'Internal server error. Try again later.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
