<?php
/**
 * Created by PhpStorm.
 * User: mohamed
 * Date: 12/5/18
 * Time: 2:14 PM
 */

namespace App\Modules\Auth\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use  App\Modules\Products\Services\ProductService;

class UserTransformer extends JsonResource
{

    protected $api_token = '';

    public function __construct($resource, $token = null) {
        parent::__construct($resource);
        $this->api_token = $token;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request) {
        $token_data = [];
        if ($this->api_token) {
            $token_data['token'] = $this->api_token;
        }

        $profile_photo = '';
        if ($this->profile_picture)
        {
          $profile_photo = asset($this->profile_picture);
        }

        return array_merge(
            [
                "id"         => $this->id,
                "name"       => $this->name,
                "role"       => $this->role,
                "device_id"  => $this->device_id,
                "branch_id"  => $this->branch_id,
                "first_name" => $this->first_name,
                "last_name"  => $this->last_name,
                "email"      => $this->email,
                "sex"        => $this->sex,
                "birthday"   => $this->birthday,
                "postcode"   => $this->postcode,
                "address1"   => $this->address1,
                "address2"   => $this->address2,
                "company"    => $this->company,
                "country"    => $this->country,
                "phone"      => $this->phone,
                "status"     => $this->status,
                "is_guest"   => $this->is_guest,
                "group"      => $this->group,
                "profile_picture" =>  $profile_photo,
                "created_at" => $this->created_at->format('d/m/Y h:m A'),
                "updated_at" => $this->updated_at->format('d/m/Y h:m A'),
            ],
            $token_data,
            $this->isFeatureProduct($this->role, $this->store_id)
        );
    }

    public function isFeatureProduct($role, $store_id)
    {
      if($role == 'seller'){
        return [
          'has_featured_product' =>  ProductService::sellerHasFeaturedProduct($store_id)
        ];
      }

      return [];
    }
}
