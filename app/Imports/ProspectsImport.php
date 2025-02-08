<?php

namespace App\Imports;

use App\models\Prospect;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\BeforeImport;

class ProspectsImport implements ToModel, WithStartRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function startRow(): int
    {
        return 2;
    }
    public function model(array $row)
    {

        $now = date('Y-m-d H:i:s');
        $find = Prospect::where(['name'=>$row[0],  'phone' => $row[1]])->first();
        if (!$find){
            $prospect = Prospect::updateOrCreate([
                'name'     => $row[0],
                'phone'    => $row[1],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $prospect;
        }else{
            return null;
        }
    }
}
