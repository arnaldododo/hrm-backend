<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\ApiController;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;

class RoleController extends ApiController
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $with_responsibility = $request->input('with_responsibilities', false);

        $roleQuery = Role::query();

        if ($id) {
            $role = $roleQuery->with('responsibilities')->find($id);

            if ($role) {
                return $this->successResponse($role, 'Role found');
            }
            return $this->errorResponse('Role not found', 404);
        }

        $roles = $roleQuery->where('company_id', $request->company_id);

        if ($name) {
            $roles->where('name', 'like', '%' . $name . '%');
        }

        if ($with_responsibility) {
            $roles->with('responsibilities');
        }

        return $this->successResponse($roles->paginate($limit), 'Roles found');
    }

    public function create(CreateRoleRequest $request)
    {
        try {
            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            if (!$role) {
                throw new Exception('Role not created');
            }

            return $this->successResponse($role, 'Role created');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                throw new Exception('Role not found');
            }

            $role->update([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            return $this->successResponse($role, 'Role updated');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                throw new Exception('Role not found');
            }

            $role->delete();

            return $this->successResponse('Role deleted');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
