<?php

namespace App\Core;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;

/**
 * Class MyBaseApiController
 * make sure extend any new controller from this class
 *
 * @package App\Http\Controllers
 */
class MyBaseApiController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {

        $this->lang = request()->header('lang') ? request()->header('lang') : config('app.locale');
        app()->setlocale($this->lang);
        config('app.locale') === $this->lang || config(['app.locale' => $this->lang]);
    }
}
