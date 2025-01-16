<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;

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
     * Returns an error json object if provided token is invalid or expired
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    #[ExcludeRouteFromDocs]
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
     * Authenticate a user and generate a JWT token.
     *
     * This method validates the login credentials, attempts authentication,
     * and returns a JSON response with the user details and JWT token on success.
     *
     * @param Request $request The HTTP request object containing login credentials.
     *                         Expected to have 'email' and 'password' fields.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response with the following possible structures:
     *         - On validation failure:
     *           {
     *             "status": "error",
     *             "message": "Login fields validation failed",
     *             "fields": {validation_errors}
     *           }
     *           (HTTP status code: 422)
     *         - On authentication failure:
     *           {
     *             "status": "error",
     *             "message": "Wrong login credentials"
     *           }
     *           (HTTP status code: 401)
     *         - On success:
     *           {
     *             "status": "success",
     *             "user": {user_object},
     *             "authorisation": {
     *               "token": "JWT_token",
     *               "type": "bearer"
     *             }
     *           }
     *           (HTTP status code: 200)
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
     * Register a new client user.
     *
     * This method validates the registration data, creates a new user,
     * logs them in, and returns a JSON response with the user details and JWT token.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object containing registration data.
     *                                 Expected to have 'name', 'email', and 'password' fields.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response with the following possible structures:
     *         - On validation failure:
     *           {
     *             "status": "error",
     *             "message": "Registration fields validation failed",
     *             "fields": {validation_errors}
     *           }
     *           (HTTP status code: 422)
     *         - On success:
     *           {
     *             "status": "success",
     *             "message": "User created successfully",
     *             "user": {user_object},
     *             "authorisation": {
     *               "token": "JWT_token",
     *               "type": "bearer"
     *             }
     *           }
     *           (HTTP status code: 200)
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
     * Log out the authenticated user.
     *
     * This method logs out the currently authenticated user by invalidating their session
     * and revoking their authentication token. It then returns a JSON response indicating
     * the successful logout.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response with the following structure:
     *         {
     *           "status": "success",
     *           "message": "Successfully logged out"
     *         }
     *         (HTTP status code: 200)
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
     * This method refreshes the user's authentication token by generating a new one.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response with the following structure:
     *           {
     *             "status": "success",
     *             "user": {user_object},
     *             "authorisation": {
     *               "token": "JWT_token",
     *               "type": "bearer"
     *             }
     *           }
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
