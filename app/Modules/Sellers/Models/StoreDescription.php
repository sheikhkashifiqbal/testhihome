<?php
#app/Models/StoreDescription.php
namespace App\Modules\Sellers\Models;
use App\Core\MyBaseApiModel;

class StoreDescription extends MyBaseApiModel
{
    protected $primaryKey = ['lang', 'config_id'];
    public $incrementing = false;
    protected $guarded = [];
    public $timestamps = false;
    public $table = 'admin_store_description';
}
