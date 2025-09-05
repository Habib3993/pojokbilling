<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\RouterController; 
use App\Http\Controllers\IpPoolController; 
use App\Http\Controllers\MapController;
use App\Http\Controllers\InventoryMapController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\GenieAcsServerController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\OltController;
use App\Http\Controllers\VlanController;
use App\Http\Controllers\RechargeController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LogController; 
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LayerGroupController;
use App\Http\Controllers\RadiusController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Semua rute yang butuh login, sekarang ada di dalam satu grup ini.
Route::middleware('auth')->group(function () {

    // --- Rute dasboard ---
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['verified'])->name('dashboard');
    Route::get('dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    
    // --- Rute Profil Pengguna ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Rute Peta Pelanggan (Umum) ---
    Route::get('/map', [MapController::class, 'index'])->name('map.index');
    Route::post('/map/store', [MapController::class, 'store'])->name('map.store');

    // =========================================================================
    // --- Rute Peta Inventaris (Sudah Diamankan Sesuai Rencana) ---
    // =========================================================================
    Route::prefix('inventory-map')->name('inventory.map.')->group(function () {
        Route::get('/', [InventoryMapController::class, 'index'])->name('index')->middleware('can:view map_data');
        Route::post('/points', [InventoryMapController::class, 'storePoint'])->name('storePoint')->middleware('can:create map_data');
        Route::post('/polylines', [InventoryMapController::class, 'storePolyline'])->name('storePolyline')->middleware('can:create map_data');
        Route::patch('/points/{mapPoint}', [InventoryMapController::class, 'updatePoint'])->name('updatePoint')->middleware('can:edit map_data');
        Route::patch('/polylines/{mapPolyline}', [InventoryMapController::class, 'updatePolyline'])->name('updatePolyline')->middleware('can:edit map_data');
        Route::delete('/points/{mapPoint}', [InventoryMapController::class, 'destroyPoint'])->name('destroyPoint')->middleware('can:delete map_data');
        Route::delete('/polylines/{mapPolyline}', [InventoryMapController::class, 'destroyPolyline'])->name('destroyPolyline')->middleware('can:delete map_data');
    });

    // =========================================================================
    // --- Rute Pelanggan (Sudah Diamankan Sesuai Rencana) ---
    // =========================================================================
    Route::controller(CustomerController::class)->prefix('customers')->name('customers.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:view customers');
        Route::get('/create', 'create')->name('create')->middleware('can:create customers');
        Route::post('/', 'store')->name('store')->middleware('can:create customers');
        Route::get('/active', 'active')->name('active')->middleware('can:view customers');
        Route::get('/inactive', 'inactive')->name('inactive')->middleware('can:view customers');
        Route::post('/sync', 'syncWithMikrotik')->name('sync')->middleware('can:edit customers');
        Route::get('/{customer}', 'show')->name('show')->middleware('can:view customers');
        Route::get('/{customer}/edit', 'edit')->name('edit')->middleware('can:edit customers');
        Route::match(['PUT', 'PATCH'], '/{customer}', 'update')->name('update')->middleware('can:edit customers');
        Route::delete('/{customer}', 'destroy')->name('destroy')->middleware('can:delete customers');
        Route::post('/{customer}/sync-radius', 'syncRadius')->name('sync_radius')->middleware('can:edit customers');
    });

    // =========================================================================
    // --- Rute Lainnya (Syntax Diperbaiki & Diberi Proteksi Dasar) ---
    // =========================================================================

    // Layer Groups (Hanya Superadmin yang boleh kelola)
    Route::resource('layer-groups', LayerGroupController::class)->middleware('can:manage layer_groups');

    // Resource Controllers (Nanti bisa disesuaikan dengan izin 'can:')
    Route::resource('transactions', TransactionController::class); // TODO: Proteksi dengan 'can:'
    Route::resource('users', UserController::class); // TODO: Proteksi dengan 'can:'
    
    // Rute yang hanya bisa diakses oleh Superadmin & Admin (Sintaks diperbaiki)
    Route::middleware('role:superadmin|admin')->group(function () {
        Route::resource('genieacs-servers', GenieAcsServerController::class);
        Route::resource('packages', PackageController::class);
        Route::resource('routers', RouterController::class);
        Route::resource('ip-pools', IpPoolController::class);
        Route::resource('olts', OltController::class);
        Route::resource('vlans', VlanController::class);
        Route::resource('radius', RadiusController::class); // Note: '/radius' diubah jadi 'radius'
    });

    // Rute tunggal lainnya
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/recharge', [RechargeController::class, 'create'])->name('recharge.create');
    Route::post('/recharge', [RechargeController::class, 'store'])->name('recharge.store');
    Route::get('/recharge/customer/{customer}', [RechargeController::class, 'getCustomerDetails'])->name('recharge.customer.details');
    Route::get('/whatsapp/private', [WhatsappController::class, 'createPrivate'])->name('whatsapp.private.create');
    Route::post('/whatsapp/private', [WhatsappController::class, 'sendPrivate'])->name('whatsapp.private.send');
    Route::get('/whatsapp/bulk', [WhatsappController::class, 'createBulk'])->name('whatsapp.bulk.create');
    Route::post('/whatsapp/bulk', [WhatsappController::class, 'sendBulk'])->name('whatsapp.bulk.send');
    Route::get('/settings/general', [SettingController::class, 'general'])->name('settings.general.index');
    Route::post('/settings/general', [SettingController::class, 'storeGeneral'])->name('settings.general.store');
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('/backup', [BackupController::class, 'create'])->name('backup.create');
    Route::get('/backup/download/{fileName}', [BackupController::class, 'download'])->name('backup.download');
    Route::delete('/backup/{fileName}', [BackupController::class, 'destroy'])->name('backup.destroy');
    Route::get('/invoices/{payment}/download', [RechargeController::class, 'downloadInvoice'])->name('invoices.download');
    Route::get('/ip-pools/sync/{routerId}', [IpPoolController::class, 'sync'])->name('ip-pools.sync');
    Route::get('/routers/{router}/status', [RouterController::class, 'getStatus'])->name('routers.status');
    Route::get('/packages/sync/{routerId}', [PackageController::class, 'sync'])->name('packages.sync');
});

require __DIR__.'/auth.php';
