<?php
require_once ('./models/Jws.php');
require_once('./models/Usuario.php');
use Slim\Psr7\Response as ResponseMW;

class Logger
{
    public function GenerarToken($request, $handler): ResponseMW {
		$parametros= $request->getParsedBody();
		$response= new ResponseMW();

		if($request->getMethod()=="GET")
		{
         	$response= $handler->handle($request);
		}else{
			$parametros = $request->getParsedBody();
			$usuario= new Usuario();
			$usuario->usuario= $parametros['usuario'];
			$usuario->clave= $parametros['clave'];
			$usuario->rol= $parametros['rol'];
			$datos = array('usuario' => $usuario->usuario,'perfil' => $usuario->rol);
			
			$token= Autenticador::CrearToken($datos);
					
		//$response= new ResponseMW();
			echo "Token: " .  $token;
			//$response->getBody()->write($token);
			$response= $handler->handle($request);
		}
		return $response;   
	}

    public function VerificarToken($request, $handler): ResponseMW {
		$objDelaRespuesta= new stdclass();
		$seccion= self::Prueba($_SERVER['REQUEST_URI']);
		$objDelaRespuesta->respuesta = "";
		$parametros = $request->getParsedBody();
		$response = new ResponseMW();
		$token = "";
		try 
		{
			if($request->getMethod()=="GET")
			{
				$response->getBody()->write('<p>NO necesita credenciales para los get </p>');
				$response= $handler->handle($request);
				return $response;
			}
			else
			{
				if(!isset($parametros['token']))
				{
					if(isset($parametros['id']))
					{
						$usuario= Usuario::obtenerUsuario($parametros['id']);
						if($usuario== false) throw new Exception("Error");
						$datos = array('usuario' => $usuario->usuario,'perfil' => $usuario->rol);
						$token= Autenticador::CrearToken($datos);
					}
					
				}
				else $token= $parametros['token'];
			}
			Autenticador::verificarToken($token);
			$objDelaRespuesta->esValido=true;      
		}
		catch (Exception $e) {      
			$objDelaRespuesta->excepcion=$e->getMessage();
			$objDelaRespuesta->esValido=false;     
		}

		if($objDelaRespuesta->esValido)
		{		
			$payload=Autenticador::ObtenerData($token);
			switch($seccion)	{
				case 'mesa':
					if($payload->perfil=="socio" || $payload->perfil == "mesero")
					{
						$response = $handler->handle($request);
					}		           	
					else
					{	
						$objDelaRespuesta->respuesta="Solo administradores";
					}
				default:
				if($payload->perfil=="socio")
				{
					$response = $handler->handle($request);
				}		           	
				else
				{	
					$objDelaRespuesta->respuesta="Solo administradores";
				}
				break;
			}			
			//var_dump($payload);
				          
		}    
		else
		{
			//   $handler->getBody()->write('<p>no tenes habilitado el ingreso</p>');
			$objDelaRespuesta->respuesta="Solo usuarios registrados";
			$objDelaRespuesta->elToken=$token;

		}  				
	$response->getBody()->write($objDelaRespuesta->respuesta);
	return $response;				
	}

	private function Prueba($string){
		$array=explode("/",$string);

		return $array[3];
	}

}