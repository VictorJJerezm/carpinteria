<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\CotizacionGestionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\UsuarioGestionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ProveedorController;

// Público
Route::get('/', [CatalogoController::class, 'index'])
    ->name('catalogo.index');

// Auth
Route::get('/login', [AuthController::class, 'loginForm'])
    ->name('login')
    ->middleware('guest');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.procesar')
    ->middleware('guest');

// Registro de clientes (solo invitados)
Route::get('/registro', [AuthController::class, 'registerForm'])
    ->name('register')
    ->middleware('guest');

Route::post('/registro', [AuthController::class, 'register'])
    ->name('register.store')
    ->middleware('guest');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// Cliente: solo cotizar
Route::middleware(['auth', 'activo', 'rol:cliente'])
    ->group(function () {

    Route::get('/cotizar', [CotizacionController::class, 'index'])
        ->name('cliente.cotizar.index');

    Route::post('/cotizar', [CotizacionController::class, 'store'])
        ->name('cliente.cotizar.store');

    Route::get('/mis-cotizaciones', [CotizacionController::class, 'mis'])
        ->name('cliente.cotizaciones.mis');

    Route::post('/mis-cotizaciones/{id}/aceptar', [CotizacionController::class, 'aceptar'])
        ->name('cliente.cotizaciones.aceptar');

    Route::post('/mis-cotizaciones/{id}/rechazar', [CotizacionController::class, 'rechazar'])
        ->name('cliente.cotizaciones.rechazar');
});

// Carpintero/Admin: panel y mantenimientos
Route::middleware(['auth', 'activo', 'rol:admin,carpintero'])
    ->group(function () {

    Route::get('/panel', [DashboardController::class, 'index'])
        ->name('panel.dashboard');

    // Rutas para creación de productos
    Route::resource('productos', ProductoController::class)
        ->except(['show']);

    // CRUD de insumos
    Route::resource('insumos', InsumoController::class)
        ->except(['show']);

    // Inventario (listado + movimientos)
    Route::get('inventario', [InventarioController::class, 'index'])
        ->name('inventario.index');

    Route::post('inventario/{id_insumo}/entrada', [InventarioController::class, 'entrada'])
        ->name('inventario.entrada');

    Route::post('inventario/{id_insumo}/salida',  [InventarioController::class, 'salida'])
        ->name('inventario.salida');

    // Rutas para las cotizaciones
    Route::get('/cotizaciones', [CotizacionGestionController::class, 'index'])
        ->name('gestion.cotizaciones.index');

    Route::get('/cotizaciones/{id}', [CotizacionGestionController::class, 'show'])
        ->name('gestion.cotizaciones.show');

    Route::get('/cotizaciones/{id}/responder', [CotizacionGestionController::class, 'formResponder'])
        ->name('gestion.cotizaciones.form');

    Route::post('/cotizaciones/{id}/responder', [CotizacionGestionController::class, 'responder'])
        ->name('gestion.cotizaciones.responder');

    Route::post('/cotizaciones/{id}/cancelar', [CotizacionGestionController::class, 'cancelar'])
        ->name('gestion.cotizaciones.cancelar');
    
    // Rutas para realización de pedidos
    Route::get('/pedidos', [PedidoController::class, 'index'])
        ->name('pedidos.index');

    Route::get('/pedidos/{id}', [PedidoController::class, 'show'])
        ->name('pedidos.show');

    Route::post('/pedidos/{id}/estado', [PedidoController::class, 'cambiarEstado'])
        ->name('pedidos.estado');

    // Rutas para gestión de usuatios
    Route::resource('usuarios', UsuarioGestionController::class)
        ->except(['show']);

    Route::post('usuarios/{usuario}/activar',[UsuarioGestionController::class, 'activar'])
        ->name('usuarios.activar');

    Route::post('usuarios/{usuario}/desactivar',[UsuarioGestionController::class, 'desactivar'])
        ->name('usuarios.desactivar');

    //Rutas para reportes
    Route::get('/reportes', [ReporteController::class, 'index'])
        ->name('reportes.index');

        Route::prefix('reportes')->group(function () {
            Route::get('/materiales', [ReporteController::class, 'materiales'])
                ->name('reportes.materiales');
            Route::get('/insumos',    [ReporteController::class, 'insumos'])
                ->name('reportes.insumos');
            Route::get('/clientes',   [ReporteController::class, 'clientes'])
                ->name('reportes.clientes');
            
            // exports CSV - EXTRAS
            Route::get('/materiales/export', [ReporteController::class, 'exportMateriales'])
                ->name('reportes.materiales.export');
            Route::get('/insumos/export',    [ReporteController::class, 'exportInsumos'])
                ->name('reportes.insumos.export');
            Route::get('/clientes/export',   [ReporteController::class, 'exportClientes'])
                ->name('reportes.clientes.export');
            });

    //export CSV
    Route::get('/reportes/export', [ReporteController::class, 'export'])
        ->name('reportes.export'); 

    //Proveedores
    Route::resource('proveedores', ProveedorController::class)
        ->parameters(['proveedores' => 'proveedor']);

    Route::resource('proveedores', ProveedorController::class)
        ->except(['show']);

    Route::get('proveedores/{proveedor}/solicitar',
        [ProveedorController::class, 'formSolicitud'])
            ->name('proveedores.solicitud');

    Route::post('proveedores/{proveedor}/solicitar',
        [ProveedorController::class, 'enviarSolicitud'])
            ->name('proveedores.solicitud.enviar');

    Route::patch('proveedores/{proveedor}/toggle',
        [ProveedorController::class, 'toggle'])
            ->name('proveedores.toggle');
});