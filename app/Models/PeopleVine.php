<?php
#app/Models/PeopleVine.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeopleVine extends Model
{
    
    public $incrementing = false;
    protected $guarded = [];
    public $timestamps = false;
    public $table = 'people_vine';
    public function user() {
        return $this->belongsTo(ShopUser::class,'user_id','id');
    }
    
}
