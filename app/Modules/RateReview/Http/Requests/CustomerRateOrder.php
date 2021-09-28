<?php

namespace App\Modules\RateReview\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;
use App\Modules\RateReview\Rules\OrderCreatedByCustomer;

class CustomerRateOrder extends FormRequest
{
    use ApiResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
          'order_id' => [
            'required',
            'exists:shop_order,id',
            new OrderCreatedByCustomer($this->request->get('order_id'), $this->user()->id)
          ],
          'review' => 'required',
          'products.*.product_id' => 'required|exists:shop_product,id',
          'products.*.rate'     => 'required|numeric|max:5'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
      if ($validator->fails()) {
          foreach ($validator->errors()->messages() as $key => $messages) {
              $errors[$key] = implode(',', $messages);
          }
          $this->errorResponse('', $errors);
          throw new HttpResponseException($this->responseWithJsonErrorsArray($this->return, 400));
      }
    }

}
