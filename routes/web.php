<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\CoinController;
use App\Http\Controllers\ViewController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CoinListController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('sessionAuthWeb')->prefix('/')->group(function () {
    // Route::get('/', [ViewController::class, 'index_page_view'])->name('HomePage');

    Route::post('login-user', [UserController::class, 'admin_login'])->name('LoginUser');
});


Route::middleware('sessionAuthWeb')->prefix('/admin/')->group(function () {
    
    Route::get('users', [UserController::class, 'index'])->name('AllUsers');
    Route::get('logout', [UserController::class, 'logout'])->name('Logout');
    
});
Route::middleware("sessionAuthWeb")->prefix('/admin/')->group(function () {
    Route::get("categories", [CategoryController::class, 'index'])->name("AllCategaries");
    Route::get("active-categories", [CategoryController::class, 'get_active'])->name("GetCategories");
    Route::get("create-category", [CategoryController::class, 'create'])->name("CreateCategory");
    Route::post("category", [CategoryController::class, 'store'])->name("AddCategory");
    Route::get("edit-category/{category}", [CategoryController::class, 'edit'])->name("EditCategoryPage");
    Route::post("update-category/{category}", [CategoryController::class, 'update'])->name("UpdateCategory");
    Route::post("status-category/{category}", [CategoryController::class, 'status'])->name("CategoryStatus");
    Route::get("delete-category/{category}", [CategoryController::class, 'destroy'])->name("DeleteCategory");
    
    Route::get("coin-lists", [CoinListController::class, 'index'])->name("AllCoinsList");
    Route::post("add-coin-list", [CoinListController::class, 'store'])->name("AddCoinList");
    Route::post("update-coin-list/{coinList}", [CoinListController::class, 'update'])->name("UpdateCoinListPage");
    Route::get("create-coin-list", [CoinListController::class, 'create'])->name("CreateCoinList");
    Route::get("edit-coin-list/{coinList}", [CoinListController::class, 'edit'])->name("EditCoinList");
    Route::post("status-coin-list/{coinList}", [CoinListController::class, 'status'])->name("CoinListStatus");
    Route::get("delete-coin-list/{coinList}", [CoinListController::class, 'destroy'])->name("DeleteCoinList");

    
});