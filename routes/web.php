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


/*
|--------------------------------------------------------------------------
| Dashboard / Welcome
|--------------------------------------------------------------------------
*/

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
    ->except(['show']);

Route::patch(
    'items/{item}/toggle-status',
    [ItemController::class, 'toggleStatus']
)->name('items.toggle-status');

Route::post(
    '/items/import',
    [ItemController::class, 'import']
)->name('items.import');


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
|
| Barang Masuk:
| - index  : daftar barang masuk
| - create : form tambah barang masuk
| - store  : simpan barang masuk
| - show   : detail transaksi barang masuk
|
| Barang Keluar:
| - outIndex : daftar barang keluar
| - edit     : edit barang keluar
| - update   : update barang keluar
|
*/


// Daftar Barang Masuk
Route::get(
    '/stock-transactions',
    [StockTransactionController::class, 'index']
)->name('stock-transactions.index');


// Form Tambah Barang Masuk
Route::get(
    '/stock-transactions/create',
    [StockTransactionController::class, 'create']
)->name('stock-transactions.create');


// Simpan Barang Masuk
Route::post(
    '/stock-transactions',
    [StockTransactionController::class, 'store']
)->name('stock-transactions.store');


// Daftar Barang Keluar
Route::get(
    '/stock-transactions/out',
    [StockTransactionController::class, 'outIndex']
)->name('stock-transactions.out');


// Edit Barang Keluar
Route::get(
    '/stock-transactions/{stockTransaction}/edit',
    [StockTransactionController::class, 'edit']
)->name('stock-transactions.edit');


// Update Barang Keluar
Route::put(
    '/stock-transactions/{stockTransaction}',
    [StockTransactionController::class, 'update']
)->name('stock-transactions.update');


// Detail Transaksi Barang Masuk
Route::get(
    '/stock-transactions/{stockTransaction}',
    [StockTransactionController::class, 'show']
)->name('stock-transactions.show');


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
    ->only([
        'index',
        'create',
        'store',
    ]);

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


// Buat Invoice otomatis dari Barang Keluar
Route::post(
    '/stock-transactions/{stockTransaction}/create-invoice',
    [InvoiceController::class, 'createFromOut']
)->name('stock-transactions.create-invoice');


/*
|--------------------------------------------------------------------------
| Nota Keluar
|--------------------------------------------------------------------------
*/

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


// Buat Nota Keluar otomatis dari Barang Keluar
Route::post(
    '/stock-transactions/{stockTransaction}/create-nota',
    [NotaKeluarController::class, 'createFromOut']
)->name('stock-transactions.create-nota');


/*
|--------------------------------------------------------------------------
| LPDH
|--------------------------------------------------------------------------
*/

// Total Invoice
Route::get(
    'lpdhs/invoice-total',
    [LPDHController::class, 'invoiceTotal']
)->name('lpdhs.invoice-total');


// Resource LPDH
Route::resource(
    'lpdhs',
    LPDHController::class
);
