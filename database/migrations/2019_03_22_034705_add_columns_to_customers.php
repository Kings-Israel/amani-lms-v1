<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->after('branch_id')->nullable();
            $table->unsignedBigInteger('industry_id')->after('account_id')->nullable();
            $table->unsignedBigInteger('business_type_id')->after('industry_id')->nullable();
            $table->unsignedTinyInteger('is_employed')->after('status')->nullable();
            $table->string('employment_status')->after('is_employed')->nullable();
            $table->string('alternate_phone')->after('phone');

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('industry_id')->references('id')->on('industries')->onDelete('cascade');
            $table->foreign('business_type_id')->references('id')->on('business_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('account_id');
            $table->dropColumn('industry_id');
            $table->dropColumn('business_type_id');
            $table->dropColumn('is_employed');
            $table->dropColumn('employment_status');
            $table->dropColumn('alternate_phone');
        });
    }
}
