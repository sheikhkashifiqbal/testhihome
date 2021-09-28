<?php
#app/Models/Currency.php
namespace App\Modules\Common\Models;

/* use Cart; */

use App\Core\MyBaseApiModel;

class Feedback extends MyBaseApiModel
{
    public $table = 'feedbacks';

    protected $fillable = [
        'user_id','body', 'image', 'customer_name', 'customer_email', 'type', 'customer_phone'
    ];


}
