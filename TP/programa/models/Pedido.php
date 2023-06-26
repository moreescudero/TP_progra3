<?php
class Pedido{
    public $id;
    public $idProducto;
    public $cantidad;
    public $idMesa;
    public $codigo;
    public $estado;
    public $tiempo;
    public $idMesero;

    public function __construct($idProducto, $cantidad, $idMesa, $codigo, $estado, $idMesero, $tiempo=0) {
        $this->idProducto = $idProducto;
        $this->idMesa = $idMesa;
        $this->codigo = $codigo;
        $this->estado = $estado;
        $this->tiempo = $tiempo;
        $this->cantidad = $cantidad;
        $this->idMesero = $idMesero;
        if($tiempo == 0)
        {
            $this->tiempo = date("H:i");
        }
        else $this->tiempo = $tiempo;

    }

    public function CargarPedido(){

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO Pedidos (idProducto, cantidad, idMesa, codigo, estado, tiempoAprox, idMesero, hora) 
        VALUES (:idProducto, :cantidad, :idMesa, :codigo, :estado, :tiempoAprox, :idMesero, :hora)");
        $consulta->bindValue(':idProducto', $this->idProducto);
        $consulta->bindValue(':cantidad', $this->cantidad);
        $consulta->bindValue(':idMesa', $this->idMesa);
        $consulta->bindValue(':codigo', $this->codigo);
        $consulta->bindValue(':estado', $this->estado);
        $consulta->bindValue(':tiempoAprox', $this->tiempo);
        $consulta->bindValue(':idMesero', $this->idMesero);
        $hora= date("H:i");
        $consulta->bindValue(':hora', $hora);
        $consulta->execute();
    }

    public static function TraerTodos($pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta($pedido);
        $consulta->execute();
        $pedidos = array();

        while($fila = $consulta->fetch(PDO::FETCH_ASSOC))
        {
            array_push($pedidos,$fila);   
        }
        
        return $pedidos;
    }

    static function ComprobarMesa($idMesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM Mesas where id = :codigo");
        $consulta->bindValue(':codigo', $idMesa);
        $consulta->execute();

        if($consulta->rowCount() > 0)
        {
            $fila = $consulta->fetch(PDO::FETCH_ASSOC);
            if($fila['estado'] == "Libre")
            {
                $consulta= $objAccesoDatos->prepararConsulta("UPDATE Mesas set estado = 'aguardando por el pedido' where id = :id"); 
                $consulta->bindValue(":id",$idMesa);
                $consulta->execute();
                return true;
            }
            else 
            {
                $consulta= $objAccesoDatos->prepararConsulta("SELECT codigo FROM Pedidos where idMesa = :id"); 
                $consulta->bindValue(":id",$idMesa);
                $consulta->execute();
                $dato= $consulta->fetch(PDO::FETCH_ASSOC);
                return $dato['codigo'];
            }
        }
        else return false;
    }

    public static function modificarPedido($tiempo, $estado, $id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        switch($estado){
            case 'En preparacion':
                $consulta = $objAccesoDato->prepararConsulta("UPDATE Pedidos SET tiempoAprox = :tiempo, estado = :estado WHERE id = :id");
                $consulta->bindValue(':tiempo', $tiempo);
                $consulta->bindValue(':estado', $estado);
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                break;
            case 'Listo para servir':
                $consulta = $objAccesoDato->prepararConsulta("SELECT hora,tiempoAprox FROM Pedidos WHERE id = :id");
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                $consulta->execute();
                $retorno=$consulta->fetch(PDO::FETCH_ASSOC);
                date_default_timezone_set('America/Argentina/Buenos_Aires');
                $horaActual= strtotime("now");
                $horaCargado= strtotime($retorno['hora']);
                $tiempoPretendido = strtotime($retorno['tiempoAprox']);
                $diferencia = $horaCargado - $horaActual;
                $minutos = floor(($diferencia % 3600) / 60);
                $minutosAproximado= floor(($tiempoPretendido %3600)/60);
                $resultado = $minutosAproximado + $minutos;
               
                $consulta = $objAccesoDato->prepararConsulta("UPDATE Pedidos SET tiempoAprox = :tiempo, estado = :estado WHERE id = :id");
                $consulta->bindValue(':tiempo', $tiempo);
                $consulta->bindValue(':estado', $estado);
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                $consulta->execute();
                if($resultado>= 0) 
                {
                    $consulta = $objAccesoDato->prepararConsulta("UPDATE Pedidos SET ATiempo = true WHERE id = :id");
                    $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                }
                else 
                {
                    $consulta = $objAccesoDato->prepararConsulta("UPDATE Pedidos SET ATiempo = false WHERE id = :id");
                    $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                }
                break;
        }
       
        $consulta->execute();
    }
    public static function DescontarStock($id,$cantidad)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE Productos SET cantidad = :cantidad WHERE id = :id");
        $consulta->bindValue(':cantidad', $cantidad);
        $consulta->bindValue(':id', $id);
        $consulta->execute();
    }

    public static function ConsultaActualizar($id, $pedido){
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta($pedido);
        $consulta->bindValue(':id', $id);
        $consulta->execute();
    }

}
?>