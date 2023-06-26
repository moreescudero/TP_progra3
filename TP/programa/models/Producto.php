<?php
class Producto{
    public $id;
    public $nombre;
    public $precio;
    public $tipo;
    public $cantidad;

    public function __construct( $nombre, $precio, $tipo, $cantidad)
    {
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->tipo = $tipo;
        $this->cantidad = $cantidad;
    }

    public function CargarProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO Productos (nombre, precio, tipo, cantidad) VALUES (:nombre, :precio, :tipo, :cantidad)");
        $consulta->bindValue(':nombre', $this->nombre);
        $consulta->bindValue(':precio', $this->precio);
        $consulta->bindValue(':tipo', $this->tipo);
        $consulta->bindValue(':cantidad', $this->cantidad);
        $consulta->execute();
    }

    public static function TraerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM Productos");
        $consulta->execute();
        $productos = array();

        while($fila = $consulta->fetch(PDO::FETCH_ASSOC)){
            $id = $fila['id'];
            $nombre = $fila['nombre'];
            $precio = $fila['precio'];
            $tipo = $fila['tipo'];
            $cantidad = $fila['cantidad'];
            
            $producto = new Producto($nombre,$precio,$tipo,$cantidad);

            $producto->id = $id;
            array_push($productos,$producto);
        }
        return $productos;
    }
}
?>