<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <title>PERFORMANCE TRACKER SCORE SHEET</title>

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
            <h3 style="text-align: center" class="text-centre">PERFORMANCE TRACKER SCORE SHEET</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-6"></div>
        <div class="col-xs-5">
            <table style="width: 100%">
                <tbody>
                <tr class="well" style="padding: 5px">
                    <th style="padding: 5px"><div> Compiled At: </div></th>
                    <td style="padding: 5px" class="text-right"><strong> {{date('D-M-Y H:i:s')}} </strong></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-bottom: 0px">&nbsp;</div>
    <table class="table table-bordered table-responsive" style="width: 100%">
        <thead style="background: #F5F5F5;">
        <tr>
            <th>C.O</th>
            <th>New <br>Customers</th>
            <th>OLB</th>
            <th>Repayment<br>Rate</th>
            <th>PAR</th>
            <th>Skipped</th>
            <th>1-30days<br>Arrears</th>
            <th>R.Over</th>
            <th>Delinquent<br> Loans</th>
            <th>Disbursed<br> Loans</th>
            <th>% Disbursed</th>
            <th>Loan Size</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['credit_officers'] as $ln)
            <tr>
                <td>{{$ln['CO_name']}}</td>
                <td>{{$ln['customers']}}</td>
                <td>{{number_format($ln['OLB'])}}</td>
                <td>{{number_format($ln['repayment_rate'], 2)}} %</td>
                <td>{{$ln['par']}} %</td>
                <td> - {{$ln['skipped']}} <br> - {{number_format($ln['skipped_amount'])}}</td>
                <td>{{number_format($ln['1_to_30'])}}</td>
                <td> - {{$ln['rolled_over']}} <br> - {{number_format($ln['rolled_over_amount'])}}</td>
                <td>{{number_format($ln['non_performing_amount'])}}</td>
                <td> - {{$ln['disb_loans']}} <br> - {{number_format($ln['disb_loans_amount'])}}</td>
                <td>{{$ln['perc_disb']}} %</td>
                <td>{{$ln['loan_size']}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-bottom: 0px">&nbsp;</div>
    <table class="table table-bordered table-responsive" style="width: 100%">
    <thead style="background: #F5F5F5;">
        <tr>
            <th>Branch</th>
            <th>New <br>Customers</th>
            <th>OLB</th>
            <th>Repayment<br>Rate</th>
            <th>PAR</th>
            <th>Skipped</th>
            <th>1-30days<br>Arrears</th>
            <th>R.Over</th>
            <th>Delinquent<br> Loans</th>
            <th>Disbursed<br> Loans</th>
            <th>% Disbursed</th>
            <th>Loan Size</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['branches'] as $ln)
            <tr>
                <td>{{$ln['CO_name']}}</td>
                <td>{{$ln['customers']}}</td>
                <td>{{number_format($ln['OLB'])}}</td>
                <td>{{number_format($ln['repayment_rate'], 2)}} %</td>
                <td>{{$ln['par']}} %</td>
                <td> - {{$ln['skipped']}} <br> - {{number_format($ln['skipped_amount'])}}</td>
                <td>{{number_format($ln['1_to_30'])}}</td>
                <td> - {{$ln['rolled_over']}} <br> - {{number_format($ln['rolled_over_amount'])}}</td>
                <td>{{number_format($ln['non_performing_amount'])}}</td>
                <td> - {{$ln['disb_loans']}} <br> - {{number_format($ln['disb_loans_amount'])}}</td>
                <td>{{$ln['perc_disb']}} %</td>
                <td>{{$ln['loan_size']}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div style="margin-bottom: 0px">&nbsp;</div>
</div>

</body>
</html>
