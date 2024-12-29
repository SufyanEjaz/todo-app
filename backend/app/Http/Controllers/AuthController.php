<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Authentication
 *
 * APIs for managing user authentication.
 */
class AuthController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    /**
     * Register a new user
     *
     * @bodyParam name string required The name of the user. Example: John Doe
     * @bodyParam email string required The email address of the user. Example: john@example.com
     * @bodyParam password string required The password of the user. Must be at least 8 characters. Example: password123
     * @bodyParam confirm_password string required Password confirmation. Must match the password.
     *
     * @response 201 {
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com"
     *   },
     *   "token": "1|abcdef123456..."
     * }
     * @response 422 {
     *   "name": ["The name field is required."],
     *   "email": ["The email field is required."],
     *   "password": ["The password must be at least 8 characters."]
     * }
     */
    public function register(RegisterRequest $request)
    {
        $user = $this->userRepository->register($request->only('name', 'email', 'password'));
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    /**
     * Login user
     *
     * @bodyParam email string required The email of the user. Example: john@example.com
     * @bodyParam password string required The password of the user. Example: password123
     *
     * @response 200 {
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com"
     *   },
     *   "token": "1|abcdef123456..."
     * }
     * @response 401 {
     *   "message": "Invalid credentials"
     * }
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $authData = $this->userRepository->login($credentials);
        if (!$authData) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json($authData, 200);
    }

    /**
     * Logout user
     *
     * @response 204 {
     *   "message": "Logged out successfully"
     * }
     */
    public function logout()
    {
        return $this->userRepository->logout();
    }
}
