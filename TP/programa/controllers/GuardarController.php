<?php

require_once ('./models/Usuario.php');
require_once ('./models/Mesa.php');
require_once('./models/Pedido.php');
require_once('./models/Producto.php');

class GuardarController
{
    public function GuardarUsuarios($request, $response)
    {
        try
        {
            $usuarios = Usuario::obtenerTodos();
            $archivo = fopen('./csv/usuarios.csv', 'w');

            $datos= array('id', 'nombre','tipo' );
            fputcsv($archivo, $datos);

            foreach($usuarios as $u)
            {
                $fila = get_object_vars($u);
                fputcsv($archivo, $fila);
            }

            $response->getBody()->write("Archivo guardado correctamente");
        }
        catch(Exception $e)
        {
            $response->getBody()->write(e->getMessage);
        }
        finally
        {
            fclose($archivo);
        }

        return $response;
    }

    public function GuardarMesas($request, $response)
    {
        try
        {
            $mesas = Mesa::TraerMesas();
            $archivo = fopen('./csv/mesas.csv', 'w');

            $datos = array('id', 'codigo','estadoDelMomento' );
            fputcsv($archivo, $datos);

            foreach($mesas as $m)
            {
                $fila= get_object_vars($m);
                fputcsv($archivo, $fila);
            }
            $response->getBody()->write("Archivo guardado correctamente");
        }
        catch(Exception $e)
        {
            $response->getBody()->write(e->getMessage);
        }
        finally
        {
            fclose($archivo);
        }
        return $response;
    }

    public function GuardarPedidos($request, $response)
    {
        try
        {
            $pedidos = Pedido::TraerTodos("SELECT PE.id, PR.nombre as 'Producto' , PR.precio as 'Precio', PE.cantidad as 'Cantidad', M.codigo as 'CodigoMesa', PE.codigo as 'CodigoPedido', PE.estado FROM Pedidos PE inner join Productos PR on PE.idProducto = PR.id inner join Mesas M on PE.idMesa = M.id");
            $archivo = fopen('./csv/pedidos.csv', 'w');

            $datos = array('id', 'producto', 'precio', 'cantidad', 'codigoMesa', 'codigoPedido', 'estado');
            fputcsv($archivo, $datos);

            foreach($pedidos as $p)
            {
                fputcsv($archivo, $p);
            }

            $response->getBody()->write("Archivo guardado correctamente");
        }
        catch(Exception $e)
        {
            $response->getBody()->write(e->getMessage);
        }
        finally
        {
            fclose($archivo);
        }

        return $response;
    }

    public function GuardarProductos($request, $response)
    {
        try
        {
            $productos = Producto::TraerTodos();

            $archivo = fopen('./csv/productos.csv', 'w');

            $datos = array('id', 'nombre', 'precio', 'tipo', 'cantidad');
            fputcsv($archivo, $datos);

            foreach($productos as $p){
                $fila = get_object_vars($p);
                fputcsv($archivo, $fila);
            }
            $response->getBody()->write("Archivo guardado correctamente");
        }
        catch(Exception $e)
        {
            $response->getBody()->write(e->getMessage);
        }
        finally
        {
            fclose($archivo);
        }

        return $response;
    }

    public function DescargarPDF($request, $response){
        $parametros = $request->getParsedBody();

        if (isset($_FILES['img'])) {
            $tempImagePath = $_FILES['img']['tmp_name'];
            $imageExtension = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);

            $pdf = new FPDF();
            $pdf->AddPage();

            $pdf->Image($tempImagePath, 10, 10, 100, 0, $imageExtension); 
            $pdfFileName = uniqid() . '.pdf';
            $pdf->Output('F', $pdfFileName);
    
            header('Content-Disposition: attachment; filename="' . $pdfFileName . '"');
            header('Content-type: application/pdf');
            readfile($pdfFileName);
    
            unlink($pdfFileName);
            $response->getBody()->write("Se descargo con exito el PDF del logo.");
        } else {
            $response->getBody()->write("No se ha enviado ninguna imagen.");
          }

        return $response;
    }
}
?>