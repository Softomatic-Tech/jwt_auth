<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserAuthentication;
use App\Models\UserDetails; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;


/**

 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     in="header",
 *     name = "bearerAuth"
 * 
 * )

 */

class AuthController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

/**
 * @OA\Post(
 *     path="/api/login",
 *     summary="Log in a user",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="phone", type="string", format="numeric", example="1234567890"),
 *             @OA\Property(property="password", type="string", format="password", example="password")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful login",
 *         @OA\JsonContent(
 *             @OA\Property(property="access_token", type="string"),
 *             @OA\Property(property="token_type", type="string", example="bearer"),
 *             @OA\Property(property="expires_in", type="integer"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Email or phone number is required.")
 *         )
 *     )
 * )
 */
    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => [
            'sometimes',
            'required',
            'email',
        ],
        'phone' => [
            'sometimes',
            'required',
            'string',
            'numeric',
            'digits:10',
        ],
        'password' => 'required|string|min:6',
    ]);
    
    $email = $request->input('email');
    $phone = $request->input('phone');
    $password = $request->input('password');
    
    if (empty($email) && empty($phone)) {
        return response()->json(['error' => 'Email or phone number is required.'], 422);
    }
    
    if (!empty($email)) {
        $credentials = ['email' => $email, 'password' => $password];
    } else {
        $credentials = ['phone_no' => $phone, 'password' => $password];
    }
    
    if (! $token = auth('api')->attempt($credentials)) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    return $this->createNewToken($token);
}
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a User",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *             @OA\Property(property="phone_no", type="string", example="1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User successfully registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User successfully registered"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *     )
     * )
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:user_authentication',
            'password' => 'required|string|min:6',
            'phone_no' => [
                'required',
                'string',
                'numeric',
                'digits:10', // Validates that the phone_no has exactly 10 digits
            ],
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Create a new user in the User Authentication table
        $userAuth = UserAuthentication::create([
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'phone_no' => $request->input('phone_no'),
        ]);


    
        // Extract the ID of the newly created UserAuthentication record
        Log::debug('User Authentication Object:', ['userAuth1' => $userAuth]);

        

        $data = json_decode($userAuth);
        Log::debug('data', ['data' => $data]);

        $id = $data->id;
      
        Log::debug('User Authentication ID:', ['id' => $id]);
        


    
        // Create a new user in the User Details table and associate it with User Authentication
        $userDetails = UserDetails::create([
            'user_authentication_id' => $id, // Associate with UserAuthentication
            'name' => $request->input('name'),
            'other_details_column1' => '', // You can set default values for other details columns here
            'other_details_column2' => '',
            'other_details_column3' => '',
        ]);

        Log::debug('User Details Object:', ['userAuth' => $userDetails]);





        // Associate the User Authentication and User Details records
        $userDetails->userAuthentication()->associate($userAuth);
        $userDetails->save();

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $userAuth,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Log the user out (Invalidate the token)",
     *     tags={"Authentication"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(
     *         response=200,
     *         description="User successfully logged out",
     *     )
     * )
     */
    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Refresh a token",
     *     tags={"Authentication"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(
     *         response=200,
     *         description="Token successfully refreshed",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer"),
     *         )
     *     )
     * )
     */
    public function refresh() {
        return $this->createNewToken(auth::refresh());
    }

    /**
     * @OA\Get(
     *     path="/api/user-profile",
     *     summary="Get the authenticated User",
     *     tags={"Authentication"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(
     *         )
     *     )
     * )
     */
    public function userProfile() {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth::factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
