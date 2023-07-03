<?php
require_once './models/Mesa.php';
require_once './interfaces/ApiInterface.php';

class MesaController implements ApiInterface{

    public function TraerTodos($request, $response, $args)
    {   
        $array= Mesa::TraerMesas();

        $retorno= json_encode(array("Mesas " => $array));
        $response->getBody()->write($retorno);

        return $response;
    }

	public function CargarUno($request, $response, $args)
    {
        $codigo = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
        $retorno;
        
        if(Mesa::ComprobarCodigo($codigo))
        {
            $mesa = new Mesa($codigo, "Vacia");
            $mesa->CargarMesa();
            $retorno= json_encode(array('Mesa cargada' => $mesa->codigo));
        }
        else $retorno= json_encode(array("Mensaje"=>"Error, ya existe ese codigo"));

        $response->getBody()->write($retorno);

        return $response;
    }

    public function Actualizar($request, $response, $args){
        $args = $request->getQueryParams();

        if(Mesa::ActualizarMesa($args['idMesa']))
        {
            $response->getBody()->write("Se actualizo el pedido");
        }
        else $response->getBody()->write("Algun pedido esta sin hacer ");
        
        return $response;
    }

    public function CerrarMesa($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        if(Mesa::CerrarMesa($parametros['idMesa']))
        {
            $response->getBody()->write("Se cerro la mesa");
        } 
        else $response->getBody()->write("La mesa aun no ha pagado");
        return $response;
    }
}