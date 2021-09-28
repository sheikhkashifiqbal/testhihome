<?php


namespace App\Modules\Orders\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\RateReview\Services\OrderRatingService;
use App\Modules\Sellers\Services\StoreService;
use App\Modules\Sellers\Transformers\SellerTransformer;

class OrderTransformer extends JsonResource
{

    public function __construct($resource) {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request) {
        $relations_loaded = [
            'order_items' => OrderDetailsTransformer::collection($this->whenLoaded('details'))
        ];
        $dt               = Carbon::parse($this->created_at);
        $estimated_time   = Carbon::parse($this->estimated_time)->format('h:i a');
        $pickup_time       = ($this->pickup_time) ? Carbon::parse($this->pickup_time)->format('h A') : '';
        $customer_rating   = OrderRatingService::getOrderRatingAndReview($this->id);

        $can_rate_order    = (empty((array)$customer_rating) && $this->can_rate_order) ? true : false;
        $store = new SellerTransformer(StoreService::getStoreDetails($this->store_id));

        return array_merge(
            [
                'order_id'       => $this->id,
                'order_number'       => $this->number,
                'transaction_id' => $this->transaction,
                'order_type'     => $this->order_type,
                'order_date'     => $dt->format('M d'),
                'order_year'     => $dt->format('Y'),
                'order_time'     => $dt->format('h:i a'),
                'estimated_time' => $estimated_time,
                'status'         => $this->orderStatus,
                'delivery_date'  => $this->delivery_date,
                'delivery_slot'  => $this->delivery_slot,
                'pickup_time'    => $pickup_time,
                'can_rate_order' => $can_rate_order,
                'customer_rating' => $customer_rating,
                'special_request' => $this->special_request,
                'transaction_type' => $this->transaction_type,
                'transaction'     => $this->transaction,
                'shipping'        => $this->shipping,
                'ordered_items'  => json_decode($this->items),
                'price'          => [
                    'subtotal' => $this->subtotal,
                    'shipping' => $this->shipping,
                    'discount' => $this->discount,
                    'tax'      => $this->tax,
                    'total'    => 'AED ' . $this->total,
                ],
                'customer'       => [
                    'name'     => $this->first_name . ' ' . $this->last_name,
                    'address'  => $this->address1,
                    'landmark' => $this->address2,
                    'mobile'   => $this->phone,
                    'email'    => $this->email,
                ],
                'seller' => $store
            ],
            $relations_loaded
        );
    }
}
