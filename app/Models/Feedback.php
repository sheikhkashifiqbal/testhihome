<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    public $table = 'feedbacks';

    protected $fillable = [
        'user_id','body', 'image', 'customer_name', 'customer_email', 'type'
    ];

    public function scopeWithOrderBy($query, $sort_order){
        $field = explode('__', $sort_order)[0];
        $sort_field = explode('__', $sort_order)[1];
        return $query->orderBy($field, $sort_field);
    }

    public function scopeWithSearchKeyword($query, $keyword){
        return $query->whereRaw('(feedbacks.customer_name like "%' . $keyword . '%" OR feedbacks.customer_email like "%' . $keyword . '%" )');
    }

}
