<?php
require_once './models/Pedido.php'; 
require_once './interfaces/ApiInterface.php';
use Slim\Psr7\Response as ResponseMW;

class EncuestaController{

    public function TraerMejoresComentarios($request, $response){
        $array = Pedido::TraerTodos("SELECT *  FROM Encuestas where notaGeneral >= 7");
        $retorno = json_encode(array("Los mejores comentarios"=>$array));
        $response->getBody()->write($retorno);

        return $response;
    }

    public function TraerMesaMasUsada($request, $response){
        $obj= AccesoDatos::obtenerInstancia();
        $consulta = $obj->prepararConsulta("SELECT idMesa, COUNT(*) as cantidad FROM Encuestas ORDER BY cantidad DESC LIMIT 1");
        $consulta->execute();

        $array= $consulta->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode(array("La mesa mas usada es: " => $array)));

        return $response;
    }

    public function TraerPedidosTardios($request, $response){
        $obj= AccesoDatos::obtenerInstancia();
        $consulta = $obj->prepararConsulta("SELECT PE.id, PR.nombre AS nombreProducto, PE.cantidad, PE.idMesa, PE.codigo, PE.idMesero FROM Pedidos PE inner join Productos PR on PE.idProducto = PR.id WHERE ATiempo = 0");
        $consulta->execute();

        $array= $consulta->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode(array("Los pedidos que no se entregaron a tiempo: " => $array)));

        return $response;

    }
}
?>