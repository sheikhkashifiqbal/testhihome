<?php


namespace App\Modules\Cart\Transformers;

use App\Modules\Sellers\Models\Store;
use App\Modules\Sellers\Transformers\SellerTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class CartTransformer extends JsonResource
{
    public $additional_data;

    public function __construct($resource, $additional_data = [])
    {
        parent::__construct($resource);
        $this->additional_data = $additional_data;
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
        $relations_loaded = [];
        return array_merge(
            [
                'id'      => $this->id,
                'user_id' => $this->user_id,
                'key'     => $this->key,

            ],
            $this->additional_data,
            [
                'stores_items' => $this->cartItems()
            ],
            $relations_loaded
        );
    }

    private function cartItems()
    {
        $items = [];
        if (isset($this->items)) {
            $cart_items = $this->items->pluck('store_id')->toArray();
            $stores     = Store::whereIn('id', $cart_items)
                ->with(
                    [
                        'description',
                        'cartItems' => function ($q) {
                            $q->where('cart_id', $this->id);
                        },
                        'cartItems.product',
                        'cartItems.product.productDescription',
                    ]
                )
                ->get();
            $items      = SellerTransformer::collection($stores);
        }
        return $items;
    }
}
