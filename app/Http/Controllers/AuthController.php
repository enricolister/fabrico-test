<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /**
     * Create a new AuthController instance
     * Inside it it's attached auth:api middleware to prevent unauthorized use of its methods
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','tokenInvalid']]);
    }

    /**
     * Returns a JWT token json object if provided token is invalid or expired
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tokenInvalid(Request $request)
    {
        $responseBody = [
            'status' => 'error',
            'message' => 'Invalid or expired token',
        ];
        LogController::saveLog('auth','tokenInvalid',json_encode($responseBody));
        return response()->json($responseBody, 401);
    }

    /**
     * Login the client
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            $responseBody = [
                'status' => 'error',
                'message' => 'Login fields validation failed',
                'fields' => $validator->errors()
            ];
            LogController::saveLog('auth','login',json_encode($responseBody));
            return response()->json($responseBody, 422);
        }

        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            $responseBody = [
                'status' => 'error',
                'message' => 'Wrong login credentials',
            ];
            LogController::saveLog('auth','login',json_encode($responseBody));
            return response()->json($responseBody, 401);
        }

        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);

    }

    /**
     * Register a new client
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            $responseBody = [
                'status' => 'error',
                'message' => 'Registration fields validation failed',
                'fields' => $validator->errors()
            ];
            LogController::saveLog('auth','register',json_encode($responseBody));
            return response()->json($responseBody, 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * Logout the client
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Refresh the user token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
