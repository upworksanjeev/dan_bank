<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SessionAuth extends Controller {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		$sessionNotRequired = [
			'Login',
			'LoginAdmin',
			'Register',
			'VerifyEmail',
			'ResetPasswordRequest',
			'UpdatePassword',
			'CheckUserProfileStatus',
			'PasswordReset',
			'AdminUpdatePassword',
			'AdminPasswordReset'
		];

		if ($this->is_valid_token($request)) {
			$admin = Admin::where('admin_id', $request->login_attempt->user_id)->first();
			if ($admin) {
				$request->admin = $admin;
				request()->admin = $admin;
				return $next($request);
			} else {
				$user = User::where('user_id', $request->login_attempt->user_id)->first();
				if ($user) {
					$request->user = $user;
					return $next($request);
				}
			}

		} else if (in_array($request->route()->getName(), $sessionNotRequired)) {
			return $next($request);

		}
		return api_error('You are unauthorized!', 401);
	}

	public function is_valid_token(&$request) {
		$token = getToken($request);
		if (!$token) {
			return false;
		}
		$request->login_attempt = LoginAttempt::where("access_token", $token)->get()->first();
		$is_expired = "is_access_expired";
		return $request->login_attempt && !($request->login_attempt->toArray())[$is_expired];
	}
}
