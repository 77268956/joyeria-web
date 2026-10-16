<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\CashSessionController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiscountAuthorizationController;
use App\Http\Controllers\Admin\LayawayController;
use App\Http\Controllers\Admin\MaterialLotController;
use App\Http\Controllers\Admin\MetalPriceController;
use App\Http\Controllers\Admin\MetalPurchaseController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\StockCountController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - JoyeriaWeb
|--------------------------------------------------------------------------
|
| Conforme al PRD, TRD y Flujo de la App:
| 1. Rutas públicas del sitio web
| 2. Rutas de autenticación (Login / Logout)
| 3. Rutas del panel administrativo (protegidas por middleware auth)
|
*/

// =========================================================================
// 1. RUTAS PÚBLICAS (Sitio Web)
// =========================================================================
Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::get('/catalogo', [PublicSiteController::class, 'catalog'])->name('public.catalog');
Route::get('/pieza/{product}', [PublicSiteController::class, 'product'])->name('public.product');
Route::get('/vender-oro', [PublicSiteController::class, 'sellGold'])->name('public.sell_gold');
Route::post('/vender-oro/cotizar', [PublicSiteController::class, 'quote'])
    ->middleware('throttle:30,1')
    ->name('public.sell_gold.quote');
Route::get('/sucursal', [PublicSiteController::class, 'store'])->name('public.store');
Route::get('/contacto', [PublicSiteController::class, 'contact'])->name('public.contact');
Route::post('/contacto', [PublicSiteController::class, 'submitContact'])
    ->middleware('throttle:5,1')
    ->name('public.contact.store');

// =========================================================================
// 2. AUTENTICACIÓN
// =========================================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// =========================================================================
// 3. PANEL ADMINISTRATIVO (Rutas protegidas por auth)
// =========================================================================
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Punto de Venta (POS) — requiere turno de caja abierto
    Route::middleware(['permission:sales.create', 'cash.session'])->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/buscar', [PosController::class, 'search'])->name('pos.search');
        Route::post('/pos/venta', [PosController::class, 'store'])->name('pos.store');

        // Autorizaciones de descuento en mostrador (híbrido PIN / remoto)
        Route::post('/pos/autorizacion/validar-pin', [DiscountAuthorizationController::class, 'validatePin'])->name('pos.authorization.pin');
        Route::post('/pos/autorizacion/solicitar', [DiscountAuthorizationController::class, 'store'])->name('pos.authorization.store');
        Route::get('/pos/autorizacion/{authorization}/estado', [DiscountAuthorizationController::class, 'status'])->name('pos.authorization.status');
    });

    // Autorizaciones de descuento (Bandeja y acciones de Gerente / Dueño)
    Route::middleware(['permission:products.edit_price'])->group(function () {
        Route::get('/autorizaciones/pendientes', [DiscountAuthorizationController::class, 'pending'])->name('authorizations.pending');
        Route::post('/autorizaciones/{authorization}/aprobar', [DiscountAuthorizationController::class, 'approve'])->name('authorizations.approve');
        Route::post('/autorizaciones/{authorization}/rechazar', [DiscountAuthorizationController::class, 'reject'])->name('authorizations.reject');
        Route::get('/autorizaciones/conteo', [DiscountAuthorizationController::class, 'count'])->name('authorizations.count');
    });

    // Ventas: listado, detalle, ticket y anulación
    Route::middleware(['permission:sales.create'])->group(function () {
        Route::get('/ventas', [SaleController::class, 'index'])->name('sales.index');

        Route::middleware(['permission:sales.void'])->group(function () {
            Route::post('/ventas/{sale}/anular', [SaleController::class, 'void'])->name('sales.void');
        });

        Route::get('/ventas/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('/ventas/{sale}/ticket', [SaleController::class, 'ticket'])->name('sales.ticket');
    });

    // Caja y Turnos
    Route::middleware(['permission:cash.open'])->group(function () {
        Route::get('/caja', [CashSessionController::class, 'index'])->name('cash.index');
        Route::post('/caja/abrir', [CashSessionController::class, 'store'])->name('cash.open');
        Route::post('/caja/movimiento', [CashSessionController::class, 'movement'])->name('cash.movement');
        Route::post('/caja/cerrar', [CashSessionController::class, 'close'])->name('cash.close')->middleware('permission:cash.close');
    });

    // Inventario y Catálogo Interno (consulta)
    Route::middleware(['permission:inventory.view'])->group(function () {
        Route::get('/inventario', [ProductController::class, 'index'])->name('inventory.index');
        Route::get('/inventario/exportar', [ProductController::class, 'export'])->name('inventory.export');
        Route::get('/inventario/crear', [ProductController::class, 'create'])->name('inventory.create')->middleware('permission:inventory.adjust');
        Route::get('/inventario/{product}', [ProductController::class, 'show'])->name('inventory.show');
        Route::get('/inventario/{product}/etiqueta', [ProductController::class, 'label'])->name('inventory.label');

        Route::get('/lotes', [MaterialLotController::class, 'index'])->name('inventory.lots.index');
        Route::get('/lotes/crear', [MaterialLotController::class, 'create'])->name('inventory.lots.create')->middleware('permission:inventory.adjust');
        Route::get('/lotes/{lot}', [MaterialLotController::class, 'show'])->name('inventory.lots.show');
    });

    // Inventario: edición, ajustes, etiquetas y fundición
    Route::middleware(['permission:inventory.adjust'])->group(function () {
        Route::post('/inventario', [ProductController::class, 'store'])->name('inventory.store');
        Route::get('/inventario/{product}/editar', [ProductController::class, 'edit'])->name('inventory.edit');
        Route::put('/inventario/{product}', [ProductController::class, 'update'])->name('inventory.update');
        Route::delete('/inventario/{product}', [ProductController::class, 'destroy'])->name('inventory.destroy');
        Route::get('/inventario/{product}/ajustar', [ProductController::class, 'showAdjustForm'])->name('inventory.adjust');
        Route::post('/inventario/{product}/ajustar', [ProductController::class, 'adjust'])->name('inventory.adjust.store');
        Route::post('/inventario/{product}/fundir', [ProductController::class, 'melt'])->name('inventory.melt');

        Route::post('/lotes', [MaterialLotController::class, 'store'])->name('inventory.lots.store');
        Route::post('/lotes/{lot}/ajustar', [MaterialLotController::class, 'adjustWeight'])->name('inventory.lots.adjust');
        Route::post('/lotes/{lot}/fundir', [MaterialLotController::class, 'melt'])->name('inventory.lots.melt');
    });

    // Conteos físicos de inventario
    Route::middleware(['permission:inventory.view'])->group(function () {
        Route::get('/conteos', [StockCountController::class, 'index'])->name('stock-counts.index');
        Route::get('/conteos/{stockCount}', [StockCountController::class, 'show'])->name('stock-counts.show');
    });

    Route::middleware(['permission:inventory.adjust'])->group(function () {
        Route::post('/conteos', [StockCountController::class, 'store'])->name('stock-counts.store');
        Route::post('/conteos/{stockCount}/registrar', [StockCountController::class, 'record'])->name('stock-counts.record');
        Route::post('/conteos/{stockCount}/finalizar', [StockCountController::class, 'finish'])->name('stock-counts.finish');
        Route::post('/conteos/{stockCount}/aplicar', [StockCountController::class, 'apply'])->name('stock-counts.apply');
    });

    // Compra de Metales: consulta
    Route::middleware(['permission:purchases.create'])->group(function () {
        Route::get('/compras', [MetalPurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/compras/{metalPurchase}/comprobante', [MetalPurchaseController::class, 'receipt'])->name('purchases.receipt');
    });

    // Compra de Metales: registro (requiere turno de caja abierto)
    Route::middleware(['permission:purchases.create', 'cash.session'])->group(function () {
        Route::get('/compras/crear', [MetalPurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/compras', [MetalPurchaseController::class, 'store'])->name('purchases.store');
        Route::post('/compras/cotizar', [MetalPurchaseController::class, 'quote'])->name('purchases.quote');
    });

    // Compra de Metales: aprobación, rechazo y ajustes (Gerente / Dueño)
    Route::middleware(['permission:purchases.approve'])->group(function () {
        Route::post('/compras/{metalPurchase}/aprobar', [MetalPurchaseController::class, 'approve'])->name('purchases.approve');
        Route::post('/compras/{metalPurchase}/rechazar', [MetalPurchaseController::class, 'reject'])->name('purchases.reject');
        Route::get('/compras/{metalPurchase}/ajustar', [MetalPurchaseController::class, 'adjustForm'])->name('purchases.adjust');
        Route::put('/compras/{metalPurchase}/ajustar', [MetalPurchaseController::class, 'adjust'])->name('purchases.adjust.store');
    });

    Route::get('/compras/{metalPurchase}', [MetalPurchaseController::class, 'show'])->name('purchases.show');

    // Apartados (Layaways)
    Route::middleware(['permission:layaways.manage'])->group(function () {
        Route::get('/apartados', [LayawayController::class, 'index'])->name('layaways.index');

        Route::middleware(['cash.session'])->group(function () {
            Route::get('/apartados/crear', [LayawayController::class, 'create'])->name('layaways.create');
            Route::post('/apartados', [LayawayController::class, 'store'])->name('layaways.store');
        });

        Route::get('/apartados/{layaway}', [LayawayController::class, 'show'])->name('layaways.show');

        Route::middleware(['cash.session'])->group(function () {
            Route::post('/apartados/{layaway}/abonar', [LayawayController::class, 'addPayment'])->name('layaways.payment');
            Route::post('/apartados/{layaway}/entregar', [LayawayController::class, 'complete'])->name('layaways.complete');
        });

        Route::post('/apartados/{layaway}/cancelar', [LayawayController::class, 'cancel'])->name('layaways.cancel');
    });

    // Clientes
    Route::middleware(['permission:customers.manage'])->group(function () {
        Route::get('/clientes', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/clientes/crear', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/clientes', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/clientes/buscar', [CustomerController::class, 'search'])->name('customers.search');
        Route::get('/clientes/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/clientes/{customer}/editar', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/clientes/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/clientes/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::get('/clientes/{customer}/foto', [CustomerController::class, 'photo'])->name('customers.photo');
    });

    // Precio del Metal
    Route::get('/precios', [MetalPriceController::class, 'index'])->name('prices.index');

    // Precio del Metal: actualización y override manual (Dueño y Gerente)
    Route::middleware(['permission:metal_price.override'])->group(function () {
        Route::post('/precios/actualizar', [MetalPriceController::class, 'refresh'])->name('prices.refresh');
        Route::post('/precios/manual', [MetalPriceController::class, 'setManual'])->name('prices.manual');
    });

    // Precio del Metal: descuentos de compra (solo Dueño)
    Route::middleware(['permission:settings.manage'])->group(function () {
        Route::get('/precios/descuentos', [MetalPriceController::class, 'purchaseSettings'])->name('prices.purchase_settings');
        Route::put('/precios/descuentos', [MetalPriceController::class, 'updatePurchaseSettings'])->name('prices.purchase_settings.update');
    });

    // Reportes (Dueño y Gerente)
    Route::middleware(['permission:reports.view'])->group(function () {
        Route::get('/reportes', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reportes/{report}', [ReportController::class, 'show'])
            ->whereIn('report', ['ventas', 'compras', 'inventario', 'caja'])
            ->name('reports.show');
        Route::get('/reportes/{report}/exportar/{format}', [ReportController::class, 'export'])
            ->whereIn('report', ['ventas', 'compras', 'inventario', 'caja'])
            ->whereIn('format', ['csv', 'pdf'])
            ->name('reports.export');
    });

    // Auditoría (Dueño y Gerente)
    Route::get('/auditoria', [AuditController::class, 'index'])->name('audit.index')->middleware('role:Dueño|Gerente');

    // Usuarios y Permisos (Dueño con users.manage)
    Route::middleware(['permission:users.manage'])->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
        Route::post('/usuarios', [UserController::class, 'store'])->name('users.store');
        Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/usuarios/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle_status');
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Configuración del Sistema
    Route::get('/configuracion', function () {
        return view('admin.settings.index');
    })->name('settings.index');
});
