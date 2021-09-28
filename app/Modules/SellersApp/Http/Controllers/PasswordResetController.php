<?php

namespace App\Modules\SellersApp\Http\Controllers;

use App\Core\MyBaseApiController;
use App\Modules\Auth\Models\ShopUser as User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\PasswordReset;
use Validator;

class PasswordResetController extends MyBaseApiController
{
    public $templatePath = 'templates.default';
    /**
     * Create token password reset
     *
     * @param  [string] email
     * @return [string] message
     */
    public function create(Request $request)
    {
        $request->validate([
            'email' => '',
        ]);
        $this->validateApiRequest(
            ['email'],
            ['email' => 'required|string|email']
        );
        $user = User::where('email', $request->email)->first();

        if (!$user)
            return response()->json([
                'status' => 0,
                'message' => trans('passwords.user')
            ], 404);
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => str_random(60)
            ]
        );

        if ($user && $passwordReset)
            $user->notify(
                new PasswordResetRequest($passwordReset->token)
            );
        return response()->json([
            'status' => 1,
            'message' => trans('passwords.sent')
        ]);
    }
    /**
     * Find token password reset
     *
     * @param  [string] $token
     * @return [string] message
     * @return [json] passwordReset object
     */
    public function find(Request $request, $token = null)
    {

        $passwordReset = PasswordReset::where('token', $token)
            ->first();
        $valide_msg = '';

        if (!$passwordReset) {
            return redirect()->route('home')->with(['error' => trans('passwords.token')]);
            //$valide_msg = 'This password reset token is invalid.';
        }

        if ($valide_msg == '' && Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return redirect()->route('home')->with(['error' => trans('passwords.token')]);
        }
        $valide = 0;
        if ($valide_msg != '') {
            $token = '';
        } else {
            $valide = 0;
        }

        return view(
            $this->templatePath . '.auth.reset-api',
            [
                'title' => trans('front.reset_password'),
                'valide_msg' => $valide_msg,
                'valide' => $valide,
                'token' => $token,
                'templatePath' => $this->templatePath,

            ]
        );
    }
    /**
     * Reset password
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @param  [string] token
     * @return [string] message
     * @return [json] user object
     */
}
