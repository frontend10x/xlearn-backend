<?php

namespace App\Http\Controllers;

use App\Models\Areas;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AreaController extends Controller
{
    public static function store(Request $request, $validate = true)
    {
        try {

            if($validate){
                $areas = Areas::where("name", $request->input("name"))->first();
                if (!empty($areas)) {
                    throw new Exception("Ya existe un area con el nombre " . $request->input("name"));
                }
            }

            $create = [
                "name" => $request->input("name"), 
                "description" => $request->input("description"), 
                "state" => $request->input("state"),
                "vimeo:id" => $request->input("vimeo:id"), 
                "vimeo:uri" => $request->input("vimeo:uri")
            ];
            if (!empty($request->input("file_path"))) {
                $create['file_path'] = $request->input("file_path");
            }

            $create_area = Areas::create($create);
            return response()->json(["message" => "Area creada con éxito", "id" => $create_area['id']], 200);
        } catch (Exception $e) {
            return return_exceptions($e);
        }
    }

    public static function sync_with_vimeo(Request $request)
    {
        try {

            $courses = Areas::where("vimeo:id", $request->input("vimeo:id"))->first();

            if (empty($courses)) {
                $state = self::store($request, false);
            }else{
                $state = self::edit($request);
            }

            return json_encode($state->original);

        } catch (Exception $e) {
            return return_exceptions($e);
        }
    }

    public static function edit(Request $request)
    {
        try {
            $area = Areas::where("vimeo:id", $request->input("vimeo:id"))->first();
            $edit = [
                "name" => $request->input("name"), 
                "description" => $request->input("description"), 
                "state" => $request->input("state"),
                "vimeo:id" => $request->input("vimeo:id"), 
                "vimeo:uri" => $request->input("vimeo:uri")
            ];
            if (!empty($request->input("file_path"))) {
                $edit['file_path'] = $request->input("file_path");
            }
            if (empty($area)) {
                throw new Exception("No existe el id: " . $id . " para ser actualizado");
            } else {
                $area->update($edit);
                $message = "Area actualizada con éxto";
            }

            return response()->json(["message" => $message, "id" => $area->id], 200);
        } catch (Exception $e) {
            return return_exceptions($e);
        }
    }

    /**
    * @OA\Get(
    *     path="/api/v1/area/list",
    *     summary="Mostrar areas",
    *     tags={"Areas"},
    *     security={{"bearer_token":{}}},
    *     @OA\Parameter(name="offset", in="query", @OA\Schema(type="number")),
    *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="number")),
    *     @OA\Response(
    *         response=200,
    *         description="Mostrar todas las areas.",
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
    *                           "_rel": "areas",
    *                           "_embedded": {
    *                               "areas": {
    *                                   {
    *                                   "id": 0,
    *                                   "name": "",
    *                                   "description": "",
    *                                   "file_path": "",
    *                                   "state": 0,
    *                                   "created_at": "2022-06-11T23:21:42.000000Z",
    *                                   "updated_at": "2022-06-12T00:46:06.000000Z"
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

            //TODO debe sacarse del request, por defecto el valor es uno
            $offset = $request->has('offset') ? intval($request->get('offset')) : 1;

            //TODO debe sacarse del request, por defecto el valor es 10.
            $limit = $request->has('limit') ? intval($request->get('limit')) : 10;

            $consult = Areas::where('state', 1)->limit($limit)->offset(($offset - 1) * $limit)->get()->toArray();

            $nexOffset = $offset + 1;
            $previousOffset = ($offset > 1) ? $offset - 1 : 1;

            $users = array(
                "hc:length" => count($consult), //Es la longitud del array a devolver
                "hc:total"  => Areas::count(), //Es la longitud total de los registros disponibles en el query original,
                "hc:offset" => $offset,
                "hc:limit"  => $limit,
                "hc:next"   => server_path() . '?limit=' . $limit . '&offset=' . $nexOffset,
                "hc:previous"   => server_path() . '?limit=' . $limit . '&offset=' . $previousOffset,
                "_rel"		=> "areas",
                "_embedded" => array(
                    "areas" => $consult
                )
            );

            if(empty($consult))
                throw new Exception("No se encontraron registros");

            return response()->json(["response" => $users], 200);

        } catch (Exception $e) {
            return return_exceptions($e);
        }
    }
    public function changestate(Request $request, $id)
    {
        try {
            $buscaActualiza = Areas::find($id);
            if (empty($buscaActualiza)) {
                throw new Exception("No existe el Id:" . $id . " para el cambio de estado");
            }
            $buscaActualiza->update(["state" => $request->input("state")]);
            return response()->json(["message" => "Cambio de estado correctamente"], 200);
        } catch (Exception $e) {
            return return_exceptions($e);
        }
    }
}
