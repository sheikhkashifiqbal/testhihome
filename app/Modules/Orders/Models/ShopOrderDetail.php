<?php
#app/Models/ShopOrderDetail.php
namespace App\Modules\Orders\Models;

use App\Core\MyBaseApiModel;

class ShopOrderDetail extends MyBaseApiModel
{
    protected $table = 'shop_order_detail';
    protected $guarded = [];

    public function order() {
        return $this->belongsTo(ShopOrder::class, 'order_id', 'id');
    }

    public function product() {
        return $this->belongsTo(ShopProduct::class, 'product_id', 'id');
    }

    public function updateDetail($id, $data)
    {
        return $this->where('id', $id)->update($data);
    }
    public function addNewDetail($data)
    {
        if ($data) {
            $this->insert($data);
            //Update stock, sold
            foreach ($data as $key => $item) {
                //Update stock, sold
                ShopProduct::updateStock($item['product_id'], $item['qty']);
            }
        }
    }
}
