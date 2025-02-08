<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRejectedToCheckOffLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('check_off_loans', function (Blueprint $table) {
            $table->boolean('rejected')->default(false)->after('settled_at');

            $table->unsignedBigInteger('rejected_by')->nullable()->after('settled_at');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('restrict');

            $table->timestamp('rejected_at')->nullable()->after('settled_at');
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
            $table->dropForeign(['rejected_by']);
            $table->dropColumn(['rejected', 'rejected_by', 'rejected_at']);
        });
    }
}
