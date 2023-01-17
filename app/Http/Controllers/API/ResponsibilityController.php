<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\ApiController;
use App\Http\Requests\CreateResponsibilityRequest;
use App\Models\Responsibility;
use Exception;
use Illuminate\Http\Request;

class ResponsibilityController extends ApiController
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $responsibilityQuery = Responsibility::query();

        if ($id) {
            $responsibility = $responsibilityQuery->find($id);

            if ($responsibility) {
                return $this->successResponse($responsibility, 'Responsibility found');
            }

            return $this->errorResponse('Responsibility not found', 404);
        }

        $responsibilities = $responsibilityQuery->where('role_id', $request->role_id);

        if ($name) {
            $responsibilities->where('name', 'like', '%' . $name . '%')->paginate($limit);
        }

        return $this->successResponse($responsibilities, 'Responsibilities found');
    }

    public function create(CreateResponsibilityRequest $request)
    {
        try {
            // Create responsibility
            $responsibility = Responsibility::create([
                'name' => $request->name,
                'role_id' => $request->role_id,
            ]);

            if (!$responsibility) {
                throw new Exception('Responsibility not created');
            }

            return $this->successResponse($responsibility, 'Responsibility created');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $responsibility = Responsibility::find($id);

            if (!$responsibility) {
                throw new Exception('Responsibility not found');
            }

            $responsibility->delete();

            return $this->successResponse('Responsibility deleted');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
