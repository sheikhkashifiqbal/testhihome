<?php

namespace App\Modules\SellersApp\Http\Controllers;

use App\Core\GeneralApiController;
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

class PasswordFindController extends GeneralApiController
{

    public function __construct()
    {
        parent::__construct();
    }

    //public $templatePath='templates.default';
    /**
     * Create token password reset
     *
     * @param  [string] email
     * @return [string] message
     */
    public function find(Request $request, $token = null)
    {
        // dd($request->role);
        $passwordReset = PasswordReset::where('token', $token)
            ->first();
        $valide_msg = '';

        if (!$passwordReset) {
            return redirect()->route('home')->with(['error' => trans('passwords.token')]);
            //$valide_msg = 'This password reset token is invalid.';
        }

        if ($valide_msg == '' && Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return redirect()->route('home')->with(['error' => trans('passwrods.token')]);
        }
        $valide = 0;
        if ($valide_msg != '') {
            $token = '';
        } else {
            $valide = 0;
        }
        // dd($passwordReset);
        return view(
            $this->templatePath . '.auth.reset-api',
            [
                'title' => trans('front.reset_password'),
                'valide_msg' => $valide_msg,
                'valide' => $valide,
                'token' => $token,
                'email' => $passwordReset->email,
                'role' => $request->role
                //      'templatePath'=>$this->templatePath,
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
     */
    public function reset(Request $request)
    {
        /* $request->validate([
          'email' => 'required|string|email',
          'password' => 'required|string|confirmed',
          'token' => 'required|string'
          ]); */
        $data = request()->all();
        // dd($data);
        $dataOrigin = request()->all();

        $validator = Validator::make($dataOrigin, [
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();

        if (!$passwordReset) {
            $error['valide_msg'] = trans('passwords.token');
            return redirect()->back()
                ->withErrors($error)
                ->withInput();
        }
        //dd($request->role);


        $user = User::where('email', $passwordReset->email);
        if ($request->role == 'NULL' || $request->role == null) {
            $user = $user->wherenull('role');
        } else {
            $user = $user->where('role', $request->role);
        }

        $user = $user->first();


        if (!$user) {

            $error['valide_msg'] = trans('passwords.user');
            return redirect()->back()
                ->withErrors($error)
                ->withInput();
        }

        $user->password = bcrypt($request->password);
        $user->real_password = $request->password;
        $user->save();
        $passwordReset->delete();
        $user->notify(new PasswordResetSuccess($passwordReset));
        return redirect()->route('home')->with(['success' => trans('passwords.success'),]);
        //return redirect()->route('success')->with('success', 'Your password has been reset successfully.');
        return view(
            $this->templatePath . '.auth.success',
            [
                'title' => trans('front.reset_password'),
                'success', trans('passwords.success')
            ]
        );
    }

    public function success()
    {


        return view(
            $this->templatePath . '.auth.success',
            [
                'title' => trans('front.reset_password'),
            ]
        );
    }
}
