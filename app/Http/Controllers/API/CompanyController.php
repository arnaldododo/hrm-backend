<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $companyQuery = Company::with(['users'])->whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        });

        if ($id) {
            $company = $companyQuery->find($id);

            if ($company) {
                return ResponseFormatter::sendResponse($company, 'Company is found.');
            }
            return ResponseFormatter::sendError('Company is not found.', 404);
        }

        $companies = $companyQuery;
        if ($name) {
            $companies->where('name', 'like', '%' . $name . '%')->paginate($limit);
        }
        return ResponseFormatter::sendResponse($companies, 'Companies are found.');
    }

    /**
     * Creating new data.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CreateCompanyRequest $request)
    {
        try {
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            $company = Company::create([
                'name' => $request->name,
                'logo' => $path
            ]);

            if (!$company) {
                throw new Exception('Failed to create company.');
            }

            $user = User::find(Auth::id());
            $user->companies->attach($company->id);

            $company->load('users');

            return ResponseFormatter::sendResponse($company, 'Company successfully created.');
        } catch (Exception $e) {
            return ResponseFormatter::sendResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompanyRequest $request, $id)
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                throw new Exception('Company is not found.');
            }

            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            $company->updated([
                'name' => $request->name,
                'logo' => isset($path) ? $path : $company->logo
            ]);
            return ResponseFormatter::sendResponse($company, 'Company has been updated.');
        } catch (Exception $e) {
            return ResponseFormatter::sendError($e->getMessage(), 500);
        }
    }
}
