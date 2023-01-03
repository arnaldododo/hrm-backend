<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Validate user log in
     *
     * @return \Illuminate\Http\Response
     */
    public function login(LoginUserRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->firstOrFail();
            if (!Hash::check($request->password, $user->password)) {
                throw new Exception('Password is invalid.');
            }

            $token = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::sendResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Login successful.');
        } catch (Exception $e) {
            return ResponseFormatter::sendError($e->getMessage());
        }
    }

    /**
     * Create a new user
     *
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterUserRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $token = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::sendResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Register successful.');
        } catch (Exception $e) {
            return ResponseFormatter::sendError($e->getMessage());
        }
    }

    /**
     * Revoke the token
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::sendResponse($token, 'Logout successful.');
    }

    /**
     * Fetch user data
     *
     * @return \Illuminate\Http\Response
     */
    public function fetch(Request $request)
    {
        $user = $request->user();

        return ResponseFormatter::sendResponse($user, 'Fetch successful.');
    }
}
