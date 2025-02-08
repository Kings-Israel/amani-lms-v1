$(document).ready(function () {
    calls($current_branch_id, $current_field_agent_id);

    $('#field_agent').on('change', function() {
        var $current_field_agent_id = this.value;

        calls('all',$current_field_agent_id);
    });

    //branch change
    $('#branch').on('change', function() {
        var $current_branch_id = this.value;
        calls($current_branch_id,'all');
    });

});

function calls($current_branch_id, $current_field_agent_id) {
    //online users
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }, method: "post",
        url: $_base + "ajax_dashboard/online_count",
        dataType: 'json',

        success: function (json) {
            if (json.status === "success")
                //.location.href = json.redirect;
                $('#online').text(json.data);
        }
    });

    //customers
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
           // 'Cache-Control': 'no-cache, no-store, must-revalidate',
        },
        cache: false,
        method: "post",
        url: $_base + "ajax_dashboard/customers",
        dataType: 'json',
        data: {
            branch_id: $current_branch_id,
            field_agent_id: $current_field_agent_id
        },
        success: function (json) {
            if (json.status === "success")
                //.location.href = json.redirect;
                $('#total_customers').text(new Intl.NumberFormat().format(json.data));
        }
    });

    //customers info
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },

        method: "post", url: $_base + "ajax_dashboard/customer_info", dataType: 'json', data: {
            branch_id: $current_branch_id, field_agent_id: $current_field_agent_id

        }, success: function (json) {
            if (json.status === "success")
                //.location.href = json.redirect;
                $('#customer_info_month').text(json.data[0][0]);
            $('#customer_info_new').text(json.data[0][1]);
        }
    });

    // totalAmount
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },

        method: "post", url: "ajax_dashboard/total_amount", data: {
            branch_id: $current_branch_id, field_agent_id: $current_field_agent_id
        }, dataType: 'json', success: function (json) {
            if (json.status === "success") $('#loan_amount').text(json.data['totalAmount']);
        }
    });

    // today due amount
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }, method: "post", url: "ajax_dashboard/due_today_amount", data: {
            branch_id: $current_branch_id, field_agent_id: $current_field_agent_id

        }, dataType: 'json', success: function (json) {
            $('#due_today_amount').text(json.data);
        }
    });


    //active loans
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }, method: "post", url: $_base + "ajax_dashboard/active_loans", dataType: 'json', data: {
            branch_id: $current_branch_id, field_agent_id: $current_field_agent_id

        }, success: function (json) {
            if (json.status === "success") $('#active_loans').text(json.data);
        }
    });

    //total_arrears
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }, method: "post", url: $_base + "ajax_dashboard/total_arrears", dataType: 'json', data: {
            branch_id: $current_branch_id, field_agent_id: $current_field_agent_id

        }, success: function (json) {
            if (json.status === "success") $('#total_arrears').text(new Intl.NumberFormat().format(json.data['arrears_total']));
            $('#arrears_count').text(new Intl.NumberFormat().format(json.data['arrears_count']));
        }
    });

    //total_arrears
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }, method: "post", url: $_base + "ajax_dashboard/due_loans_count", dataType: 'json', data: {
            branch_id: $current_branch_id, field_agent_id: $current_field_agent_id

        }, success: function (json) {
            if (json.status === "success") $('#loans_due_today').text(new Intl.NumberFormat().format(json.data));
        }
    });

    //$mtd_loans
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: "post",
        url: $_base + "ajax_dashboard/mtd_loans",
        dataType: 'json',
        data: {
            branch_id: $current_branch_id,
            field_agent_id: $current_field_agent_id
        },
        success: function (json) {
            if (json.status === "success") {
                $('#mtd_loans').text(json.data['mtd_loans']);
                $('#mtd_loans_amount').text(new Intl.NumberFormat().format(json.data['mtd_loan_amount']));
                $('#applied_amount').text(new Intl.NumberFormat().format(json.data['mtd_loan_applied_amount'])); 
            }
        }
    });



    // pending_approval
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }, method: "post", url: $_base + "ajax_dashboard/pending_approval", dataType: 'json', data: {
            branch_id: $current_branch_id, field_agent_id: $current_field_agent_id

        }, success: function (json) {
            if (json.status === "success") $('#pending_approval').text(json.data['pending_approval']);
            $('#pending_disbursements').text(new Intl.NumberFormat().format(json.data['pending_disbursements']));
        }
    });

    //repayment_rate
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }, method: "post", url: $_base + "ajax_dashboard/repayment_rate", dataType: 'json', data: {
            branch_id: $current_branch_id, field_agent_id: $current_field_agent_id

        }, success: function (json) {
            if (json.status === "success") $('#repayment_rate').text(json.data + '%');


        }
    });

    //PAR
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }, method: "post", url: "ajax_dashboard/PAR", dataType: 'json', data: {
            branch_id: $current_branch_id, field_agent_id: $current_field_agent_id

        }, success: function (json) {
            if (json.status === "success") $('#PAR').text(json.data + '%');


        }
    });

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: "get",
        url: "/ajax/field_agents_performance",
        dataType: 'json',
        success: function (json) {
            $('#total-payments').text(new Intl.NumberFormat().format(json.payments))
            $('#total-target').text(new Intl.NumberFormat().format(json.target))
            $('#total-performance').text(json.performance + '%')
        }
    });

    // Field agent commission
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: "post",
        url: $_base + "ajax_dashboard/total_commission", // Ensure $_base is correct
        data: {
            branch_id: $current_branch_id,
            field_agent_id: $current_field_agent_id
        },
        dataType: 'json',
        success: function (json) {
            if (json.status === "success") {
                $('#total_commission').text(new Intl.NumberFormat().format(json.data));
            }
        }
    });


    // Field Agent Collection Performance

    //chart initialization
    // var charts = {
    //     init: function () {
    //         // -- Set new default font family and font color to mimic Bootstrap's default styling
    //         Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    //         Chart.defaults.global.defaultFontColor = '#292b2c';
    //
    //         this.ajaxGetPostMonthlyData();
    //
    //     },
    //
    //     ajaxGetPostMonthlyData: function () {
    //         var month_array;
    //         $.ajax({
    //             headers: {
    //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //             },
    //             method: "post",
    //             url: "ajax_dashboard/chart",
    //             dataType: 'json',
    //             success: function (json) {
    //                 if (json.status === "success")
    //                     month_array = json.data;
    //                 charts.createCompletedJobsChart( month_array );
    //
    //
    //             }
    //         });
    //
    //
    //     },
    //
    //     /**
    //      * Created the Completed Jobs Chart
    //      */
    //     createCompletedJobsChart: function ( month_array ) {
    //
    //         var ctx = document.getElementById("myAreaChart");
    //         var myLineChart = new Chart(ctx, {
    //             type: 'bar',
    //             data: {
    //                 labels: month_array.month, // The response got from the ajax request containing all month names in the database
    //                 datasets: [{
    //                     label: "Loans Disbursed count",
    //                     type: 'line',
    //                     lineTension: 0.3,
    //                     backgroundColor: "rgba(244,176,64,0.78)",
    //                     borderColor: "rgba(219,141,13, 0.8)",
    //                     pointBorderColor: "#fff",
    //                     pointBackgroundColor: "rgba(219,141,13, 0.8)",
    //                     pointRadius: 5,
    //                     pointHoverRadius: 5,
    //                     pointHoverBackgroundColor: "rgba(219,141,13, 0.8)",
    //                     pointHitRadius: 20,
    //                     pointBorderWidth: 2,
    //                     yAxisID: 'A',
    //                     data: month_array.post_count_data // The response got from the ajax request containing data for the completed jobs in the corresponding months
    //                 },
    //                     {
    //                         label: "Loans Amount (Ksh.)",
    //                         type: 'bar',
    //                         backgroundColor: "rgba(111,163,58, 0.8)",
    //                         borderColor: "rgba(85,125,45, 0.8)",
    //                         pointRadius: 5,
    //                         pointBackgroundColor: "rgba(111,163,58, 0.8)",
    //                         pointBorderColor: "rgba(255,255,255,0.8)",
    //                         pointHoverRadius: 5,
    //                         pointHoverBackgroundColor: "rgba(111,163,58, 0.8)",
    //                         pointHitRadius: 20,
    //                         pointBorderWidth: 2,
    //                         yAxisID: 'B',
    //                         data: month_array.loan_amount // The response got from the ajax request containing data for the completed jobs in the corresponding months
    //                     }
    //                 ],
    //             },
    //             options: {
    //                 scales: {
    //                     xAxes: [{
    //                         time: {
    //                             unit: 'date'
    //                         },
    //                         gridLines: {
    //                             display: false
    //                         },
    //                         ticks: {
    //                             maxTicksLimit: 7
    //                         }
    //                     }],
    //                     yAxes: [{
    //                         id:'A',
    //                         position: 'right',
    //                         ticks: {
    //                             min: 0,
    //                             max: month_array.max_disbursement, // The response got from the ajax request containing max limit for y axis
    //                             maxTicksLimit: 5
    //                         },
    //                         gridLines: {
    //                             display:false
    //                         }
    //                     },
    //                         {id:'B',
    //                             position: 'left',
    //                             ticks: {
    //                                 min: 0,
    //                                 max: month_array.max_amount, // The response got from the ajax request containing max limit for y axis
    //                                 maxTicksLimit: 5
    //                             },
    //                             gridLines: {
    //                                 color: "rgba(0, 0, 0, .125)",
    //                             }
    //                         },
    //                     ],
    //                 },
    //                 legend: {
    //                     display: true
    //                 }
    //             }
    //         });
    //     }
    // };
    // charts.init();

}











