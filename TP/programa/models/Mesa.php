<?php

class Mesa{

    public $id;
    public $codigo;
    public $estado;

    public function __construct($codigo, $estado, $id = 0)
    {
        $this->codigo = $codigo;
        $this->estado = $estado;
        $this->id = $id;
    }

    static function ComprobarCodigo($codigo){ 
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM Mesas where codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo);
        $consulta->execute();

        if($consulta->rowCount() > 0)return false;
        else return true;
    }

    public function CargarMesa(){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO Mesas (codigo, estado) VALUES (:codigo, :estado)");
        $consulta->bindValue(':codigo', $this->codigo);
        $consulta->bindValue(':estado', $this->estado);
        $consulta->execute();
    }

    public static function TraerMesas(){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM Mesas");
        $consulta->execute();

        $mesas= array();

        while($fila = $consulta->fetch(PDO::FETCH_ASSOC)){
            $mesa= new Mesa($fila['codigo'], $fila['estado'], $fila['id']);
            array_push($mesas, $mesa);
        }
        return $mesas;
    }

    public static function CerrarMesa($id){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE Mesas SET estado = 'Vacia' where id = '$id'");
        $consulta->execute();

        if($consulta->rowCount() > 0)return true;
        else return false;
    }

    public static function ActualizarMesa($id){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT P.estado FROM Pedidos P INNER JOIN Mesas M on P.idMesa = M.id where P.idMesa = '$id'");
        $consulta->execute();
        $flag = false;
        
        while($pedido = $consulta->fetch(PDO::FETCH_ASSOC)){
            if($pedido['estado'] != "Listo para servir"){
                $flag= true;
                break;
            }
        }
        if(!$flag) {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE Mesas SET estado = 'Comiendo' where id = '$id'");
            $consulta->execute();
            return true;
        }
        return false;
    }
}
?>