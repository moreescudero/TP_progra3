<?php
require_once './models/Producto.php';
require_once './interfaces/ApiInterface.php';

class ProductoController implements ApiInterface {
	
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        
        $producto = new Producto($parametros['nombre'], $parametros['precio'], $parametros['tipo'], $parametros['cantidad']);
        $producto->CargarProducto($producto);

        $restorno = json_encode(array('producto cargado' => $producto->nombre));
        $response->getBody()->write($restorno);

        return $response;
    }

    public function TraerUno($request, $response, $args)
    {

    }
    
	public function TraerTodos($request, $response, $args)
    {
        $array = Producto::TraerTodos();
        $retorno = json_encode(array('productos' => $array));
        $response->getBody()->write($retorno);

        return $response;
    }


}
?>