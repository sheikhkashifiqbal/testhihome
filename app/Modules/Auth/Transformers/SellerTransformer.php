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

class SellerTransformer extends JsonResource
{

    protected $api_token = '';

    public function __construct($resource, $token = null)
    {
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
    public function toArray($request)
    {
        $token_data = [];
        if ($this->api_token) {
            $token_data['token'] = $this->api_token;
        }

        return array_merge(
            [
                "id"         => $this->id,
                "name"       => $this->name,
                "store_id"       => $this->store_id,
                "role"       => $this->role,
                "device_id"  => $this->device_id,
                "first_name" => $this->first_name,
                "last_name"  => $this->last_name,
                "email"      => $this->email,
                "status"     => $this->status,
                "has_featured_product"    => ProductService::sellerHasFeaturedProduct($this->store_id),
                "created_at" => $this->created_at->format('d/m/Y h:m A'),
                "updated_at" => $this->updated_at->format('d/m/Y h:m A'),
                "seller_details" => [
                    'store_id' => $this->seller->id,
                    'accept_orders' => $this->seller->accept_orders,
                    'status' => $this->seller->status,
                    'title' => $this->seller->description->title,
                    'legal_business_email' => $this->seller->legal_business_email,
                    'legal_business_phone' => $this->seller->legal_business_phone,
                    'city' => $this->seller->city,
                    'address' => $this->seller->address,
                    'logo' => ($this->seller->logo) ? sc_image_cdn_get_path($this->seller->logo) : '',
                    'rank' => $this->seller->rank,
                    'reviewer_count' => $this->seller->reviewer_count,
                    'emirates_id' => $this->seller->emirates_id,
                    'license_id' => $this->seller->license_id,
                    'license_start_date' => $this->seller->license_start_date,
                    'license_end_date' => $this->seller->license_end_date,
                    'pincode' => $this->seller->pincode,
                    'contact_us_first_name' => $this->seller->contact_us_first_name,
                    'contact_us_last_name' => $this->seller->contact_us_last_name,
                    'contact_us_email' => $this->seller->contact_us_email,
                    'license_photo' => ($this->seller->license_photo) ? sc_image_cdn_get_path($this->seller->license_photo) : '',
                    'maintain_content' => $this->seller->description->maintain_content,
                    'lat' => $this->seller->lat,
                    'long' => $this->seller->long,
                    'seller_eid' => $this->seller->seller_eid,
                ],
            ],
            $token_data
        );
    }
}
