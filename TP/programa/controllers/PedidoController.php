<?php
require_once './models/Pedido.php'; 
require_once './interfaces/ApiInterface.php';
require_once './models/Usuario.php';
use Slim\Psr7\Response as ResponseMW;

class PedidoController implements ApiInterface{

	public function TraerTodos($request, $response, $args)
    {      
        $array= Pedido::TraerTodos("SELECT PE.id, PR.nombre as 'Producto', PR.precio as 'Precio', PE.cantidad as 'Cantidad', M.codigo as 'CodigoMesa', PE.codigo as 'CodigoPedido', PE.estado as 'EstadoPedido', PE.tiempoAprox FROM Pedidos PE inner join Productos PR on PE.idProducto= PR.id inner join Mesas M on PE.idMesa = M.id");
        $retorno= json_encode(array("Todos los Pedidos"=>$array));
        $response->getBody()->write($retorno);

        return $response;
    }

	public function CargarUno($request, $response, $args)
    {
        $parametros= $request->getParsedBody();
        $codigo = Pedido::ComprobarMesa($parametros['idMesa']);

        if ($codigo == true)
        {
            $codigo= substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
        }
        $pedido= new Pedido($parametros['idProd'], $parametros['cantidad'], $parametros['idMesa'], $codigo, "En espera", $parametros['idMesero']);    
        
        $pedido->CargarPedido(); 

        if(isset($_FILES['foto'])){
            $foto= $_FILES['foto'];
            $ruta= 'imagenes/' . $codigo . "-" . date("y-m-d") . ".png";
            move_uploaded_file($foto['tmp_name'],$ruta);
        }
        
        $retorno = json_encode(array("Pedido Realizado correctamente con el codigo:" => $codigo));
        $response->getBody()->write($retorno);

        return $response;
    }

    public function TraerFiltrado($request, $response, $args) {
        $retorno= json_encode(array("Algo salió mal" => 404));
        $args = $request->getQueryParams();
        $consulta = "SELECT PE.id, PR.nombre as 'Producto', PR.precio as 'Precio', PE.cantidad as 'Cantidad' , M.codigo as 'CodigoMesa', PE.codigo as 'CodigoPedido' , PE.estado as 'EstadoPedido', PE.tiempoAprox FROM Pedidos PE inner join Productos PR on PE.idProducto = PR.id inner join Mesas M on PE.idMesa = M.id";
        if(isset($args['id']))
        {
            $usuario= Usuario::obtenerUsuario($args['id']);
            switch ($usuario->rol) 
            {
                case 'cocinero':
                    $consulta = $consulta . " where PR.tipo = 'comida' and PE.estado != 'Cobrado'";
                    break;
                case 'bartender' :
                    $consulta = $consulta . " where PR.tipo = 'bebida' and PE.estado != 'Cobrado'";
                    break;
                case 'cervecero':
                    $consulta = $consulta . " where PR.tipo = 'cerveza' and PE.estado != 'Cobrado'";
                    break;
                case 'mesero':
                    $consulta = $consulta . " where PE.estado = 'Listo'";
                    break;
            }
            $array = Pedido::TraerTodos($consulta);
        }
        else
        {
            $usuario = new stdClass();
            $usuario->usuario = "Cliente";
            $array = Pedido::TraerTodos($consulta . " where M.id= '{$args['idMesa']}' and PE.id= '{$args['idPedido']}'");
            $tiempoEsperado = Pedido::TraerTodos("SELECT MAX(PE.tiempoAprox) as 'tiempoAprox' FROM Pedidos PE inner join Productos PR on PE.idProducto = PR.id inner join Mesas M on PE.idMesa = M.id where M.id= '{$args['idMesa']}' and PE.id= '{$args['idPedido']}'");
            Pedido::Insertar($args['notaCocinero'], $args['notaMesero'], $args['notaMesa'], $args['notaGeneral'], $args['comentario'], $args['idMesa'], $args['idPedido']);
            $retorno = json_encode($tiempoEsperado);
            $response->getBody()->write($retorno);
        }
        $retorno = json_encode(array("Los pedidos de $usuario->usuario <br/>"=>$array));
        $response->getBody()->write($retorno);
        
        return $response;
    }

    public function AtenderPedido($request, $response)
    {
        $parametros = $request->getParsedBody();

        Pedido::modificarPedido($parametros['tiempo'], $parametros['estado'], $parametros['id']);  

        $retorno = json_encode(array("Se actualizo el pedido, el tiempo aproximado es de:" => $parametros['tiempo']));
        $response->getBody()->write($retorno);

        return $response;

    }

    public function VerificarStock($request, $handler) : ResponseMW{
        $response = new ResponseMW();
        $parametros = $request->getParsedBody();
        if($request->getMethod() == 'POST')
        {
            $pedido = Pedido::TraerTodos("SELECT cantidad FROM Productos where id = {$parametros['idProd']}");
            if($pedido[0]['cantidad'] >= $parametros['cantidad'])
            {
                Pedido::DescontarStock($parametros['idProd'],$pedido[0]['cantidad'] - $parametros['cantidad']);
                $response = $handler->handle($request);
            }  
            else $response->getBody()->write("No hay stock ");
        }
        else $response = $handler->handle($request); 

        return $response;
    }

    public function Cobrar($request, $response)
    {
        $parametros = $request->getParsedBody();
        
        $pedidos = Pedido::TraerTodos("SELECT PE.id, PR.precio, PE.cantidad, PE.idMesa from Pedidos PE inner join Productos PR on PE.idProducto = PR.id inner join Mesas M on M.id = PE.idMesa where M.id = {$parametros['idMesa']}");
        $totalApagar = 0;

        foreach($pedidos as $pedido)
        {
            $totalApagar += $pedido['precio'] * $pedido['cantidad'];
            Pedido::ConsultaActualizar($pedido['id'], "UPDATE Pedidos SET estado = 'Cobrado' WHERE id = :id");
        }
        
        Pedido::ConsultaActualizar($parametros['idMesa'], "UPDATE Mesas SET estado = 'Cobrado' WHERE id = :id");
        $response->getBody()->write("Total a pagar: ". $totalApagar);
        return $response;
    }
}
?>