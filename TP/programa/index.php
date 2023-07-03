<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/EncuestaController.php';
require_once './middlewares/Logger.php';
require_once './controllers/GuardarController.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$app = AppFactory::create();

$app->setBasePath('/TP/programa');
$app->addErrorMiddleware(true, true, true);

$app->addBodyParsingMiddleware();

// Routes
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
})->add(\Logger::class . ':GenerarToken')->add(\Logger::class . ':VerificarToken');

$app->group('/productos', function (RouteCollectorProxy $group){
  $group->get('[/]', \ProductoController::class . ':TraerTodos');
  $group->post('[/]', \ProductoController::class . ':CargarUno');
})->add(\Logger::class . ':VerificarToken');

$app->group('/mesas', function (RouteCollectorProxy $group){
  $group->get('[/]', \MesaController::class . ':TraerTodos');
  $group->post('[/]', \MesaController::class . ':CargarUno');
  $group->post('/cerrar', \MesaController::class . ':CerrarMesa');
  $group->get('/actualizar', \MesaController::class . ':Actualizar');
})->add(\Logger::class . ':VerificarToken');

$app->group('/pedidos', function (RouteCollectorProxy $group){
  $group->post('/cargar', \PedidoController::class . ':CargarUno')->add(\PedidoController::class . ':VerificarStock');
  $group->post('/cobrar', \PedidoController::class . ':Cobrar');
  $group->post('/atender', \PedidoController::class . ':AtenderPedido');
});

$app->group('/filtrar', function (RouteCollectorProxy $group){
  $group->get('/encuesta/', \PedidoController::class . ':TraerFiltrado');
  $group->get('/listo/', \PedidoController::class . ':TraerFiltrado');
  $group->get('/', \PedidoController::class . ':TraerFiltrado');
});

$app->group('/guardar', function (RouteCollectorProxy $group){
  $group->post('/usuarios', \GuardarController::class . ':GuardarUsuarios');
  $group->post('/mesas', \GuardarController::class . ':GuardarMesas');
  $group->post('/pedidos', \GuardarController::class . ':GuardarPedidos');
  $group->post('/productos', \GuardarController::class . ':GuardarProductos');
})->add(\Logger::class . ':VerificarToken');

$app->group('/estadisticas', function (RouteCollectorProxy $group){
  $group->post('/comentarios', \EncuestaController::class . ':TraerMejoresComentarios');
  $group->post('/mesa', \EncuestaController::class . ':TraerMesaMasUsada');
  $group->post('/tardios', \EncuestaController::class . ':TraerPedidosTardios');
})->add(\Logger::class . ':VerificarToken');

$app->group('/descargar', function (RouteCollectorProxy $group){
  $group->post('/', \GuardarController::class . ':DescargarPDF');
})->add(\Logger::class . ':VerificarToken');

$app->run();
