<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Cartalyst\Stripe\Laravel\Facades\Stripe;

use App\Http\Requests\UserLogin as UserLoginRequest;
use App\Http\Requests\StoreUser as StoreUserRequest;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\CompleteStripeAccount as CompleteStripeAccountRequest;
use App\Http\Requests\AddBankAccount as AddBankAccountRequest;
use App\Http\Requests\AdminLoginRequest as RequestsAdminLoginRequest;
use App\Mail\UserEmailVerificationLinkMail;
use App\Mail\UserResetPasswordMail;

use App\Models\User;
use App\Models\Admin;
use App\Models\UserDetail;
use App\Models\LoginAttempt;
use App\Models\Friend;

class UserController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(UserLoginRequest $request)
    {
        $user = User::where("email", $request->email)->first();
		if ($user && HASH::check($request->password, $user->password)) {
            if (!$user->email_verified) {
                $user->email_verification_token = rand(1000, 9999);
                // $user->email_verification_token = 4444;
                if ($user->save()) {
                    Mail::to($user->email)->send(new UserEmailVerificationLinkMail($user));
                    if (count(Mail::failures()) > 0) return api_error('Email verification email couldn\'t send!', 400);
        
                    return api_error('Your email is not verified! Email Verification Number has been sent to your email!');
                }
            } else if (!$user->active) {
                return api_error('Your account is not active! Please contact support staff for further details.');

            }

            request()->user = $user;
            $login_attempt = new LoginAttempt();
            $login_attempt->user_id = $user->user_id;
            $login_attempt->access_token = generate_token($user);
            $login_attempt->access_expiry = date("Y-m-d H:i:s", strtotime("1 year"));

            if (!$login_attempt->save()) return api_error();

            $data = array(
                'message' => 'Login Successfully',
                'detail' => $user,
                'token_detail' => array (
                    'access_token' => $login_attempt->access_token,
                    'token_type' => 'Bearer'
                )
            );
            return api_success('User LoggedIn Successfully', $data);
        }
        return api_error('Invalid Email or Password', 400);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        if (!is_dir(public_path("assets/users/"))) {
            mkdir(public_path("assets/users/"), 0777, true);
        }

        $user = new User;
        $user->user_id = (string) Str::uuid();
        $user->username = generate_unique_username($request->name);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->addFlag(User::FLAG_ACTIVE);

        mkdir(public_path("assets/users/" . $user->user_id), 0777, true);

        // if ($request->has('logo_image') && $request->filled('logo_image')) {
        //     $logo_image = rand(9999, 99999);
        //     $source = fopen($request->logo_image, 'r');
        //     $path = public_path("assets/users/" . $user->user_id. '/') . $logo_image . '.jpg';
        //     $destination = fopen($path, 'w');
        //     $result = stream_copy_to_stream($source, $destination);
        //     fclose($source);
        //     fclose($destination);
        //     if ($result) $user->logo_image = $logo_image . '.jpg';

        // }
        if ($request->hasFile('logo_image')) {
            $logo_image = addFile($request->file('logo_image'), public_path("assets/users/" . $user->user_id));
            $user->logo_image = $logo_image;

        }

        if ($request->hasFile('banner_image')) {
            $banner_image = addFile($request->file('banner_image'), public_path("assets/users/" . $user->user_id));
            $user->banner_image = $banner_image;

        }

        // if ($request->has('banner_image') && $request->filled('banner_image')) {
        //     $banner_image = rand(9999, 99999);
        //     $source = fopen($request->banner_image, 'r');
        //     $path = public_path("assets/users/" . $user->user_id. '/') . $banner_image . '.jpg';
        //     $destination = fopen($path, 'w');
        //     $result = stream_copy_to_stream($source, $destination);
        //     fclose($source);
        //     fclose($destination);
        //     if ($result) $user->banner_image = $banner_image . '.jpg';

        // }

        $user->email_verification_token = rand(1000, 9999);
        // $user->email_verification_token = 4444;
        if ($user->save()) {
            Mail::to($user->email)->send(new UserEmailVerificationLinkMail($user));
            if (count(Mail::failures()) > 0) return api_error('Email verification email couldn\'t send!', 400);

            return api_success1('Email Verification Number has been sent to your email!');
        }
        return api_error();
    }

    public function verify_email(Request $request)
    {
        $user = User::where('email', $request->email)->where('email_verification_token',  $request->token)->first();  
        if ($user && !$user->email_verified) {
            $stripe_key = config('credentials.stripe_key');
            try {
                $stripe = new \Stripe\StripeClient($stripe_key);
                $response = $stripe->accounts->create([
                    'type' => 'custom',
                    'country' => 'US',
                    'email' => $user->email,
                    'business_type' => 'individual',
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                        'transfers' => ['requested' => true],
                    ],
                    'tos_acceptance' => [
                        'date' => strtotime("now"),
                        'ip' => request()->ip(),
                        'user_agent' => $request->server('HTTP_USER_AGENT')
                    ],
                    'settings' => [
                        'payouts' => [
                            'schedule' => [
                                'interval' => 'daily'
                            ],
                            'statement_descriptor' => 'PRECOCIAL'
                        ]
                    ]
                ]);
            } catch (\Stripe\Exception\ApiErrorException $e) {
                return api_error_array([
                    "status" => $e->getHttpStatus(),
                    "type" => $e->getError()->type,
                    "code" => $e->getError()->code,
                    "param" => $e->getError()->param,
                    "message" => $e->getError()->message,
                ]);

            }

            try {
                $stripe = new \Stripe\StripeClient($stripe_key);
                $customer_create_response = $stripe->customers->create([
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
            } catch (\Stripe\Exception\ApiErrorException $e) {
                return api_error_array([
                    "status" => $e->getHttpStatus(),
                    "type" => $e->getError()->type,
                    "code" => $e->getError()->code,
                    "param" => $e->getError()->param,
                    "message" => $e->getError()->message,
                ]);

            }
            $user->removeFlag(User::FLAG_EMAIL_VERIFIED);
            $user->addFlag(User::FLAG_EMAIL_VERIFIED);
            $user->email_verification_token = 0;
            $user->stripe_account_id = $response->id;
            $user->customer_id = $customer_create_response->id;
            $user->stripe_response = json_encode($response);
            $user->customer_response = json_encode($customer_create_response);
            $user->save();

            request()->user = $user;
            $login_attempt = new LoginAttempt();
            $login_attempt->user_id = $user->user_id;
            $login_attempt->access_token = generate_token($user);
            $login_attempt->access_expiry = date("Y-m-d H:i:s", strtotime("1 year"));

            if (!$login_attempt->save()) return api_error();

            $data = array(
                'message' => 'Login Successfully',
                'detail' => $user,
                'token_detail' => array (
                    'access_token' => $login_attempt->access_token,
                    'token_type' => 'Bearer'
                )
            );
            return api_success('Email Verified Successfully', $data);
        }
        return api_error_array(['message' => 'Invalid Token!']);
    }

    public function complete_stripe_account (CompleteStripeAccountRequest $request)
    {
        $found = UserDetail::where('user_id', request()->user->user_id)->first();
        if ($found) {
            UserDetail::where('user_id', request()->user->user_id)->delete();

        }

        $stripe_key = config('credentials.stripe_key');
        $user = request()->user;
        $user_details = new UserDetail;
        $user_details->user_detail_id = (string) Str::uuid();
        $user_details->user_id = request()->user->user_id;
        $user_details->first_name = $request->first_name;
        $user_details->last_name = $request->last_name;
        $user_details->ssn_last_4 = $request->ssn_last_4;
        $user_details->phone = $request->phone;
        $user_details->dob = $request->dob;
        $user_details->city = $request->city;
        $user_details->state = $request->state;
        $user_details->country = $request->country;
        $user_details->address_line_one = $request->address_line_one;
        $user_details->postal_code = $request->postal_code;

        if ($user_details->save()) {
            $stripe = new \Stripe\StripeClient($stripe_key);
            // try {
                

                // $tmp_name = $request->file('front_image')->getPathName();
                // $file = fopen($tmp_name, 'r');
                // $front_obj = $stripe->files->create([
                //     'purpose' => 'identity_document',
                //     'file' => $file
                // ]);

                // $tmp_name = $request->file('back_image')->getPathName();
                // $file = fopen($tmp_name, 'r');
                // $back_obj = $stripe->files->create([
                //     'purpose' => 'identity_document',
                //     'file' => $file
                // ]);
            // } catch (\Stripe\Exception\ApiErrorException $e) {
                // return api_error_array([
                //     "status" => $e->getHttpStatus(),
                //     "type" => $e->getError()->type,
                //     "code" => $e->getError()->code,
                //     "param" => $e->getError()->param,
                //     "message" => $e->getError()->message,
                // ]);
            // }

            try {
                $response = $stripe->accounts->update($user->stripe_account_id,
                    [
                        'tos_acceptance' => [
                            'date' => strtotime("now"),
                            'ip' => request()->ip(),
                            'user_agent' => $request->server('HTTP_USER_AGENT')
                        ],
                        'business_profile' => [
                            'mcc' => '5734',
                            'name' => $user_details->first_name.' '.$user_details->last_name,
                            'url' => 'https://www.precocial.com',
                        ],
                        'individual' => [
                            'email' => $user->email,
                            'first_name' => $user_details->first_name,
                            'last_name' => $user_details->last_name,
                            'ssn_last_4' => $user_details->ssn_last_4,
                            'phone' => $user_details->phone,
                            'dob' => [
                                'day' => explode('-', $user_details->dob)[1],
                                'month' => explode('-', $user_details->dob)[0],
                                'year' => explode('-', $user_details->dob)[2]
                            ],
                            'registered_address' => [
                                'city' => $user_details->city,
                                'state' => $user_details->state,
                                'country' => $user_details->country,
                                'line1' => $user_details->address_line_one,
                                'postal_code' => $user_details->postal_code,
                            ],
                            'address' => [
                                'city' => $user_details->city,
                                'state' => $user_details->state,
                                'country' => $user_details->country,
                                'line1' => $user_details->address_line_one,
                                'postal_code' => $user_details->postal_code,
                            ],
                            // 'verification' => [
                            //     'document' => [
                            //         'front' => $front_obj->id,
                            //         'back' =>  $back_obj->id,
                            //     ]
                            // ]
                        ]
                    ]
                );

                // $front_image = addFile($request->file('front_image'), public_path("assets/users/" . $user->user_id));
                // $user_details->front_image = $front_image;

                // $back_image = addFile($request->file('back_image'), public_path("assets/users/" . $user->user_id));
                // $user_details->back_image = $back_image;
                $user_details->stripe_response = json_encode($response);
                $user_details->save();

                $user = User::where('user_id', request()->user->user_id)->first();
                $user->removeFlag(User::FLAG_DETAILS_PROVIDED);
                $user->removeFlag(User::FLAG_BANK_ACCOUNT_PROVIDED);
                $user->removeFlag(User::FLAG_PROFILE_COMPLETED_ON_STRIPE);
                $user->addFlag(User::FLAG_DETAILS_PROVIDED);
                $user->save();

                return api_success('User Profile updated on stripe. Now please add bank details!', $user_details);

            } catch (\Stripe\Exception\ApiErrorException $e) {
                return api_error_array([
                    "status" => $e->getHttpStatus(),
                    "type" => $e->getError()->type,
                    "code" => $e->getError()->code,
                    "param" => $e->getError()->param,
                    "message" => $e->getError()->message,
                ]);
            }

        } else {
            return api_error_array(['message' => 'There is some error!']);

        }
    }

    public function add_bank_account (AddBankAccountRequest $request)
    {
        $user = request()->user;
        $stripe_key = config('credentials.stripe_key');
        try{
            $stripe = new \Stripe\StripeClient($stripe_key);
            $external_bank = [
                'external_account' => [
                    'object'                => 'bank_account',
                    'account_holder_type'   => 'individual',
                    'account_holder_name'   => $request->account_holder_name,
                    'account_number'        => $request->account_number,
                    'routing_number'        => $request->routing_number,
                    'country'               => 'US',
                    'currency'              => 'USD',
                    'default_for_currency'  => true
                ]
            ];
            $external_account = $stripe->accounts->createExternalAccount($user->stripe_account_id, $external_bank);
            $found = UserDetail::where('user_id', request()->user->user_id)->first();
            $found->account_holder_name = $request->account_holder_name;
            $found->account_number = $request->account_number;
            $found->routing_number = $request->routing_number;
            $found->bank_account_stripe_response = json_encode($external_account);
            $found->save();

            $user = User::where('user_id', request()->user->user_id)->first();
            $user->removeFlag(User::FLAG_BANK_ACCOUNT_PROVIDED);
            $user->addFlag(User::FLAG_BANK_ACCOUNT_PROVIDED);
            $user->save();

            // if ($user->details_provided && $user->bank_account_provided) {
            //     $user->removeFlag(User::FLAG_PROFILE_COMPLETED_ON_STRIPE);
            //     $user->addFlag(User::FLAG_PROFILE_COMPLETED_ON_STRIPE);
            //     $user->save();
            // }

            return api_success('Bank Account Added!', $found);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return api_error_array([
                "status" => $e->getHttpStatus(),
                "type" => $e->getError()->type,
                "code" => $e->getError()->code,
                "param" => $e->getError()->param,
                "message" => $e->getError()->message,
            ]);
        }
    }

    public function reset_password_request(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password_verification_token = rand(1000, 9999);
            // $user->password_verification_token = 4444;
            if ($user->save()) {
                Mail::to($user->email)->send(new UserResetPasswordMail($user));
                if (count(Mail::failures()) > 0) return api_error('Reset Password email couldn\'t send!', 400);

                return api_success1('4 digit code has been sent to your email for resetting your password!');
            }
        }
        return api_error('No account found against given email!');
    }

    public function update_password(Request $request)
    {
        $user = User::where('email', $request->email)->where('password_verification_token', $request->token)->first();
        if ($user) {
            $user->password_verification_token = 0;
            $user->password = Hash::make($request->password);
            if ($user->save()) {
                return api_success1('Your password has been updated successfully!');
            }
        }
        return api_error('Invalid email or token!');
    }

    public function update(Request $request)
    {
        $user = User::where('user_id', request()->user->user_id)->first();
        if ($user) {
            $user->name = $request->input('name', $user->name);
            if ($request->has('password') && $request->filled('password')) $user->password = Hash::make($request->password);

            if ($request->hasFile('logo_image')) {
                $logo_image = addFile($request->file('logo_image'), public_path("assets/users/" . $user->user_id));
                if ($logo_image) {
                    if($user->logo_image) {
                        unlink(public_path("assets/users/" . $user->user_id. '/'.$user->logo_image));
                    }
                    $user->logo_image = $logo_image;
                }
            }

            if ($request->hasFile('banner_image')) {
                $banner_image = addFile($request->file('banner_image'), public_path("assets/users/" . $user->user_id));
                if ($banner_image) {
                    if($user->banner_image) {
                        unlink(public_path("assets/users/" . $user->user_id. '/'.$user->banner_image));
                    }
                    $user->banner_image = $banner_image;
                }

            }
            if ($user->save()) return api_success('Your profile has been updated successfully!', ['user' => $user]);

        }
        return api_error();
    }

    public function current_user()
    {
        return api_success('Current User Data', ['user' => request()->user]);
    }

    public function show(User $user)
    {
        $found = Friend::where(function ($q) use ($user) {
            $q->where('user_one', $user->user_id)->where('user_two', request()->user->user_id);

        })->orWhere(function ($q) use ($user) {
            $q->where('user_two', $user->user_id)->where('user_one', request()->user->user_id);

        })->first();
        return api_success('User Data', ['user' => $user, 'friend' => $found]);
    }

    public function search_users (Request $request)
    {
        $per_page = 25;
        if ($request->has('per_page') && $request->filled('per_page')) $per_page = $request->per_page;

        $user = User::query();

        if ($request->has('search') && $request->filled('search')) {
            $user->where(function ($q) use ($request) {
                $q->orWhere('email', $request->search)->orWhere('name', 'like', '%'.$request->search.'%')->orWhere('username', 'like', '%'.$request->search.'%');
            });

        }
        $user->where(function ($q) {
            $q->whereRaw('`flags` & ?=?', [User::FLAG_EMAIL_VERIFIED, User::FLAG_EMAIL_VERIFIED]);    
        });
        return api_success('Users Found', $user->paginate($per_page));
    }

    public function admin_login(AdminLoginRequest $request)
    {
        $admin = Admin::where('email', $request->email)->first();
        if ($admin && HASH::check($request->password, $admin->password)) {
                request()->admin = $admin;
                $login_attempt = new LoginAttempt;
                $login_attempt->user_id = $admin->admin_id;
                $login_attempt->access_token = generate_token($admin);
                $login_attempt->access_expiry = date("Y-m-d H:i:s", strtotime("1 year"));

                if (!$login_attempt->save()) 
                    return api_error();
                    $data = array(
                        'message' => 'Login Successfully',
                        'detail' => $admin,
                        'token_detail' => array (
                            'access_token' => $login_attempt->access_token,
                            'token_type' => 'Bearer'
                        )
                    );                
                return $data;        
        }
        return api_error('Invalid Email or Password', 400);
    }

    public function me(Request $request)
    {
        if (request()->admin) return api_success1(request()->admin);
        return api_error();
    }


    public function index (Request $request)
    {
        $user = User::query();
        if ($request->has('search') && $request->filled('search')) {
            $user->where('email', $request->search)->orWhere('name', 'like', '%'.$request->search.'%')->orWhere('username', 'like', '%'.$request->search.'%');
        }
        $user->orderBy('created_at', 'DESC');

        return  $user->paginate(1);   
    }

    public function logout(Request $request)
    {
        if ($request->login_attempt) {
            $request->login_attempt->access_expiry = date("Y-m-d H:i:s");
            $request->login_attempt->save();
        }
        return redirect(route('HomePage'))->with(['req_success' => "Logout Successfully!"]);
    }

    public function check_user_profile_status ()
    {
        $stripe_key = config('credentials.stripe_key');
        $stripe = new \Stripe\StripeClient($stripe_key);
        $users = User::whereNotNull('stripe_account_id')->whereRaw('`flags` & ?!=?', [User::FLAG_PROFILE_COMPLETED_ON_STRIPE, User::FLAG_PROFILE_COMPLETED_ON_STRIPE])->pluck('user_id')->toArray();

        if (sizeof($users) > 0) {
            foreach ($users as $key => $value) {
                $bank_flag = false;
                $info_flag = false;
                $user = User::where('user_id', $value)->first();
                if (!$user->profile_completed_on_stripe) {
                    $response = $stripe->accounts->retrieve($user->stripe_account_id, []);

                    if (isset($response->capabilities) && isset($response->capabilities->card_payments) && isset($response->capabilities->transfers) && isset($response->external_accounts) && isset($response->external_accounts->data) ) {
                        if ($response->capabilities->card_payments && $response->capabilities->transfers && sizeof($response->external_accounts->data) > 0) {
                            $bank_flag = true;
                            $user->removeFlag(User::FLAG_BANK_ACCOUNT_PROVIDED);
                            $user->addFlag(User::FLAG_BANK_ACCOUNT_PROVIDED);

                        }
                    }

                    if (isset($response->individual) && isset($response->individual->requirements) && isset($response->individual->requirements->past_due) && isset($response->individual->requirements->eventually_due) && sizeof($response->individual->requirements->past_due) < 1 && sizeof($response->individual->requirements->eventually_due) < 1 ) {
                        $info_flag = true;
                        $user->removeFlag(User::FLAG_DETAILS_PROVIDED);
                        $user->addFlag(User::FLAG_DETAILS_PROVIDED);

                    }

                    $user->stripe_response = json_encode($response);
                    if ($info_flag && $bank_flag) {
                        $user->removeFlag(User::FLAG_PROFILE_COMPLETED_ON_STRIPE);
                        $user->addFlag(User::FLAG_PROFILE_COMPLETED_ON_STRIPE);
                        $user->save();

                    } else {
                        $user->save();

                    }
                }
            }
        }
        return true;
    }

    public function get_user_details (Request $request)
    {
        $user_details = UserDetail::where('user_id', $request->user->user_id)->first();
        return api_success("User Details Data", ['user_details' => $user_details]);
    }

    public function update_device_token (Request $request)
    {
        $result = User::where('user_id', request()->user->user_id)->update(['device_token' => $request->token]);
        if ($result) {
            return api_success1('Device Token Updated');

        }
        return api_error();
    }
}