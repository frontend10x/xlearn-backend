<?php

namespace App\Http\Controllers\Ingreso;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


/**
* @OA\Info(title="API's Xlearn", version="1.0")
*
* @OA\Server(url="http://127.0.0.1:8000")
*
* @OAS\SecurityScheme(
*      securityScheme="bearer_token",
*      type="http",
*      scheme="bearer"
* )
*/
class LoginController extends Controller
{
    /**
    * @OA\Post(
    *     path="/api/v1/login",
    *     tags={"Auth"},
    *     summary="Ingreso de usuarios",
    *     security={{"bearer_token":{}}},
    *     @OA\Parameter(name="email", in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="password", in="query", @OA\Schema(type="password")),
    *     @OA\Response(
    *         response=200,
    *         description="Success.",
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                  example={
    *                      "message":"Acceso correcto",
    *                       "token":"",
    *                       "datosUsuario":{
    *                           "name":"",
    *                           "email":"",
    *                           "phone":"",
    *                        }
    *                 },
    *             ),
    * 
    *         ),
    *     )
    * )
    */
    public function ingreso(Request $request)
    {

        // return response()->json(
        //     [
        //         'code' => Hash::make('12')
        //     ]
        // );

        if (Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
            $user = User::where('email', $request->input('email'))->first();
            $token = $user->createToken('token_jobs' . Auth::user()->id)->accessToken;

            if (!empty($request->input('token'))) {
                $tokenCelular = $request->input('token');
                //  $this->updateToken($tokenCelular, $user->id);
            }
            return response()->json(
                [
                    'message' => "Acceso correcto", "token" => $token, "datosUsuario" => [
                        "name" => $user->name, "email" => $user->email, "phone" => $user->phone
                    ]
                ] 
            ,200);
        } else {
            return response()->json(["message" => "Datos incorrectos"],500);
        }
    }

    // public function updateToken($token, $use_id)
    // {
    //     if (!empty($token) && !empty($use_id)) {
    //         $newToken = Usuario_token::where("user_id", $use_id)->first();
    //         if (empty($newToken)) {
    //             $newToken  = new Usuario_token();
    //             $newToken->user_id = $use_id;
    //         }
    //         $newToken->token = $token;
    //         $newToken->save();
    //     }
    // }
}
