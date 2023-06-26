<?php

class Usuario
{
    public $id;
    public $usuario;
    public $clave;
    public $rol;

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO Usuarios (usuario, clave, rol) VALUES (:usuario, :clave, :rol)");
        $consulta->bindValue(':usuario', $this->usuario);
        $consulta->bindValue(':clave', $this->clave);
        $consulta->bindValue(':rol', $this->rol);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM Usuarios");
        $consulta->execute();
        $usuarios= array();

     
        while($fila = $consulta->fetch(PDO::FETCH_ASSOC)){
            $usuario= new Usuario();
            $usuario->id= $fila['id'];
            $usuario->usuario= $fila['usuario'];
            $usuario->rol= $fila['rol'];

            array_push($usuarios,$usuario);
        }
        
        return $usuarios;
    }

    public static function obtenerUsuario($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, rol FROM Usuarios WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();
        
        return $consulta->fetchObject('Usuario');
    }
}