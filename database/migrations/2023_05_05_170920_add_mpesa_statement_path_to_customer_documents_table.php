<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMpesaStatementPathToCustomerDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_documents', function (Blueprint $table) {
            $table->string('mpesa_statement_path')->after('id_back_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_documents', function (Blueprint $table) {
            $table->dropColumn('mpesa_statement_path');
        });
    }
}
