<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\AuthController;
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
Route::get('/', function () {
    return view('public.home');
})->name('home');

Route::get('/catalogo', function () {
    return view('public.catalog');
})->name('public.catalog');

Route::get('/pieza/{id}', function ($id) {
    return view('public.product-detail', ['id' => $id]);
})->name('public.product');

Route::get('/vender-oro', function () {
    return view('public.sell-gold');
})->name('public.sell_gold');

Route::get('/sucursal', function () {
    return view('public.store');
})->name('public.store');

Route::get('/contacto', function () {
    return view('public.contact');
})->name('public.contact');

// =========================================================================
// 2. AUTENTICACIÓN
// =========================================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
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

    // Punto de Venta (POS)
    Route::get('/pos', function () {
        return view('admin.pos.index');
    })->name('pos.index');

    // Caja y Turnos
    Route::get('/caja', function () {
        return view('admin.cash.index');
    })->name('cash.index');

    // Inventario y Catálogo Interno
    Route::get('/inventario', function () {
        return view('admin.inventory.index');
    })->name('inventory.index');

    // Compra de Metales
    Route::get('/compras', function () {
        return view('admin.purchases.index');
    })->name('purchases.index');

    // Apartados (Layaways)
    Route::get('/apartados', function () {
        return view('admin.layaways.index');
    })->name('layaways.index');

    // Clientes
    Route::get('/clientes', function () {
        return view('admin.customers.index');
    })->name('customers.index');

    // Precio del Metal
    Route::get('/precios', function () {
        return view('admin.prices.index');
    })->name('prices.index');

    // Reportes
    Route::get('/reportes', function () {
        return view('admin.reports.index');
    })->name('reports.index');

    // Auditoría
    Route::get('/auditoria', function () {
        return view('admin.audit.index');
    })->name('audit.index');

    // Usuarios y Permisos
    Route::get('/usuarios', function () {
        return view('admin.users.index');
    })->name('users.index');

    // Configuración del Sistema
    Route::get('/configuracion', function () {
        return view('admin.settings.index');
    })->name('settings.index');
});
