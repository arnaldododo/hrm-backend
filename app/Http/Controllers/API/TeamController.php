<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $teamQuery = Team::query();

        if ($id) {
            $team = $teamQuery->find($id);

            if ($team) {
                return ResponseFormatter::sendResponse($team, 'Team is found.');
            }
            return ResponseFormatter::sendError('Company is not found.', 404);
        }

        $teams = $teamQuery;
        if ($name) {
            $teams->where('name', 'like', '%' . $name . '%')->paginate($limit);
        }
        return ResponseFormatter::sendResponse($teams, 'Companies are found.');
    }

    public function create(CreateTeamRequest $request)
    {
        try {
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            $team = Team::create([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id
            ]);

            if (!$team) {
                throw new Exception('Failed to create company.');
            }

            return ResponseFormatter::sendResponse($team, 'Team successfully created.');
        } catch (Exception $e) {
            return ResponseFormatter::sendResponse($e->getMessage(), 500);
        }
    }

    public function update(UpdateTeamRequest $request, $id)
    {

        try {
            $team = Team::find($id);

            if (!$team) {
                throw new Exception('Team not found');
            }

            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            $team->update([
                'name' => $request->name,
                'icon' => isset($path) ? $path : $team->icon,
                'company_id' => $request->company_id,
            ]);

            return ResponseFormatter::sendResponse($team, 'Team updated');
        } catch (Exception $e) {
            return ResponseFormatter::sendError($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $team = Team::find($id);

            if (!$team) {
                throw new Exception('Team not found.');
            }

            $team->delete();

            return ResponseFormatter::sendResponse('Team deleted.');
        } catch (Exception $e) {
            return ResponseFormatter::sendError($e->getMessage(), 500);
        }
    }
}
