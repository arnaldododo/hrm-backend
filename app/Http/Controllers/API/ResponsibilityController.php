<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateResponsibilityRequest;
use App\Models\Responsibility;
use Exception;
use Illuminate\Http\Request;

class ResponsibilityController extends Controller
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
                return ResponseFormatter::sendResponse($responsibility, 'Responsibility found');
            }

            return ResponseFormatter::sendError('Responsibility not found', 404);
        }

        $responsibilities = $responsibilityQuery->where('role_id', $request->role_id);

        if ($name) {
            $responsibilities->where('name', 'like', '%' . $name . '%')->paginate($limit);
        }

        return ResponseFormatter::sendResponse($responsibilities, 'Responsibilities found');
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

            return ResponseFormatter::sendResponse($responsibility, 'Responsibility created');
        } catch (Exception $e) {
            return ResponseFormatter::sendError($e->getMessage(), 500);
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

            return ResponseFormatter::sendResponse('Responsibility deleted');
        } catch (Exception $e) {
            return ResponseFormatter::sendError($e->getMessage(), 500);
        }
    }
}
