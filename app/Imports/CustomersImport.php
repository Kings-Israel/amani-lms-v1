<?php
namespace App\Imports;

use App\models\Customer;
use App\models\County;
use App\models\Referee;
use App\Http\Controllers\RegistryController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

// use App\models\User;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;



class CustomersImport implements ToCollection
{
    public $insertedCount = 0;
    public $errors = [];
    protected $relationshipOfficerId;
    protected $branchId;

    public function __construct($relationshipOfficerId)
    {
        $this->relationshipOfficerId = $relationshipOfficerId;
        $this->branchId = User::find($relationshipOfficerId)->branch_id;

    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {
            if ($key === 0) {
                continue;
            }

            $data = [
                'customer_type' => $row[0],
                'customer_title' => $row[1],
                'first_name' => $row[2],
                'last_name' => $row[3],
                'mobile_line' => $row[4],
                'identity_number' => $row[5],
                'country' => $row[6],
                'county' => $row[7],
                'constituency' => $row[8],
                'ward' => $row[9],
                'loan_amount' => $row[10],
                'product_id' => $row[11],
                'installments' => $row[12],
                'loan_type' => $row[13],
                'loan_applications_number' => $row[14],
            ];

            $validator = Validator::make($data, [
                'customer_type' => ['required'],
                'customer_title' => ['required'],
                'first_name' => ['required'],
                'last_name' => ['required'],
                'mobile_line' => ['required'],
                'identity_number' => ['required'],
                'country' => ['required'],
                'county' => ['required'],
                'constituency' => ['required'],
                'ward' => ['required'],
                'loan_amount' => ['required'],
                'product_id' => ['required'],
                'installments' => ['required'],
                'loan_type' => ['required'],
                'loan_applications_number' => ['min:0'],
            ]);

            if ($validator->fails()) {
                $this->errors[] = [
                    'row' => $key + 1,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            try {
                $phone_number = '254' . substr($data['mobile_line'], -9);

                $customer = Customer::where('phone', $phone_number)->first();
                if ($customer) {
                    $this->errors[] = [
                        'row' => $key + 1,
                        'error' => 'The customer already exists in the system',
                    ];
                    continue;
                }

                $customerData = [
                    "type" => $data['customer_type'],
                    "title" => $data['customer_title'],
                    "fname" => $data['first_name'],
                    "lname" => $data['last_name'],
                    "field_agent_id" => $this->relationshipOfficerId,
                    "phone" => $phone_number,
                    "id_no" => $data['identity_number'],
                    "branch_id" => $this->branchId,
                    "document_id" => 1,
                    "prequalified_amount" => $data['loan_amount'],
                    "times_loan_applied" => $data['loan_applications_number'] ?? 2,
                ];

                $county = County::where('cname', 'like', '%' . $data['county'] . '%')->first();

                $customerLocation = [
                    "country" => $data['country'],
                    "county_id" => $county ? $county->id : null,
                    "constituency" => $data['constituency'],
                    "ward" => $data['ward'],
                ];

                DB::transaction(function () use ($customerData, $customerLocation) {
                    $customer = Customer::create($customerData);
                    $customer->location()->create($customerLocation);
                    $this->insertedCount++;
                });

            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $key + 1,
                    'error' => $e->getMessage(),
                ];
            }
        }
    }
}

