<?php

namespace App\Imports;

use App\Models\AdminStore as Location;
use App\Models\AdminStoreDescription as Description;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class LocationsImport implements ToCollection, WithHeadingRow
{
    private $rows = 0;
    public $brand_id;
    public function __construct($brand_id)
    {
        $this->brand_id=$brand_id;
    }

    public function collection(Collection $rows) {//$row is hodling every row of excell later to do validation if exist
        //dd($rows);
        //import Location
        foreach ($rows as $row) {
            if (isset($row['location_id']) && $row['location_id']):
                ++$this->rows;
                $data = Location::create(
                    [
                        'id'          => $row['location_id'],
                        'brand_id'    => $this->brand_id,
                        'time_active' => $row['time_active'],
                        'phone'       => $row['phone'],
                        'email'       => $row['email'],
                        'lat'         => $row['lat'],
                        'long'        => $row['long'],
                'address' => $row['address'],
                /* 'city' => $row['city'], */
                'site_status' => 1,
                'sort' => $row['location_id'],
            ]);


                Description::create(
                    [
                        'config_id' => $data->id,
                        'lang'      => 'en',
                        'title'     => $row['title'],
                        'address'   => $row['address']
                    ]
                );
            endif;
        }
        /* $data= new Location([
            'id' => $row['location_id'],
            'brand_id' => $this->brand_id,
            'time_active' => $row['time_active'],
            'phone' => $row['phone'],
            'email' => $row['email'],
            'lat' => $row['lat'],
            'long' => $row['long'],
            'address' => $row['branch_location'],
            'city' => $row['city'],
            'site_status' => 1,
            'sort' => $row['location_id'],
        ]);

        //return $dataInsert;
        //$id = Location::insertGetId($dataInsert);

        $dataDes[] = new Description([
            //'config_id' => $id,
            'lang' => 'en',
            'title' => $row['city'],
            'address' => $row['branch_location']
        ]);
        $data->descriptions()->attach($dataDes);


       // Description::insert($dataDes);
        return $data;*/

    }

    public function getRowCount(): int
    {
        return $this->rows;
    }
}
