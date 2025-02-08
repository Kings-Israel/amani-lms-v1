<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInterestToCheckOffLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('check_off_loans', function (Blueprint $table) {
            $table->unsignedDecimal('total_amount', 8, 2)->after('loan_amount');
            $table->unsignedDecimal('interest', 8, 2)->after('loan_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('check_off_loans', function (Blueprint $table) {
            $table->dropColumn('interest');
            $table->dropColumn('total_amount');
        });
    }
}
