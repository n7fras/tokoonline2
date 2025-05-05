<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RajaOngkirController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // return view('welcome');
    return redirect()->route('beranda');
});
Route::get('backend/beranda', [BerandaController::class, 'berandaBackend'])->name('backend.beranda');
Route::get('backend/login', [LoginController::class, 'loginBackend'])->name('backend.login');
Route::post('backend/login', [LoginController::class, 'authenticateBackend'])->name('backend.login.authenticate');
Route::post('backend/logout', [LoginController::class, 'logoutBackend'])->name('backend.logout');

// Route::resource('backend/user', UserController::class)->middleware('auth');
Route::resource('/backend/user', UserController::class, ['as' => 'backend'])->middleware('auth');
// Route::resource('backend/user', UserController::class)->middleware('auth');

Route::resource('backend/kategori', KategoriController::class, ['as' => 'backend'])->middleware('auth');
Route::resource('backend/produk', ProdukController::class, ['as' => 'backend'])->middleware('auth');
// Route untuk menambahkan foto
Route::post('foto-produk/store', [ProdukController::class, 'storeFoto'])->name('backend.foto_produk.store')->middleware('auth');
// Route untuk menghapus foto
Route::delete('foto-produk/{id}', [ProdukController::class, 'destroyFoto'])->name('backend.foto_produk.destroy')->middleware('auth');
Route::get('backend/laporan/formuser', [UserController::class, 'formUser'])->name('backend.laporan.formuser')->middleware('auth');
Route::post('backend/laporan/cetakuser', [UserController::class, 'cetakUser'])->name('backend.laporan.cetakuser')->middleware('auth');

Route::get('backend/laporan/formproduk', [ProdukController::class, 'formProduk'])->name('backend.laporan.formproduk')->middleware('auth');
Route::post('backend/laporan/cetakproduk', [ProdukController::class, 'cetakProduk'])->name('backend.laporan.cetakproduk')->middleware('auth');

// Frontend
Route::get('/beranda',[BerandaController::class, 'index'])->name('beranda');
Route::get('/produk/detail/{id}', [ProdukController::class, 'detail'])->name('produk.detail'); 
Route::get('/produk/kategori/{id}', [ProdukController::class, 'produkKategori'])->name('produk.kategori');
Route::get('/produk/all', [ProdukController::class, 'produkAll'])->name('produk.all'); 

//API Google 
Route::get('/auth/redirect', [CustomerController::class, 'redirect'])->name('auth.redirect'); 
Route::get('/auth/google/callback', [CustomerController::class, 'callback'])->name('auth.callback'); 
// Logout 
Route::post('/logout', [CustomerController::class, 'logout'])->name('customer.logout'); 
// Buat route /login agar Laravel bisa redirect ke sini saat user belum login
Route::get('/login', function () {
    return redirect()->route('auth.redirect'); // langsung redirect ke login Google
})->name('login');


//Route Customer
Route::resource('backend/customer', CustomerController::class, ['as' => 'backend'])->middleware('auth'); 
// Route untuk menampilkan halaman akun customer 
Route::get('/customer/akun/{id}', [CustomerController::class, 'akun'])->name('customer.akun')->middleware('is.customer'); 
Route::put('/customer/akun/{id}/update', [CustomerController::class, 'updateAkun'])->name('customer.akun.update')->middleware('is.customer'); 

// Group route untuk customer 
Route::middleware('is.customer')->group(function () { 
    // Route untuk menampilkan halaman akun customer 
    Route::get('/customer/akun/{id}', [CustomerController::class, 'akun']) 
        ->name('customer.akun'); 
 
    // Route untuk mengupdate data akun customer 
    Route::put('/customer/updateakun/{id}', [CustomerController::class, 'updateAkun']) 
        ->name('customer.updateakun'); 
 
    // Route untuk menambahkan produk ke keranjang 
    Route::post('add-to-cart/{id}', [OrderController::class, 'addToCart'])->name('order.addToCart'); 
    Route::get('cart', [OrderController::class, 'viewCart'])->name('order.cart'); 
    Route::post('cart/update/{item_id}', [OrderController::class, 'updateCart'])->name('order.updateCart');

}); 
//  #cek api rajaongkir
//  Route::get('/cek-ongkir', function () {
//     $baseurl = env('RAJAONGKIR_BASE_URL');
   

//     $response = Http::asForm()->withHeaders([
//         'key' => 'qnFgYX9I5840bbd550a62aa5uv6f1dua',
//         'Accept' => 'application/json'
//     ])->post("https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost", [
//         'origin' => '501',
//         'destination' => '114',
//         'weight' => 1000,
//         'courier' => 'jne'
//     ]);

//     dd($response->json());
// });
Route::get('/cek-ongkir', function () { 
    return view('ongkir'); 
}); 
 
Route::get('/location', [RajaOngkirController::class, 'getLocation']);
Route::post('/cost', [RajaOngkirController::class, 'getCost']);




// Route::get('/search-destination', function () {
//     // Keyword untuk pencarian
//     $keyword = 'jakarta';

//     // Kirim permintaan GET ke API Search Destination
//     $response = Http::withHeaders([
//         'x-api-key' => 'dp3KijAw5840bbd550a62aa5frenxfy4',
//         'accept' => 'application/json',
//     ])->get("https://api-sandbox.collaborator.komerce.id/tariff/api/v1/destination/search?keyword=bekasi");

//     if ($response->status() === 200) {
//         // Jika sukses, tampilkan hasil JSON
//         dd($response->json());
//     } else {
//         // Jika ada error, tampilkan status dan body error
//         dd($response->status(), $response->body());
//     }
// });


