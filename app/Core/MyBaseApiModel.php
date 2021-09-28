<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MyBaseModel
 * make sure extend any new model from this class
 *
 * @package App\Models
 */
class MyBaseApiModel extends Model
{

    /**
     * @var string $lang it is carry language value for use it in any purpose in all models
     */
    public $lang = 'en';

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->lang = config('app.locale');
    }

}
