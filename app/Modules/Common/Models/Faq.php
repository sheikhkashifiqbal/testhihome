<?php
#app/Models/Currency.php
namespace App\Modules\Common\Models;

/* use Cart; */

use App\Core\MyBaseApiModel;

class Faq extends MyBaseApiModel
{
    public $table = 'faq';
    public $timestamps = false;
    protected $guarded = [];

}
