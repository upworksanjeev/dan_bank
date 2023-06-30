<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\CoinController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CoinCategoryController;
use App\Http\Controllers\CoinListController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use App\Models\Setting;

Route::post("save-pn-token", [AdminController::class, 'save_token'])->name("SavePNToken");
Route::get("check-token", [AdminController::class, 'check_token'])->name("CheckToken");

Route::middleware("sessionAuth")->group(function () {
    // User Controller methods
        Route::post('login-admin', [UserController::class, 'admin_login'])->name('LoginAdmin');
        Route::post("login", [UserController::class, 'login'])->name("Login");
        Route::post("register", [UserController::class, 'store'])->name("Register");
        Route::post("verify-email", [UserController::class, 'verify_email'])->name("VerifyEmail");
        Route::post("reset-password", [UserController::class, 'reset_password_request'])->name("ResetPasswordRequest");
        Route::post("update-password", [UserController::class, 'update_password'])->name("UpdatePassword");
    // User Controller methods

    // Admin Controller methods
        Route::get("logout",[AdminController::class,'logout'])->name('Logout');
    // Admin Controller methods

});
    
    Route::middleware("sessionAuth")->prefix('/admin/')->group(function () {
     
    Route::get('home',[AdminController::class,'index'])->name('Home');    
    Route::get('search',[TransactionController::class,'search'])->name('Search');    
    
    Route::post("reset-password-admin", [AdminController::class, 'reset_password_request'])->name("AdminPasswordReset");
    Route::post("update-password-admin", [AdminController::class, 'update_password'])->name("AdminUpdatePassword");
    
    Route::post("update-admin", [AdminController::class, 'update'])->name("AdminUpdate");
    Route::get('me',[UserController::class,'me'])->name('MeAdmin');

    Route::get("categories", [CategoryController::class, 'index'])->name("AllCategories");
    Route::get("active-categories", [CategoryController::class, 'get_active'])->name("GetCategories");
    Route::post("category", [CategoryController::class, 'store'])->name("CreateCategory");
    Route::post("update-category/{category}", [CategoryController::class, 'update'])->name("UpdateCategory");
    Route::get("delete-category/{category}", [CategoryController::class, 'destroy'])->name("DeleteCategory");
    
    Route::get("coins", [CoinController::class, 'getcoins'])->name("GetCoins");

    Route::get("coin-categories", [CoinCategoryController::class, 'index'])->name("GetCoinCategories");

    Route::get("friends", [FriendController::class, 'index'])->name("AllFriends");
    
    Route::get("transactions", [TransactionController::class, 'index'])->name("AllTransactions");
    
    Route::get("coin-lists", [CoinListController::class, 'index'])->name("AllCoinsList");
    Route::post("coin-list", [CoinListController::class, 'store'])->name("CreateCoinList");
    Route::post("update-coin-list/{coinList}", [CoinListController::class, 'update'])->name("UpdateCoinList");
    
    Route::post("update-setting", [SettingController::class, 'update'])->name("UpdateSetting");
    Route::get("settings", [SettingController::class, 'index'])->name("GetSettings");
    Route::get("users",[UserController::class,'index'])->name("GetUsers");
});

Route::middleware("sessionAuth")->prefix('/user/')->group(function () {
    // User Controller methods
        Route::post("update-device-token", [UserController::class, 'update_device_token'])->name("UpdateDeviceToken");
        Route::get("me", [UserController::class, 'current_user'])->name("CurrentUser");
        Route::get("show-user/{user}", [UserController::class, 'show'])->name("UserInfo");
        Route::post("update-user", [UserController::class, 'update'])->name("UpdateUser");
        Route::post("search-users", [UserController::class, 'search_users'])->name("SearchUsers");
        Route::post("complete-stripe-account", [UserController::class, 'complete_stripe_account'])->name("CompleteStripeAccount");
        Route::post("add-bank-account", [UserController::class, 'add_bank_account'])->name("AddBankAccount");
        Route::get("check-user-profile-status", [UserController::class, 'check_user_profile_status'])->name("CheckUserProfileStatus");
        Route::get("user-details", [UserController::class, 'get_user_details'])->name("GetUserDetails");
    // User Controller methods

    // Friend Controller methods
        Route::post("my-friends", [FriendController::class, 'index'])->name("MyFriends");
        Route::post("add-friend/{user}", [FriendController::class, 'store'])->name("AddFriend");
        Route::post("update-friend-request/{friend}", [FriendController::class, 'update'])->name("AddFriend");
    // Friend Controller methods

    // Coin Controller methods
        Route::post("send-coins", [CoinController::class, 'store'])->name("SendCoin");
        Route::get("my-coins", [CoinController::class, 'index'])->name("MyCoins");
        Route::get("open-coin/{coin}", [CoinController::class, 'open_coin'])->name("OpenCoin");
        Route::post("deduct-amount/{transaction}", [CoinController::class, 'deduct_amount'])->name("DeductAmount");
        Route::get("send-coins", [CoinController::class, 'from_coins_list'])->name("SendCoins");
        Route::post("payment-intent/{coin}", [CoinController::class, 'create_payment_intent'])->name("PaymentIntent");
    // Coin Controller methods
});