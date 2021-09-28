<?php

namespace App\Modules\RateReview\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;

class RateSellerRequest extends FormRequest
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
        'store_id' => 'required|exists:admin_store,id',
        'rate'     => 'required|numeric|max:5',
        'review'   => 'required',
        'user_id'   => [ Rule::unique('store_rates')->where(function ($query) {
                         return $query->where('store_id', $this->request->get('store_id'))->where('user_id', $this->user()->id);
                       })],
      ];
    }

    public function all($keys = null)
    {
        $data = parent::all();
        $data['user_id'] = $this->user()->id;
        return $data;
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
