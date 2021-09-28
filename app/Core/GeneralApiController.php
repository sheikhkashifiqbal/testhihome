<?php

namespace App\Core;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use App\Models\ShopSubscribe;
use Illuminate\Http\Request;

/**
 * Class MyBaseApiController
 * make sure extend any new controller from this class
 *
 * @package App\Http\Controllers
 */
class GeneralApiController extends Controller {

    public $templatePath;
    public $templateFile;

    public function __construct() {
        $languages = sc_language_all();
        $currencies = sc_currency_all();
        $blocksContent = sc_block_content();
        $layoutsUrl = sc_link();
        $this->templatePath = 'templates.' . sc_store('template');
        $this->templateFile = 'templates/' . sc_store('template');
        view()->share('languages', $languages);
        view()->share('currencies', $currencies);
        view()->share('blocksContent', $blocksContent);
        view()->share('layoutsUrl', $layoutsUrl);
        view()->share('templatePath', $this->templatePath);
        view()->share('templateFile', $this->templateFile);
    }

}
