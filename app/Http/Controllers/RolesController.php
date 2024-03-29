<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roles;
use Exception;


class RolesController extends Controller
{
    /**
    * @OA\Get(
    *     path="/api/v1/roles/list",
    *     summary="Mostrar Roles de Usuarios",
    *     tags={"Roles"},
    *     security={{"bearer_token":{}}},
    *     @OA\Response(
    *         response=200,
    *         description="Mostrar todos los roles de usuarios.",
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                  example={
    *                       "roles": {
    *                           {
    *                               "id": 0,
    *                               "name": ""
    *                           },
    *                         }
    *                   }
    *             )
    *         )
    *     )
    * )
    */
    public function index(Request $request)
    {
        try {
            $roles = Roles::all('id', 'rol_name');
            return response()->json(["roles" => $roles], 200);
        } catch (Exception $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }

    public static function showNameById($id)
    {
        try {

            $rol = Roles::find($id);

            if($rol)
                return $rol->rol_name;

        } catch (Exception $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }

    public static function showIdByName($name)
    {
        try {

            $rol = Roles::where('rol_name', $name)->first();

            if($rol)
                return $rol->id;

        } catch (Exception $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
}
