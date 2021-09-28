<?php

namespace App\Imports;

use App\Models\AdminStore as Store;
use App\Models\ShopCategory as Category;
use App\Models\ShopCategoryDescription as Description;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoriesImport implements ToCollection, WithHeadingRow
 {
    private $rows = 0;
    public $brand_id;

    public function __construct( $brand_id )
 {
        $this->brand_id = $brand_id;
    }

    public function collection( Collection $rows )
 {
        //$row is hodling every row of excell later to do validation if exist

        //import category
        $stores = Store::where( 'brand_id', $this->brand_id )
        ->where( 'site_status', 1 )
        ->pluck( 'id' )->toArray();
     foreach ($rows as $row) {
         if (isset($row['category_id']) && $row['category_id']):
             ++$this->rows;

             $data = Category::create(
                 [
                     'id'       => $row['category_id'],
                     'brand_id' => $this->brand_id,
                     'alias'    => str_slug($row['category_name'], '-'),
                     'status'   => 1,
                     'is_extra' => $row['is_extra'] ?? 0,
                'sort' => $row['category_id'],
            ] );

            Description::create( [
                'category_id' => $data->id,
                'lang' => 'en',
                'name' => $row['category_name']
            ] );
            //admin_store_shop_category
            $data->stores()->sync( $stores );
            endif;
        }

    }

    public function getRowCount(): int
 {
        return $this->rows;
    }
}
