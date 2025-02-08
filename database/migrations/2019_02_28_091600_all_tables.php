<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relationships', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('rname');
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('dname');
            $table->timestamps();
        });
        Schema::create('counties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cname');
            $table->timestamps();
        });
        Schema::create('industries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('iname');
            $table->timestamps();
        });

        Schema::create('business_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('bname');
            $table->timestamps();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('aname');
            $table->timestamps();
        });
        Schema::create('field_agents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('ophone');
            $table->string('oemail');
            $table->timestamps();
        });


        Schema::create('guarantors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('gname');
            $table->string('gphone');
            $table->string('gdob');
            $table->string('gid');
            $table->string('marital_status');
            $table->string('location');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('industry_id');
            $table->string('business_id');
            $table->timestamps();
        });
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('fname');
            $table->string('mname')->nullable();
            $table->string('lname');
            $table->unsignedBigInteger('field_agent_id');
            $table->unsignedBigInteger('guarantor_id')->nullable();
            $table->string('tax_pin')->nullable();
            $table->string('dob')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->unsignedBigInteger('document_id');
            $table->string('id_no');
            $table->string('marital_status')->nullable();
            $table->string('gender')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->string('postal_address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Kenya');
            $table->unsignedBigInteger('county_id');

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');

            $table->string('constituency');
            $table->string('ward');
            $table->string('physical_address')->nullable();
            $table->string('residence_type')->nullable();
            $table->string('years_lived')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();
        });


        Schema::create('next_of_kins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Kin_name');
            $table->string('Kin_phone');
            $table->unsignedBigInteger('customer_id');
           /* $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');*/
            $table->integer('relationship_id');
            $table->timestamps();
        });

        Schema::create('employers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ename');
            $table->string('location');
            $table->string('latitude');
            $table->string('longitude');
            $table->timestamps();
        });



    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employers');
        Schema::dropIfExists('next_of_kins');
        Schema::dropIfExists('customer_locations');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('guarantors');
        Schema::dropIfExists('field_agents');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('business_types');
        Schema::dropIfExists('industries');
        Schema::dropIfExists('counties');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('relationships');

    }
}
