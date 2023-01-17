<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\ApiController;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;

class TeamController extends ApiController
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
                return $this->successResponse($team, 'Team is found.');
            }
            return $this->errorResponse('Company is not found.', 404);
        }

        $teams = $teamQuery;
        if ($name) {
            $teams->where('name', 'like', '%' . $name . '%')->paginate($limit);
        }
        return $this->successResponse($teams, 'Companies are found.');
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

            return $this->successResponse($team, 'Team successfully created.');
        } catch (Exception $e) {
            return $this->successResponse($e->getMessage(), 500);
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

            return $this->successResponse($team, 'Team updated');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
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

            return $this->successResponse('Team deleted.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
