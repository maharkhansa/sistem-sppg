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
use App\Http\Controllers\InvoiceExpenseDetailController;
use App\Http\Controllers\StockReconciliationController;
use App\Http\Controllers\Finance\ChartOfAccountController;
use App\Http\Controllers\Finance\JournalEntryController;
use App\Http\Controllers\Finance\FinanceReportController;

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

Route::resource('categories', CategoryController::class)
    ->except(['show', 'destroy']);

Route::patch(
    '/categories/{category}/toggle-status',
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
    '/suppliers/{supplier}/toggle-status',
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
    '/items/{item}/toggle-status',
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
    '/kitchens/{kitchen}/toggle-status',
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
    '/expense-types/{expenseType}/toggle-status',
    [ExpenseTypeController::class, 'toggleStatus']
)->name('expense-types.toggle-status');

/*
|--------------------------------------------------------------------------
| Stock Transactions - Barang Masuk
|--------------------------------------------------------------------------
*/

Route::get(
    '/stock-transactions',
    [StockTransactionController::class, 'index']
)->name('stock-transactions.index');

Route::get(
    '/stock-transactions/create',
    [StockTransactionController::class, 'create']
)->name('stock-transactions.create');

Route::post(
    '/stock-transactions',
    [StockTransactionController::class, 'store']
)->name('stock-transactions.store');

Route::get(
    '/stock-transactions/{stockTransaction}/edit-in',
    [StockTransactionController::class, 'editIn']
)->name('stock-transactions.edit-in');

/*
|--------------------------------------------------------------------------
| Stock Transactions - Barang Keluar
|--------------------------------------------------------------------------
*/

Route::get(
    '/stock-transactions/out',
    [StockTransactionController::class, 'outIndex']
)->name('stock-transactions.out');

Route::get(
    '/stock-transactions/{stockTransaction}/edit',
    [StockTransactionController::class, 'edit']
)->name('stock-transactions.edit');

Route::put(
    '/stock-transactions/{stockTransaction}',
    [StockTransactionController::class, 'update']
)->name('stock-transactions.update');

/*
|--------------------------------------------------------------------------
| Detail dan Hapus Transaksi
|--------------------------------------------------------------------------
*/

Route::get(
    '/stock-transactions/{stockTransaction}',
    [StockTransactionController::class, 'show']
)->name('stock-transactions.show');

Route::delete(
    '/stock-transactions/{id}',
    [StockTransactionController::class, 'destroy']
)->name('stock-transactions.destroy');

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
| Stock Reconciliation - Rekonsiliasi Stok
|--------------------------------------------------------------------------
| Halaman audit untuk membandingkan saldo stok saat ini dengan histori
| barang masuk dan barang keluar. Route ini tidak melakukan koreksi stok.
*/

Route::get(
    '/rekonsiliasi-stok',
    [StockReconciliationController::class, 'index']
)->name('stock-reconciliation.index');

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
    '/purchase-orders/{purchaseOrder}/process',
    [PurchaseOrderController::class, 'process']
)->name('purchase-orders.process');

Route::get(
    '/purchase-orders/{purchaseOrder}/daftar-pesanan',
    [PurchaseOrderController::class, 'daftarPesanan']
)->name('purchase-orders.daftar-pesanan');

Route::get(
    '/purchase-orders/{purchaseOrder}/delivery-note',
    [PurchaseOrderController::class, 'deliveryNote']
)->name('purchase-orders.delivery-note');

/*
|--------------------------------------------------------------------------
| Invoices
|--------------------------------------------------------------------------
*/

Route::get(
    '/invoices',
    [InvoiceController::class, 'index']
)->name('invoices.index');

Route::get(
    '/invoices/{invoice}/expenses/edit',
    [InvoiceExpenseDetailController::class, 'edit']
)->name('invoices.expenses.edit');

Route::put(
    '/invoices/{invoice}/expenses',
    [InvoiceExpenseDetailController::class, 'update']
)->name('invoices.expenses.update');

Route::get(
    '/invoices/{invoice}',
    [InvoiceController::class, 'show']
)->name('invoices.show');

Route::post(
    '/stock-transactions/{stockTransaction}/create-invoice',
    [InvoiceController::class, 'createFromOut']
)->name('stock-transactions.create-invoice');

/*
|--------------------------------------------------------------------------
| Nota Keluar
|--------------------------------------------------------------------------
*/

Route::get(
    '/nota-keluars',
    [NotaKeluarController::class, 'index']
)->name('nota-keluars.index');

Route::get(
    '/nota-keluars/{notaKeluar}/cetak',
    [NotaKeluarController::class, 'print']
)->name('nota-keluars.print');

Route::get(
    '/nota-keluars/{notaKeluar}',
    [NotaKeluarController::class, 'show']
)->name('nota-keluars.show');

Route::post(
    '/stock-transactions/{stockTransaction}/create-nota',
    [NotaKeluarController::class, 'createFromOut']
)->name('stock-transactions.create-nota');

/*
|--------------------------------------------------------------------------
| LPDH
|--------------------------------------------------------------------------
*/

Route::post(
    '/lpdhs/invoice-total',
    [LPDHController::class, 'invoiceTotal']
)->name('lpdhs.invoice-total');

Route::resource('lpdhs', LPDHController::class);

/*
|--------------------------------------------------------------------------
| Invoice - Alokasi Nota
|--------------------------------------------------------------------------
*/

Route::get(
    '/invoices/{invoice}/nota-allocation',
    [InvoiceController::class, 'notaAllocation']
)->name('invoices.nota-allocation');

Route::post(
    '/invoices/{invoice}/nota-allocation',
    [InvoiceController::class, 'saveNotaAllocation']
)->name('invoices.nota-allocation.save');


/*
|--------------------------------------------------------------------------
| Akuntansi - Daftar Akun dan Jurnal Umum
|--------------------------------------------------------------------------
*/

Route::prefix('keuangan')->name('finance.')->group(function () {
    Route::get('/akun', [
        ChartOfAccountController::class,
        'index',
    ])->name('accounts.index');

    Route::get('/jurnal', [
        JournalEntryController::class,
        'index',
    ])->name('journals.index');

    Route::get('/jurnal/create', [
        JournalEntryController::class,
        'create',
    ])->name('journals.create');

    Route::post('/jurnal', [
        JournalEntryController::class,
        'store',
    ])->name('journals.store');

    Route::get('/jurnal/{journalEntry}', [
        JournalEntryController::class,
        'show',
    ])->name('journals.show');

    Route::post('/jurnal/{journalEntry}/posting', [
        JournalEntryController::class,
        'post',
    ])->name('journals.post');
});

/*
|--------------------------------------------------------------------------
| Keuangan - Kas, Bank, Neraca, dan Laba Rugi
|--------------------------------------------------------------------------
*/

Route::prefix('keuangan')->name('finance.')->group(function () {
    Route::get('/kas-bank', [
        FinanceReportController::class,
        'cashBank',
    ])->name('cash-bank');

    Route::get('/laba-rugi', [
        FinanceReportController::class,
        'profitLoss',
    ])->name('profit-loss');

    Route::get('/neraca', [
        FinanceReportController::class,
        'balanceSheet',
    ])->name('balance-sheet');
});

