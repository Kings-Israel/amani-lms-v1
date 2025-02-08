<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <title>SKIPPED PAYMENTS AUTOMATED REPORT</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <style>
        .text-right {
            text-align: right;
        }
        .text-centre {
            text-align: center;
        }
    </style>

</head>
<body class="login-page" style="background: white">

<div>
    <div class="row">
        <div class="col-xs-4">
            <img src="http://172.171.247.139/litsa_lms/assets/images/LITSALOGO.jpg" alt="logo">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <h3 style="text-align: center" class="text-centre">SKIPPED PAYMENTS AUTOMATED REPORT</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-6"></div>
        <div class="col-xs-5">
            <table style="width: 100%">
                <tbody>
                <tr class="well" style="padding: 5px">
                    <th style="padding: 5px"><div> Number of Loans: </div></th>
                    <td style="padding: 5px" class="text-right"><strong> {{$count}} </strong></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-bottom: 0px">&nbsp;</div>
    <table class="table table-bordered table-responsive table-striped" style="width: 100%">
        <thead style="background: #F5F5F5;">
        <tr>
            <th>Branch</th>
            <th>Skipped Payments</th>
            <th>Disbursed Amount</th>
            <th>Total Loan Amount</th>
            <th>Total Arrears Amount</th>
            <th>Total Paid Amount</th>
            <th>Total Amount Due</th>
        </tr>
        </thead>
        <tbody>
        @foreach($branch_array as $name=>$ln)
            <tr>
                <td><div><strong>{{$name}}</strong></div></td>
                <td>{{$ln['skipped']}}</td>
                <td>{{number_format($ln['disb'])}}</td>
                <td>{{number_format($ln['total'])}}</td>
                <td>{{number_format($ln['arrears'])}}</td>
                <td>{{number_format($ln['paid'])}}</td>
                <td>{{number_format($ln['due'])}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="table table-bordered">
        <thead style="background: #F5F5F5;">
        <tr>
            <th>Owner</th>
            <th>Phone</th>
            <th>Branch</th>
            <th>Skipped Payments</th>
            <th>Disbursed Amount</th>
            <th>Total Loan Amount</th>
            <th>Total Arrears Amount</th>
            <th>Total Paid Amount</th>
            <th>Total Amount Due</th>
        </tr>
        </thead>
        <tbody>
        @foreach($lo as $ln)
            <tr>
                <td><div><strong>{{$ln['owner']}}</strong></div></td>
                <td><div><strong>{{$ln['phone']}}</strong></div></td>
                <td><div><strong>{{$ln['branch']}}</strong></div></td>
                <td><div><strong>{{$ln['skipped_installments']}}</strong></div></td>
                <td>{{$ln['loan_amount']}}</td>
                <td>{{$ln['total']}}</td>
                <td>{{$ln['total_arrears']}}</td>
                <td>{{$ln['amount_paid']}}</td>
                <td>{{$ln['balance']}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>


    <div style="margin-bottom: 0px">&nbsp;</div>
</div>

</body>
</html>
