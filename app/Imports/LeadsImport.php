<?php

namespace App\Imports;

use App\models\Lead;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class LeadsImport implements ToCollection, WithHeadingRow
{
    public $inserted = 0;
    public $existing = 0;
    public $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                if (!isset($row['name']) || !isset($row['phone']) || !isset($row['business']) || !isset($row['location'])) {
                    throw new \Exception('Missing required fields in row ' . ($index + 1));
                }

                $leadExists = Lead::where('phone_number', $row['phone'])->exists();
                if ($leadExists) {
                    $this->existing++;
                    continue;
                }

                Lead::create([
                    'name' => $row['name'],
                    'phone_number' => $row['phone'],
                    'type_of_business' => $row['business'],
                    'location' => $row['location'],
                    'estimated_amount' => $row['amount'] ?? null,
                    'officer_id' => auth()->id(),
                    'created_at' => Carbon::now()
                ]);
                $this->inserted++;

            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $index + 1,
                    'error' => $e->getMessage()
                ];
            }
        }
    }
}
