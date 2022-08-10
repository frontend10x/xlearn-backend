<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Exception;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Mail\ConfirmationRegisterRequest;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;


class UserController extends Controller
{

    public function datauser($id)
    {
        try {

            $user = User::find($id);
            if (empty($user))
                throw new Exception("No existe usuario con el id: " . $id);

            return response()->json(["user" => $user], 200);
        } catch (Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }

    /**
    * @OA\Post(
    *     path="/api/v1/user/store",
    *     tags={"Users"},
    *     summary="Crear usuarios",
    *     security={{"bearer_token":{}}},
    *     @OA\Parameter(name="type_id", required=true, in="query", @OA\Schema(type="number")),
    *     @OA\Parameter(name="rol_id", required=true, in="query", @OA\Schema(type="number")),
    *     @OA\Parameter(name="link_facebook", in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="link_google", in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="link_linkedin", in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="link_instagram", in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="name", required=true, in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="surname", in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="phone", required=true, in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="email", required=true, in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="subcompanies_id", in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="password", in="query", @OA\Schema(type="password")),
    *     @OA\Parameter(name="password_confirmation", in="query", @OA\Schema(type="password")),
    *     @OA\Response(
    *         response=200,
    *         description="Success.",
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                  example={
    *                      "message":"Registro almacenado con éxito.",
    *                      "id":0,
    *                 },
    *             ),
    * 
    *         ),
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Failed",
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                  example={
    *                      "message":"Mensaje de error",
    *                 },
    *             ),
    * 
    *         ),
    *     )
    * )
    */
    public static function store(Request $request)
    {

        try {

            $consult = User::where("email", $request->input("email"))->first();

            if (!empty($consult)) {
                throw new Exception("El usuario ya se encuentra registrado");
            }

            if ( $request->input("password") != $request->input("password_confirmation")) {
                throw new Exception("Las contraseñas no coinciden");
            }

            // Validamos los datos enviados
            $validated = $request->validate([
                'type_id' => 'required|integer',
                'rol_id' => 'required|integer',
                'password' => 'required',
                'password_confirmation' => 'required',
                'email' => 'required'
            ]);

            $dataInsert = [
                "type_id" => $request->input("type_id"), "rol_id" => $request->input("rol_id"), "link_facebook" => $request->input("link_facebook"), "link_google" => $request->input("link_google"), "link_linkedin" => $request->input("link_linkedin"), "link_instagram" => $request->input("link_instagram"), "name" => $request->input("name"), "surname" => $request->input("surname"), "phone" => $request->input("phone"), "email" => $request->input("email"), "state" => $request->input("state"), "password" => Hash::make($request->input("password"))
            ];

            if (!empty($request->input("subcompanies_id"))) {
                $dataInsert['subcompanies_id'] = $request->input("subcompanies_id");
            }

            $userCreated = User::create($dataInsert);
            $encryptedId = Crypt::encryptString($userCreated['id']);

            Mail::to($request->input("email"))->send(new ConfirmationRegisterRequest($encryptedId));

            return json_encode(["message" => "Registro almacenado con éxito", "id" => $userCreated['id']]);

        } catch (Exception $e) {

            return response()->json(["message" => $e->getMessage(), "line" => $e->getLine()], 500);
            \Log::debug('message ' . $e->getMessage());

        }
    }

    /**
    * @OA\Put(
    *     path="/api/v1/user/edit/{id}",
    *     tags={"Users"},
    *     summary="Editar usuarios",
    *     security={{"bearer_token":{}}},
    *     @OA\Parameter(name="type_id", required=true, in="query", @OA\Schema(type="number")),
    *     @OA\Parameter(name="rol_id", required=true, in="query", @OA\Schema(type="number")),
    *     @OA\Parameter(name="link_facebook", in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="link_google", in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="link_linkedin", in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="link_instagram", in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="name", required=true, in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="surname", in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="phone", required=true, in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="email", required=true, in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="subcompanies_id", in="query", @OA\Schema(type="string")),
    *     @OA\Parameter(name="password", in="query", @OA\Schema(type="password")),
    *     @OA\Response(
    *         response=200,
    *         description="Success.",
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                  example={
    *                      "message":"Usuario actualizado con éxto.",
    *                      "id":0,
    *                 },
    *             ),
    * 
    *         ),
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Failed",
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                  example={
    *                      "message":"Mensaje de error",
    *                 },
    *             ),
    * 
    *         ),
    *     )
    * )
    */
    public function edit(Request $request, $id)
    {
        try {
            $buscaActualiza = User::find($id);
            $dataUpdate = [
                "type_id" => $request->input("type_id"), "rol_id" => $request->input("rol_id"), "link_facebook" => $request->input("link_facebook"), "link_google" => $request->input("link_google"), "link_linkedin" => $request->input("link_linkedin"), "link_instagram" => $request->input("link_instagram"), "name" => $request->input("name"), "surname" => $request->input("surname"), "phone" => $request->input("phone"), "email" => $request->input("email"), "state" => $request->input("state")
            ];
            // echo $request->input("subcompanies_id");die;
            $dataUpdate['subcompanies_id'] = !empty($request->input("subcompanies_id")) ? $request->input("subcompanies_id") : null;
            if (!empty($request->input("password"))) {
                $dataUpdate['password'] = Hash::make($request->input("password"));
            }
            if (empty($buscaActualiza)) {
                throw new Exception("No existe el id: " . $id . " para ser actualizado");
            } else {
                $buscaActualiza->update($dataUpdate);
                $message = "Usuario actualizado con éxto";
            }
            return response()->json(["message" => $message], 200);
        } catch (Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }

    /**
    * @OA\Get(
    *     path="/api/v1/user/list",
    *     summary="Mostrar usuarios",
    *     tags={"Users"},
    *     security={{"bearer_token":{}}},
    *     @OA\Parameter(name="offset", in="query", @OA\Schema(type="number")),
    *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="number")),
    *     @OA\Response(
    *         response=200,
    *         description="Mostrar todos los usuarios.",
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                  example={
    *                       "response": {
    *                           "hc:length": 0,
    *                           "hc:total": 0,
    *                           "hc:offset": 0,
    *                           "hc:limit": 0,
    *                           "hc:next": "next page end-point ",
    *                           "hc:previous": "previous page end-point ",
    *                           "_rel": "users",
    *                           "_embedded": {
    *                               "users": {
    *                                   {
    *                                   "id": 0,
    *                                   "name": "",
    *                                   "lastname": "",
    *                                   "company": "",
    *                                   "email": "",
    *                                   "website": "",
    *                                   "size": 0,
    *                                   "country_id": 0,
    *                                   "content": "",
    *                                   "plan_id": 0,
    *                                   "quotas": 0,
    *                                   "observation": "",
    *                                   "created_at": "2022-06-11T23:21:42.000000Z",
    *                                   "updated_at": "2022-06-12T00:46:06.000000Z",
    *                                   "countries": {
    *                                       "id": 0,
    *                                       "name": ""
    *                                       }
    *                                   }
    *                               }
    *                           }
    *                       }
    *                 },
    *             ),
    * 
    *         ),
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Failed",
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                  example={
    *                      "message":"Mensaje de error",
    *                 },
    *             ),
    * 
    *         ),
    *     )
    * )
    */
    public function index(Request $request)
    {
        try {
            if (!empty(Auth::user()->subcompanies_id)) {
                $consult = User::where("subcompanies_id", Auth::user()->subcompanies_id)->get();
            } else {

                //TODO debe sacarse del request, por defecto el valor es uno
                $offset = $request->has('offset') ? intval($request->get('offset')) : 1;

                //TODO debe sacarse del request, por defecto el valor es 10.
                $limit = $request->has('limit') ? intval($request->get('limit')) : 10;

                $consult = User::with('roles')->limit($limit)->offset(($offset - 1) * $limit)->get()->toArray();

                $nexOffset = $offset + 1;
                $previousOffset = ($offset > 1) ? $offset - 1 : 1;
            }

            $users = array(
                "hc:length" => count($consult), //Es la longitud del array a devolver
                "hc:total"  => User::count(), //Es la longitud total de los registros disponibles en el query original,
                "hc:offset" => $offset,
                "hc:limit"  => $limit,
                "hc:next"   => server_path() . '?limit=' . $limit . '&offset=' . $nexOffset,
                "hc:previous"   => server_path() . '?limit=' . $limit . '&offset=' . $previousOffset,
                "_rel"		=> "users",
                "_embedded" => array(
                    "users" => $consult
                )
            );

            if(empty($consult))
                throw new Exception("No se encontraron usuarios");

            return response()->json(["response" => $users], 200);
        } catch (Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }

    public function changestate(Request $request, $id)
    {
        try {

            $desencryptedId = Crypt::decryptString($id);

            $buscaActualiza = User::find($desencryptedId);
            if (empty($buscaActualiza)) {
                throw new Exception("No existe el Id:" . $id . " para el cambio de estado");
            }
            $buscaActualiza->update(["state" => $request->input("state")]);
            return response()->json(["message" => "Cambio de estado correctamente"], 200);
        } catch (Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }

    public function forgotpassword(Request $request, $id)
    {
        try {
            $id = $id;
            $user = User::find($id);

            if (empty($user))
                throw new Exception("No existe usuario para modificar contraseña");

            $user->password = Hash::make($request->input("password"));
            $user->save();
            return response()->json(["message" => "Contraseña actualizada con éxito"], 200);
        } catch (Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }

    public function changepassword(Request $request)
    {
        try {

            $user = User::find(Auth::user()->id);
            $user->password =  Hash::make($request->input("password"));
            $user->save();

            return response()->json(["message" => "password modificado con éxito"], 200);
        } catch (Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }

    public function coursesFavorites(Request $request)
    {
        try {

            $course = Course::find($request->input("course_id"));
            if (empty($course))
                throw new Exception("El Id del curso no existe");

            $user = User::find(Auth::user()->id);

            $existencia = DB::table("user_course_favorite")->where("course_id",$request->input("course_id"))->first();
            if(empty($existencia)){

                $user->coursesFavorites()->attach($request->input("course_id"));
                return response()->json(["message" => "Curso favorito almacenado con éxito"], 200);
            }       else{
                return response()->json(["message" => "Curso ya se encuentra registrado como favorito"], 200);
            }

        } catch (Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }

    /**
    * @OA\Post(
    *     path="/api/v1/user/bulk_upload_users",
    *     tags={"Users"},
    *     summary="Carga masiva de usuarios",
    *     security={{"bearer_token":{}}},
    *     @OA\Parameter(name="file", required=true, in="query", @OA\Schema(type="file")),
    *     @OA\Parameter(name="subcompanies_id", required=true, in="query", @OA\Schema(type="number")),
    *     @OA\Response(
    *         response=200,
    *         description="Success.",
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                  example={
    *                      "message":"Usuarios cargados correctamente"
    *                 },
    *             ),
    * 
    *         ),
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Failed",
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                  example={
    *                      "message":"Mensaje de error",
    *                 },
    *             ),
    * 
    *         ),
    *     )
    * )
    */
    public function bulkUploadUsers(Request $request)
    {

        try {
            
            Excel::import(new UsersImport($request->subcompanies_id), $request->file);

            return response()->json(["message" => "Usuarios cargados correctamente"], 200);

        } catch (Exception $e) {
            
            return response()->json(["message" => $e->getMessage(), "line" => $e->getLine()], 500);

        }
         
    }
}
