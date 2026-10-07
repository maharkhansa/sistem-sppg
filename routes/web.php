<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotaKeluarController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\LPDHController;

Route::get('/', function () {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

Route::resource('categories', CategoryController::class)
    ->except(['show', 'destroy']);

Route::patch(
    'categories/{category}/toggle-status',
    [CategoryController::class, 'toggleStatus']
)->name('categories.toggle-status');


/*
|--------------------------------------------------------------------------
| Suppliers
|--------------------------------------------------------------------------
*/

Route::resource('suppliers', SupplierController::class)
    ->except(['show', 'destroy']);

Route::patch(
    'suppliers/{supplier}/toggle-status',
    [SupplierController::class, 'toggleStatus']
)->name('suppliers.toggle-status');


/*
|--------------------------------------------------------------------------
| Items
|--------------------------------------------------------------------------
*/

Route::resource('items', ItemController::class)
    ->except(['show', 'destroy']);

Route::patch(
    'items/{item}/toggle-status',
    [ItemController::class, 'toggleStatus']
)->name('items.toggle-status');


/*
|--------------------------------------------------------------------------
| Kitchens
|--------------------------------------------------------------------------
*/

Route::resource('kitchens', KitchenController::class)
    ->except(['show', 'destroy']);

Route::patch(
    'kitchens/{kitchen}/toggle-status',
    [KitchenController::class, 'toggleStatus']
)->name('kitchens.toggle-status');

/*
|--------------------------------------------------------------------------
| Expense Types
|--------------------------------------------------------------------------
*/

Route::resource('expense-types', ExpenseTypeController::class)
    ->except(['show', 'destroy']);

Route::patch(
    'expense-types/{expenseType}/toggle-status',
    [ExpenseTypeController::class, 'toggleStatus']
)->name('expense-types.toggle-status');

/*
|--------------------------------------------------------------------------
| Stock Transactions
|--------------------------------------------------------------------------
*/

Route::resource('stock-transactions', StockTransactionController::class)
    ->only(['index', 'create', 'store']);

Route::get(
    '/stock-transactions/out',
    [StockTransactionController::class, 'outIndex']
)->name('stock-transactions.out');


/*
|--------------------------------------------------------------------------
| Stocks
|--------------------------------------------------------------------------
*/

Route::get(
    '/stocks',
    [StockController::class, 'index']
)->name('stocks.index');


/*
|--------------------------------------------------------------------------
| Purchase Orders
|--------------------------------------------------------------------------
*/

Route::resource('purchase-orders', PurchaseOrderController::class)
    ->only(['index', 'create', 'store']);

Route::post(
    'purchase-orders/{purchaseOrder}/process',
    [PurchaseOrderController::class, 'process']
)->name('purchase-orders.process');


/*
|--------------------------------------------------------------------------
| Invoices
|--------------------------------------------------------------------------
*/

// Daftar Invoice
Route::get(
    '/invoices',
    [InvoiceController::class, 'index']
)->name('invoices.index');

// Lihat satu Invoice
Route::get(
    '/invoices/{invoice}',
    [InvoiceController::class, 'show']
)->name('invoices.show');

// Buat Invoice otomatis dari OUT
Route::post(
    '/stock-transactions/{stockTransaction}/create-invoice',
    [InvoiceController::class, 'createFromOut']
)->name('stock-transactions.create-invoice');

// Daftar Nota Keluar
Route::get(
    '/nota-keluars',
    [NotaKeluarController::class, 'index']
)->name('nota-keluars.index');

// Lihat Nota Keluar
Route::get(
    '/nota-keluars/{notaKeluar}',
    [NotaKeluarController::class, 'show']
)->name('nota-keluars.show');

// Buat Nota Keluar otomatis dari OUT
Route::post(
    '/stock-transactions/{stockTransaction}/create-nota',
    [NotaKeluarController::class, 'createFromOut']
)->name('stock-transactions.create-nota');

Route::post('/items/import', [ItemController::class, 'import'])->name('items.import');

//LPDH
Route::get(
    'lpdhs/invoice-total',
    [LPDHController::class, 'invoiceTotal']
)->name('lpdhs.invoice-total');

Route::resource('lpdhs', LPDHController::class);
Route::resource('lpdhs', LPDHController::class);