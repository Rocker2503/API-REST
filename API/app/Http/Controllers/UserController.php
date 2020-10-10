<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Http\Request\RegisterAuthRequest;
use MiladRahimi\Jwt\Generator;
use MiladRahimi\Jwt\Parser;
use MiladRahimi\Jwt\Cryptography\Algorithms\Hmac\HS256;

use Illuminate\Routing\Controller as BaseController;

class UserController extends BaseController {

	//Despues de registrarse, se mantiene el inicio de sesión.
	public $loginRegistro = true;

	public function new(Request $request) {

		$cliente = new Client();
		
		$data = [
			"nombre" => $request->nombre,
			"email" => $request->email,
			"contrasena" => $request->contrasena
		];

		//Guardamos en MockAPI
		$url = "https://5f74ea6c1cf3c900161cd9e3.mockapi.io/api/v1/User";
		$respuesta = $cliente->post($url, [
			'headers' => ['Content-Type' => 'application/json'],
			'body' => json_encode($data)
		]);	

		return response()->json([
			'status' => 'ok',
			'data' => $data
		], 200);

	}

	public function login(Request $request) {

		$credenciales = $request->only('email','contrasena');
		$jwt_token = null;

		//Consumir los datos desde MockAPI 
		$url = "https://5f74ea6c1cf3c900161cd9e3.mockapi.io/api/v1/User";
		$cliente = new Client();
		$respuesta = $cliente->request('GET',$url);
		$estado = $respuesta->getStatusCode();
    	$usuarios = json_decode($respuesta->getBody()->getContents());

    	//Validar usuario 
    	foreach($usuarios as $usuario) {
    		if($usuario->email == $credenciales['email'] && $usuario->contrasena == $credenciales['contrasena']) {
    			
    			//Para generar y parsear tokens
    			$signer = new HS256('12345678901234567890123456789012');

    			//Generar el token
    			$generator = new Generator($signer);
    			$jwt_token = $generator->generate(['id' => $usuario->id]);

    			return response()->json([
    				'token' => $jwt_token
    			]);
    		}
    	}
    	return response()->json(['Not Found'], 404);
	}

	public function me(Request $request) {

		//Recibir y parsear el token para obtener el id del usuario
		$token = $request['token'];
		$signer = new HS256('12345678901234567890123456789012');
		$parser = new Parser($signer);
		$auxiliar = $parser->parse($token);
		$id = $auxiliar['id'];

		//Entregar la info del usuario en una respuesta
		$url = "https://5f74ea6c1cf3c900161cd9e3.mockapi.io/api/v1/User/" . $id;
		$cliente = new Client();
		$respuesta = $cliente->request('GET',$url);
		$estado = $respuesta->getStatusCode();

		$usuario = json_decode($respuesta->getBody()->getContents());

		if(!empty($usuario)) {
			return response()->json([
			'data' => $usuario
			]);
		}else {
			return response()->json(['Not Found'], 404);
		}

	}

}