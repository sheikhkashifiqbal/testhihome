<?php

namespace App\Traits;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Modules\Common\Rules\MatchOldPassword;

/**
 * Trait ApiResponseTrait
 *
 * this trait used for return any response with specific structure for handling error
 * response and data pagination and other responses
 *
 * @package App\Traits
 */
trait ApiResponseTrait
{
    /**
     * @var string $lang it is carry language value for use it in any purpose in all classes uses this trait
     */
    public $lang;
    /**
     * @var array this variable used for build response in below functions
     */
    public $return = [];

    /**
     * initResponse
     *
     * @useage this function used to initialize response variable "return[]" with basic data that must sent in all responses
     */
    private function initResponse()
    {
        $this->lang = request()->header('lang') ? request()->header('lang') : config('app.locale');
        config('app.locale') === $this->lang || config(['app.locale' => $this->lang]);
        $this->return['lang']          = $this->lang;
        $this->return['status']        = '1';
        $this->return['code']          = Response::HTTP_OK;
        $this->return['error_msg']     = '';
        $this->return['success_msg']   = '';
        $this->return['errors']        = new \stdClass();
        $this->return['response_data'] = [];
    }

    protected function successEmptyResponse($message = '', $code = Response::HTTP_OK)
    {
        $this->initResponse();
        $this->return['success_msg'] = $message;
        return $this->returnResponse($code);
    }

    /**
     * @param $data this variable cary all data to be send in response
     * @param int $code response code ex.. code 200
     *
     * @useage in this function you can send data with additional data ex.. total_items , current_page , next_page
     * @note   make sure data must be paginated
     *
     * @return response array
     */
    protected function successResponseWithDataPaginated($data, $message = '', $code = Response::HTTP_OK)
    {
        $this->initResponse();
        $this->return['code']                       = $code;
        $this->return['success_msg']                = $message;
        $this->return['response_data']['data']      = $data;
        $this->return['response_data']['paginator'] = [
            'total_items'  => $data->count(),
            'current_page' => $data->currentPage(),
            'per_page'     => $data->perPage(),
            'hasMorePages' => $data->hasMorePages(),
            'next_page'    => ($data->currentPage() != $data->lastPage() && $data->currentPage() < $data->total()) ? $data->currentPage() + 1 : null,
        ];
        return $this->returnResponse($code);
    }

    /**
     * @param $data this variable cary all data to be send in response
     * @param int $code $code response code ex.. code 200
     *
     * @useage in this function you can send data without any additional information's
     *
     * @return response array
     */
    protected function successResponseWithData($data, $message = '', $code = Response::HTTP_OK)
    {
        $this->initResponse();
        $this->return['status']        = '1';
        $this->return['code']          = $code;
        $this->return['success_msg']   = $message;
        $this->return['error_msg']     = '';
        $this->return['errors']        = new \stdClass();
        $this->return['response_data'] = $data;
        return $this->returnResponse($code);
    }

    /**
     * @param string $error single error message for send in response
     * @param array $errors array of errors to send in response
     * @param $code response code ex.. code 400
     * @param $line
     * @param $trace
     * @param $extra_fields
     *
     * @useage in this function you can send response errors with array of errors or single message
     *
     * @return response array
     */
    protected function errorResponse($error = '', array $errors = [], $code = Response::HTTP_BAD_REQUEST, $line = "", $trace = null, $validation = false, $extra_fields = [])
    {
        $this->initResponse();
        $this->return           = array_merge($this->return, $extra_fields);
        $this->return['status'] = '0';
        $this->return['code']   = $code;

        reset($errors);
        $first_key = key($errors);

        $this->return['error_msg'] = count((array)$errors) ? $errors[$first_key] : $error;

        if ($validation) {
            if (is_array($errors)) {
                foreach ($errors as $key => $error) {
                    //array_undot_to_form()
                    $this->return['validation_errors'][$key] = $error;
                }
            } else {
                $this->return['errors'] = [$error];
            }
        } else {
            if (is_array($errors)) {
                $this->return['errors'] = $errors;
            } else {
                $this->return['errors'] = [$error];
            }
        }


        if (app()->environment() !== 'production') {
            if ($line != "") {
                $this->return['line'] = $line;
            }
            if ($trace != null) {
                $this->return['trace'] = $trace;
            }
        }

        empty($this->return['errors']) ? $this->return['errors'] = new \stdClass() : '';
        return $this->returnResponse($code);
    }

    /**
     *
     * validateApiRequest
     *
     * @usage this function used for validation you can use it an all validation ,
     *        if there are any validation errors by default this function return response directly to api without return to called function
     *
     * @param array $required all required variables to check if request has it or no
     * @param array $additional_validations another validation types
     * @param array $skip by default this function check if exist validation for any tables this parameter to skip this validation for any variable
     * @param array $messages this variable can pass messages for all validation types
     *
     * @return bool
     */
    protected function validateApiRequest($required = [], $additional_validations = [], $skip = [], $messages = [], $validator_errors = null)
    {
        $this->initResponse();
        $errors = [];
        //        if(!in_array('device_type',$skip)){
        //            $required[] = 'device_type';
        //        }
        $rules = [];
        //        $additional_validations['device_type'] = 'in:job_management';
        $additional_validations['per_page'] = 'integer|min:1';
        $additional_validations['page']     = 'integer|min:1';

        foreach ($required as $key => $item) {
            $additional   = isset($additional_validations[$item]) ? $additional_validations[$item] : null;
            $rules[$item] = 'required';
            if (strpos($item, '_id') && !in_array($item, $skip) && $item != "_id") {
                $table = Str::plural(substr($item, 0, strlen($item) - 3));
                if ($table != "" && \Schema::hasTable($table)) {
                    $rules[$item] .= '|exists:' . $table . ',id';
                }
            }
            $rules[$item] .= $additional ? '|' . $additional : '';
            $rules[$item] .= ($item == 'mobile' ? '|' . 'digits:10' : '');
        }
        foreach ($additional_validations as $k => $v) {
            if (!in_array($k, $required)) {
                if ($v == 'MatchOldPassword') {
                    $rules[$k] =  new MatchOldPassword;
                } else {
                    $rules[$k] = $v;
                }
            }
        }

        $validator = Validator::make(app('request')->only(array_merge($required, array_keys($additional_validations))), $rules, $messages);

        if ($validator->fails()) {
            if ($validator_errors) {
                return $validator->errors()->messages();
            }
            foreach ($validator->errors()->messages() as $key => $messages) {
                $errors[$key] = implode(',', $messages);
            }
            $this->errorResponse('', $errors);
            $this->responseWithJsonErrorsArray($this->return, 400)->send();
            exit();
        }
        return true;
    }

    /**
     * @param int $code response code ex.. code 400 , 200 , 201 ...
     *
     * @usage this function used in above function to return response data and we can add headers ex.. Content-Type:application/json
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function returnResponse($code = Response::HTTP_OK)
    {
        $code                 = $code == 0 ? Response::HTTP_BAD_REQUEST : $code;
        $this->return['code'] = $code;
        return response()->json($this->return, $code)
            ->header('Content-Type', 'application/json');
    }

    /**
     * @param $text
     * @param $code
     *
     * @return Response|\Laravel\Lumen\Http\ResponseFactory
     */
    function responseWithJsonErrorsArray($text, $code)
    {
        return response($text, $code);
    }
}
