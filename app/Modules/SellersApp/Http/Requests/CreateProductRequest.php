<?php

namespace App\Modules\SellersApp\Http\Requests;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class CreateProductRequest extends FormRequest
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
        $rules = [
          'price'=> 'required|numeric',
          'serve_count'=> 'required|numeric',
          'prepration_time' => 'required',
          'details.*.name' => 'required|max:200',
          //'details.*.description' => 'required|max:300',
          'details.*.ingredients' => 'required|max:300',
          // 'details.*.allergies' => 'required|max:300',
          // 'details.*.gluten_free' => 'required',
          // 'details.*.weight' => 'required|max:10',
          'category_id' => 'required|exists:shop_category,id',
          'is_feature'  => 'required|in:0,1',
          'alias' => 'max:120',
        ];

        if($this->request->get('discount_percentage')){
          $rules['discount_start_date'] = 'required';
          $rules['discount_expiry_date'] = 'required|date|after:discount_start_date';
        }

        if(Route::currentRouteName() == 'seller.update.product'){
          $rules['product_id'] = 'required|exists:shop_product,id';
        }

        return $rules;
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
