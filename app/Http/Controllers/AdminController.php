<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Mail\UserResetPasswordMail;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Coin;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $users = User::all()->count();

       $coins = Coin::all()->count();

       $categories = Category::all()->count();

       $transactions = Transaction::all()->count();

       return response()->json(['users'=>$users,'coins' => $coins, 'categories' => $categories,'transactions' => $transactions]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAdminRequest $request)
    {  
        $admin = Admin::find(request()->admin->admin_id);
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->password = Hash::make($request->password);
        if($admin->save()) return api_success1( $admin );
         return api_error();
    }

    public function reset_password_request(ResetPasswordRequest $request)
    {
        $admin = Admin::where('email', $request->email)->first();
        if ($admin) {
            // $admin->password_verification_token = 4444;
            $admin->password_verification_token = rand(1000, 9999);
            if ($admin->save()) {
                Mail::to($admin->email)->send(new UserResetPasswordMail($admin));
                if (count(Mail::failures()) > 0) return api_error('Reset Password email couldn\'t send!', 400);

                return api_success1('4 digit code has been sent to your email for resetting your password!');
            }
        }
        return api_error('No account found against given email!');
    }

    public function update_password(UpdatePasswordRequest $request)
    {
        $admin = Admin::where('password_verification_token', $request->token)->first();
        if ($admin) {
            $admin->password_verification_token = 0;
            $admin->password = Hash::make($request->password);
            if ($admin->save()) {
                return api_success1('Your password has been updated successfully!');
            }
        }
        return api_error('Invalid token!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function logout(Request $request)
    {
        if ($request->login_attempt) {
            $request->login_attempt->access_expiry = date("Y-m-d H:i:s");
            $request->login_attempt->save();
        }
        $admin = Admin::where('admin_id', $request->login_attempt->user_id)->first();
        if ($admin) {
            $admin->device_token = NULL; 
            $admin->save();

        } else {
            $user = User::where('user_id', $request->login_attempt->user_id)->first();
            $user->device_token = NULL; 
            $user->save();
        }
         return api_success1("Logout Successfully!");
    }

    public function save_token(Request $request)
    {
        Admin::where('email', 'admin@gmail.com')->update(['device_token' => $request->token]);
        return api_success1('Token updated');
    }

    public function check_token ()
    {
        $found = Admin::where('email', 'admin@gmail.com')->where('device_token', '!=', '')->first();

        if ($found) return api_success1('Found Token');
        else return api_error('Token not found!');
    }
}
