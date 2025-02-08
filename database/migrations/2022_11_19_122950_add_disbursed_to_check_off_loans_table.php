<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisbursedToCheckOffLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('check_off_loans', function (Blueprint $table) {
            $table->unsignedBigInteger('disbursed_by')->nullable()->after('approved_by');
            $table->foreign('disbursed_by')->references('id')->on('users')->onDelete('restrict');

            $table->boolean('disbursed')->default(false)->after('approved_by');
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
            $table->dropColumn('disbursed_by');
            $table->dropColumn('disbursed');
        });
    }
}
