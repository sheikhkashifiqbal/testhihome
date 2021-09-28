<?php

namespace App\Modules\RateReview\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Modules\Orders\Services\OrderService;

class OrderCreatedByCustomer implements Rule
{
     protected $_order_id;
     protected $_user_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($order_id, $user_id)
    {
        $this->_order_id = $order_id;
        $this->_user_id = $user_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return OrderService::checkOrderCreatedByCustomer($this->_order_id, $this->_user_id);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.validate_order_customer');
    }
}
