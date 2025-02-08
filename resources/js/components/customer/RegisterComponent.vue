<template>
    <div>
        <form @submit.prevent="onComplete" method="post">
            <!-- Personal information -->
            <div class="content-container">
                <h5 class="my-2 text-primary">Customer Personal Information</h5>
                <div class="form-group row">
                    <!-- type -->
                    <div class="col-md-3 my-1">
                        <label class="block">Type</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>

                            <v-select id="v-select" name="customer type" v-model.trim="$v.personal.customer_type.$model" required
                                :options="['Individual', 'Company']"></v-select>
                        </div>

                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.customer_type.$error && !$v.personal.customer_type.required">* Customer
                                type is required</div>
                        </transition>
                    </div>

                    <!-- title -->
                    <div class="col-md-3 my-1">
                        <label class="block">Title</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>

                            <v-select id="v-select" name="customer title" v-model="$v.personal.customer_title.$model" required
                                :options="['Mr', 'Mrs']"></v-select>
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.customer_title.$error && !$v.personal.customer_title.required">*
                                Customer title is required</div>
                        </transition>
                    </div>

                    <!-- first name -->
                    <div class="col-md-3 my-1">
                        <label class="block">First Name</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <input v-model="$v.personal.first_name.$model" name="first_name" type="text" required
                                class="form-control" placeholder="First Name">
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.first_name.$error && !$v.personal.first_name.required">* First name is
                                required</div>
                        </transition>
                    </div>

                    <!-- middle name -->
                    <div class="col-md-3 my-1">
                        <label class="block">Middle Name</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <input v-model="$v.personal.middle_name.$model" name="middle_name" type="text"
                                class="form-control" placeholder="Middle Name">
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.middle_name.$error && !$v.personal.middle_name.required">* Middle name
                                is required</div>
                        </transition>
                    </div>

                    <!-- last name -->
                    <div class="col-md-3 my-1">
                        <label class="block">Last Name</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <input v-model="$v.personal.last_name.$model" name="last_name" type="text" class="form-control" required
                                placeholder="Last Name">
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.last_name.$error && !$v.personal.last_name.required">* Last name is
                                required</div>
                        </transition>
                    </div>

                    <!-- relationship officer -->
                    <div class="col-md-3 my-1">
                        <label class="block">Relationship Officer</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <v-select id="v-select" name="$v.personal.relationship officer.$model" required
                                v-model="$v.personal.relationship_officer.$model"
                                :options="Object.values(formData.relationshipOfficers)"></v-select>
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.relationship_officer.$error && !$v.personal.relationship_officer.required">
                                * Relationship officer is required</div>
                        </transition>
                    </div>

                    <div class="col-md-3 my-1">
                        <label class="block"> Supervisor </label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <v-select id="v-select" name="$v.personal.supervisor.$model" required
                                v-model="$v.personal.supervisor.$model"
                                :options="Object.values(formData.supervisors)"></v-select>
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.supervisor.$error && !$v.personal.supervisor.required">
                                * Supervisor is required</div>
                        </transition>
                    </div>


                    <!-- tax pin -->
                    <!-- <div class="col-md-3 my-1">
                        <label class="block">Tax PIN</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-lock"></i>
                            </span>
                            <input v-model="$v.personal.tax_pin.$model" name="tax_pin" type="text" class="form-control"
                                placeholder="Tax PIN">
                        </div>
                        <transition name="fade">
                            <div class="text-danger" v-show="$v.personal.tax_pin.$error && !$v.personal.tax_pin.required">*
                                Tax PIN is required</div>
                        </transition>
                    </div> -->

                    <!-- gender -->
                    <!-- <div class="col-md-3 my-1">
                        <label class="block">Gender</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <v-select id="v-select" name="gender" v-model="$v.personal.gender.$model"
                                :options="['Male', 'Female']"></v-select>
                        </div>
                        <transition name="fade">
                            <div class="text-danger" v-show="$v.personal.gender.$error && !$v.personal.gender.required">*
                                Gender is required</div>
                        </transition>
                    </div> -->

                    <!-- date of birth -->
                    <!-- <div class="col-md-3 my-1">
                        <label class="block">Date of Birth</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>

                            <VueCtkDateTimePicker formatted="YYYY-MM-DD" :noButtonNow="true" :onlyDate="true" inputSize="sm"
                                color="#8EC63E" :right="true" :noHeader="true"
                                :maxDate="this.eighteenYearsAgo.format('YYYY-MM-DD')" format='YYYY-MM-DD'
                                outputFormat='YYYY-MM-DD' v-model="$v.personal.date_of_birth.$model" name="date_of_birth" />
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.date_of_birth.$error && !$v.personal.date_of_birth.required">* Date of
                                birth is required</div>
                        </transition>
                    </div> -->

                    <!-- mobile line -->
                    <div class="col-md-3 my-1">
                        <label class="block">Mobile Line (7xxxxxxxx)</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-mobile-phone"></i>
                            </span>
                            <input v-model="$v.personal.mobile_line.$model" name="mobile_line" type="tel" required
                                class="form-control" placeholder="Mobile phone number">
                        </div>
                        <transition name="fade">
                            <div>
                                <div class="text-danger"
                                    v-show="$v.personal.mobile_line.$error && !$v.personal.mobile_line.required">* Mobile
                                    line is required</div>

                                <div class="text-danger"
                                    v-show="$v.personal.mobile_line.$error && !$v.personal.mobile_line.uniquePhoneNumber">*
                                    Mobile line is already registered</div>

                                <div class="text-danger"
                                    v-show="$v.personal.mobile_line.$error && !$v.personal.mobile_line.minLength">* Mobile
                                    line should be at least 9 digits</div>
                            </div>
                        </transition>
                    </div>

                    <!-- email -->
                    <div class="col-md-3 my-1">
                        <label class="block">Email</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-email"></i>
                            </span>
                            <input v-model="$v.personal.email.$model" name="email" type="email" class="form-control"
                                placeholder="Email Address">
                        </div>

                        <transition name="fade">
                            <div class="text-danger" v-show="$v.personal.email.$error && !$v.personal.email.email">* Should
                                be a valid email</div>
                        </transition>
                    </div>

                    <!-- identity type -->
                    <!-- <div class="col-md-3 my-1">
                        <label class="block">Identity Type</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-id"></i>
                            </span>
                            <v-select id="v-select" name="identity type" v-model="$v.personal.identity_type.$model"
                                :options="Object.values(formData.idTypes)"></v-select>
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.identity_type.$error && !$v.personal.identity_type.required">* ID type
                                is required</div>
                        </transition>
                    </div> -->

                    <!-- identity number -->
                    <div class="col-md-3 my-1">
                        <label class="block">Identity Number</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-id"></i>
                            </span>
                            <input v-model="$v.personal.identity_number.$model" name="identity_number" type="text" required
                                class="form-control" placeholder="ID Number">
                        </div>
                        <transition name="fade">
                            <div>
                                <div class="text-danger"
                                    v-show="$v.personal.identity_number.$error && !$v.personal.identity_number.required">*
                                    ID number is required</div>

                                <div class="text-danger"
                                    v-show="$v.personal.identity_number.$error && !$v.personal.identity_number.uniqueIdNumber">
                                    * ID number is already registered</div>
                            </div>
                        </transition>
                    </div>

                    <!-- marital status -->
                    <!-- <div class="col-md-3 my-1">
                        <label class="block">Marital Status</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-users-alt-4"></i>
                            </span>

                            <v-select id="v-select" name="marital status" v-model="$v.personal.marital_status.$model"
                                :options="['Single', 'Married', 'Divorced', 'Widowed']"></v-select>
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.marital_status.$error && !$v.personal.marital_status.required">* Marital
                                status is required</div>
                        </transition>
                    </div> -->

                    <!-- next of kin full name -->
                    <!-- <div class="col-md-3 my-1">
                        <label class="block">Next of Kin Full Name</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <input v-model="$v.personal.next_of_kin.$model" name="next_of_kin" type="text"
                                class="form-control" placeholder="Next of Kin Full Name">
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.next_of_kin.$error && !$v.personal.next_of_kin.required">* Next of kin
                                is required</div>
                        </transition>
                    </div> -->

                    <!-- next of kin relationship -->
                    <!-- <div class="col-md-3 my-1">
                        <label class="block">Relationship</label>

                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-users-alt-4"></i>
                            </span>
                            <v-select id="v-select" name="next of kin relationship"
                                v-model="$v.personal.next_of_kin_relationship.$model"
                                :options="Object.values(formData.kinRelations)"></v-select>
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.next_of_kin_relationship.$error && !$v.personal.next_of_kin_relationship.required">
                                * Next of kin relationship is required</div>
                        </transition>
                    </div> -->

                    <!-- next of kin mobile line -->
                    <!-- <div class="col-md-3 my-1">
                        <label class="block">Next of Kin Mobile (7xxxxxxxx)</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-mobile-phone"></i>
                            </span>
                            <input v-model="$v.personal.next_of_kin_mobile_no.$model" name="next_of_kin_mobile_no"
                                type="number" class="form-control" placeholder="Next of Kin Mobile Number">
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.next_of_kin_mobile_no.$error && !$v.personal.next_of_kin_mobile_no.required">
                                * Next of kin mobile no is required</div>
                        </transition>

                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.next_of_kin_mobile_no.$error && !$v.personal.next_of_kin_mobile_no.minLength">
                                * Mobile no. should be at least 9 digits</div>
                        </transition>
                    </div> -->

                    <!-- prequalified amount -->
                    <!-- <div class="col-md-3 my-1">
                        <label class="block">Prequalified Amount</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-money"></i>
                            </span>
                            <v-select id="v-select" name="prequalified amount"
                                v-model="$v.personal.prequalified_amount.$model"
                                :options="Object.values(formData.prequalifiedAmount)"></v-select>
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.prequalified_amount.$error && !$v.personal.prequalified_amount.required">
                                * Prequalified amount is required</div>
                        </transition>
                    </div> -->

                    <!-- alternate mobile line -->
                    <div class="col-md-3 my-1">
                        <label class="block">Alternate Mobile Line</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-mobile-phone"></i>
                            </span>
                            <input v-model="$v.personal.alternate_mobile_line.$model" name="alternate_mobile_line"
                                type="tel" class="form-control" placeholder="Alternate Mobile Line">
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.alternate_mobile_line.$error && !$v.personal.alternate_mobile_line.required">
                                * Alternate mobile line is required</div>
                        </transition>

                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.alternate_mobile_line.$error && !$v.personal.alternate_mobile_line.minLength">
                                * Should be at least 9 digits</div>
                        </transition>
                    </div>

                    <div class="col-md-3 my-1">
                        <label class="block">No. of Loan Applications</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-mobile-phone"></i>
                            </span>
                            <input v-model="$v.personal.loan_applications_number.$model" name="loan_applications_number"
                                type="number" class="form-control" placeholder="No. of Loans Customer has gotten" min="0">
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.loan_applications_number.$error && !$v.personal.loan_applications_number.required">
                                * This is required</div>
                        </transition>

                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.loan_applications_number.$error && !$v.personal.loan_applications_number.minValue">
                                * Should be at least 0</div>
                        </transition>
                    </div>
                </div>
                <!-- referees-->
                <div class="form-group row" v-for="(referee, k) in personal.referees" :key="k">
                    <!-- referee full name -->
                    <div class="col-md-3 my-1">
                        <label class="block">{{ ordinal_suffix_of(k + 1) }} Referee Full Name</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <input v-model="referee.full_name" name="full_name" type="text" class="form-control"
                                placeholder="Referee Full Name">
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.referees.$each[k].full_name.$error && !$v.personal.referees.$each[k].full_name.required">
                                * Kindly Fill in the Referee's Full Name</div>
                        </transition>
                    </div>
                    <!-- referee id -->
                    <div class="col-md-3 my-1">
                        <label class="block"> {{ ordinal_suffix_of(k + 1) }} Referee National ID Number</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-id-card"></i>
                            </span>
                            <input v-model="referee.id_number" name="referee_id_number" type="number" class="form-control"
                                placeholder="Referee ID Number">
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.referees.$each[k].id_number.$error && !$v.personal.referees.$each[k].id_number.required">
                                * Kindly Fill in the Referee's National ID Number</div>
                        </transition>
                    </div>
                    <!-- referee mobile line -->
                    <div class="col-md-3 my-1">
                        <label class="block">{{ ordinal_suffix_of(k + 1) }} Referee Mobile (07xxxxxxxx)</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-mobile-phone"></i>
                            </span>
                            <input v-model="referee.phone_number" name="referee_phone_number" type="tel"
                                pattern="\d{4}\d{3}\d{3}" title="'Phone Number (Format: 0712345678)'" class="form-control"
                                placeholder="Referee Mobile Number">
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.personal.referees.$each[k].phone_number.$error && !$v.personal.referees.$each[k].phone_number.required">
                                * Kindly Fill in the Referee's Phone Number</div>
                        </transition>
                    </div>

                    <div class="col-md-3">
                        <span>
                            <button class="btn btn-sm btn-secondary" @click="addReferee(k)"
                                v-show="k == personal.referees.length - 1 && k < 3">Add {{ ordinal_suffix_of(k + 2) }} Referee
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" @click="removeReferee(k)"
                                v-show="k || (!k && personal.referees.length > 1)">Remove {{ ordinal_suffix_of(k + 1) }}
                                Referee</button>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Loan Details -->
            <div class="content-container">
                <h5 class="my-2 text-primary">Loan Details</h5>
                <div class="form-group row">
                    <div class="col-md-3">
                        <label class="block">Loan Product</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <v-select id="v-select" name="product_id" v-model="loan.product_id"
                                :options="Object.values(formData.loanProducts)"></v-select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="block">Loan Repayment Type</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <v-select id="v-select" name="loan_type" v-model="loan.loan_type"
                                :options="Object.values(formData.loanTypes)"></v-select>
                        </div>
                    </div>
                    <div class="col-md-3 my-1">
                        <label class="block">Negotiated Installments</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <input v-model="loan.installments" name="installments" class="form-control" type="text"
                                placeholder="Installments" readonly>
                        </div>
                    </div>
                    <div class="col-md-3 my-1">
                        <label class="block">Loan Amount</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-money"></i>
                            </span>
                            <v-select
                                id="v-select"
                                name="loan amount"
                                v-model="loan.loan_amount"
                                style="max-height: 200px !important"
                                :options="Object.values( formData.prequalifiedAmount )"
                            ></v-select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="block">Loan Purpose</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon"><i class="icofont icofont-location-pin"></i>
                            </span>
                            <v-select id="v-select" name="product_id" v-model="loan.purpose"
                                :options="Object.values(formData.loanPurposeType)"></v-select>
                        </div>
                    </div>
                    <!-- business type -->
                    <div class="col-md-4 my-1">
                        <label class="block">Business Type</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-office"></i>
                            </span>
                            <v-select
                                id="v-select"
                                name="business_type"
                                v-model="profession.business_type"
                                :options="Object.values(formData.businessTypes).concat('Other')"
                            ></v-select>
                        </div>
                    </div>

                    <!-- Conditional input for custom business type name -->
                    <div v-if="profession.business_type === 'Other'" class="col-md-4 my-1">
                        <label class="block">Custom Business Type</label>
                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-edit"></i>
                            </span>
                            <input
                                type="text"
                                class="form-control"
                                v-model="profession.custom_business_type"
                                placeholder="Enter custom business type"
                            />
                        </div>
                    </div>


                </div>
            </div>

            <!-- Location -->
            <div class="content-container">
                <h5 class="my-2 text-primary">Location</h5>
                <div class="form-group row">
                    <!-- country -->
                    <div class="col-md-3 my-1">
                        <label class="block">Country</label>

                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <v-select id="v-select" name="country" v-model="$v.location.country.$model" required
                                :options="['Kenya']"></v-select>
                        </div>
                        <transition name="fade">
                            <div class="text-danger" v-show="$v.location.country.$error && !$v.location.country.required">*
                                Country is required</div>
                        </transition>
                    </div>

                    <!-- county -->
                    <div class="col-md-3 my-1">
                        <label class="block">County</label>

                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <v-select id="v-select" name="county" v-model="$v.location.county.$model" required
                                :options="Object.values(formData.counties)"></v-select>
                        </div>
                        <transition name="fade">
                            <div class="text-danger" v-show="$v.location.county.$error && !$v.location.county.required">*
                                County is required</div>
                        </transition>
                    </div>

                    <!-- constituency -->
                    <div class="col-md-3 my-1">
                        <label class="block">Constituency</label>

                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <input v-model="$v.location.constituency.$model" name="constituency" type="text" required
                                class="form-control" placeholder="Enter Constituency">
                        </div>
                        <transition name="fade">
                            <div class="text-danger"
                                v-show="$v.location.constituency.$error && !$v.location.constituency.required">* Constituency is required</div>
                        </transition>
                    </div>

                    <!-- ward -->
                    <div class="col-md-3 my-1">
                        <label class="block">Ward</label>

                        <div class="input-group mb-1">
                            <span class="input-group-addon">
                                <i class="icofont icofont-ui-user"></i>
                            </span>
                            <input v-model="$v.location.ward.$model" name="ward" type="text" class="form-control" required
                                placeholder="Enter Ward">
                        </div>
                        <transition name="fade">
                            <div class="text-danger" v-show="$v.location.ward.$error && !$v.location.ward.required">* Ward is required</div>
                        </transition>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" :disabled="completed" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</template>

<script>
import { FormWizard, TabContent } from "vue-form-wizard";
import "vue-form-wizard/dist/vue-form-wizard.min.css";

import Datepicker from "vuejs-datepicker";
import moment from "moment";

import VueCtkDateTimePicker from 'vue-ctk-date-time-picker';
import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css';

import axios from "axios";
import toastr from "toastr";

import { required, requiredIf, minLength, email, alphaNum, integer, minValue } from "vuelidate/lib/validators";

export default {
    name: "RegisterComponent",

    components: {
        VueCtkDateTimePicker,
        Datepicker
    },

    data() {
        return {
            rootUrl: "/",
            completed: false,
            eighteenYearsAgo: moment().subtract(18, 'years'),
            personal: {
                customer_type: "",
                customer_title: "",
                first_name: "",
                middle_name: "",
                last_name: "",
                relationship_officer: "",
                supervisor: "",
                tax_pin: "",
                gender: "",
                date_of_birth: "",
                mobile_line: "",
                email: "",
                identity_type: "",
                identity_number: "",
                marital_status: "",
                next_of_kin: "",
                next_of_kin_relationship: "",
                next_of_kin_mobile_no: "",
                prequalified_amount: "",
                alternate_mobile_line: "",
                loan_applications_number: 0,
                referees: [
                    {
                        full_name: "",
                        id_number: "",
                        phone_number: "",
                    }
                ]
            },

            location: {
                postal_address: "",
                postal_code: "",
                country: "",
                county: "",
                constituency: "",
                ward: "",
            },

            profession: {
                business_type: "",
            },

            loan: {
                product_id: "",
                loan_type: "",
                purpose: "",
                installments: "",
                loan_amount: "",
                loan_form: null,
            },

            formData: {
                relationshipOfficers: {},
                supervisors: {},
                idTypes: {},
                kinRelations: {},
                counties: {},
                industries: {},
                businessTypes: {},
                accounts: {},
                incomeRanges: {},
                prequalifiedAmount: {},
                constituenciesAndWards: {},
                constituencies: {},
                wards: {},
                loanProducts: {},
                loanTypes: {},
                loanPurposeType: {},
            }
        };
    },

    validations: {
        personal: {
            customer_type: { required },
            customer_title: { required },
            first_name: { required },
            middle_name: {},
            last_name: { required },
            relationship_officer: { required },
            supervisor: { required },
            mobile_line: {
                required, minLength: minLength(9), uniquePhoneNumber(phone_number) {
                    if (phone_number === '') return true;
                    let country_code = "254";
                    let phone_country_code = country_code + phone_number.slice(-9);
                    return axios.get(`${this.rootUrl}registry-data/unique-phone-number/${phone_country_code}`)
                        .then(res => {
                            return res.data
                        })
                }
            },
            email: {},
            identity_number: {
                required, alphaNum, uniqueIdNumber(id_number) {
                    if (id_number === '') return true
                    return axios.get(`${this.rootUrl}registry-data/unique-id-number/${id_number}`)
                        .then(res => {
                            return res.data
                        })
                }
            },
            prequalified_amount: { required },
            alternate_mobile_line: {},
            loan_applications_number: {
                // required,
                minValue: minValue(0)
            },
            referees: {
                $each: {
                    full_name: { required },
                    id_number: { required },
                    phone_number: { required, minLength: minLength(9) },
                }
            }
        },

        location: {
            country: { required },
            county: { required },
            constituency: { required },
            ward: {},
        },
    },

    created() {
        this.relationshipOfficers();
        this.supervisors();
        this.idTypes();
        this.kinRelations();
        this.counties();
        this.prequalifiedAmount();
        this.accounts();
        this.loanTypes();
        this.loanProducts();
        this.businessTypes();
        this.formData.loanPurposeType = [
            { "label": "Business Expense", "value": "Business Expense" },
            { "label": "Start Business", "value": "Start Business" }
        ];
    },

    watch: {
        "location.county": function () {
            this.formData.constituencies = {};
            this.location.constituency = "";
        },

        "location.constituency": function () {
            this.formData.wards = {};
            this.location.ward = "";
        },

        "loan.product_id": function (newValue, currentValue) {
            this.loan.installments = newValue.installments
        },
    },

    mounted: function () {},

    methods: {
        addReferee(index) {
            this.personal.referees.push({ full_name: "", id_number: "", phone_number: "" });
        },

        removeReferee(index) {
            this.personal.referees.splice(index, 1);
        },

        ordinal_suffix_of(i) {
            var j = i % 10,
                k = i % 100;
            if (j == 1 && k != 11) {
                return i + "st";
            }
            if (j == 2 && k != 12) {
                return i + "nd";
            }
            if (j == 3 && k != 13) {
                return i + "rd";
            }
            return i + "th";
        },

        relationshipOfficers: async function () {
            try {
                const response = await axios.get(
                    `${this.rootUrl}registry-data/relationship-officers`
                );
                this.formData.relationshipOfficers = response.data;
            } catch (err) {
                this.error = err;
            }
        },

        supervisors: async function () {
            try {
                const response = await axios.get(
                    `${this.rootUrl}registry-data/supervisors`
                );
                this.formData.supervisors = response.data;
            } catch (err) {
                this.error = err;
            }
        },

        idTypes: async function () {
            try {
                const response = await axios.get(`${this.rootUrl}registry-data/id-types`);
                this.formData.idTypes = response.data;
            } catch (err) {
                this.error = err;
            }
        },

        kinRelations: async function () {
            try {
                const response = await axios.get(`${this.rootUrl}registry-data/kin-relations`);
                this.formData.kinRelations = response.data;
            } catch (err) {
                this.error = err;
            }
        },

        counties: async function () {
            try {
                const response = await axios.get(`${this.rootUrl}registry-data/counties`);
                this.formData.counties = response.data;
            } catch (err) {
                this.error = err;
            }
        },

        constituenciesAndWards: async function () {

            try {
                let county = this.location.county.label;
                let url = `${"https://cors-anywhere.herokuapp.com/"}https://frozen-basin-45055.herokuapp.com/api/wards?county=${county}`;

                const response = await axios.get(url);
                this.formData.constituenciesAndWards = response.data;

                this.constituencies();
            } catch (err) {
                this.error = err;
            }

        },

        constituencies: function () {
            // get all constituencies in selected county.
            let data = this.formData.constituenciesAndWards
                .filter(item => item.county === this.location.county.label)
                .map(item => {
                    return { label: item.constituency, value: item.constituency };
                });

            // remove repeating constituencies
            this.formData.constituencies = this.getUnique(data, "label");
            // this.wards()
        },

        getUnique: function (arr, comp) {
            const unique = arr
                .map(e => e[comp])

                // store the keys of the unique objects
                .map((e, i, final) => final.indexOf(e) === i && i)

                // eliminate the dead keys & store unique objects
                .filter(e => arr[e])
                .map(e => arr[e]);

            return unique;
        },

        wards: function () {
            this.formData.wards = this.formData.constituenciesAndWards
                .filter(
                    item =>
                        item.county === this.location.county.label &&
                        item.constituency === this.location.constituency.label
                )
                .map(item => {
                    return { label: item.name, value: item.name };
                });
        },

        prequalifiedAmount: async function () {
            try {
                const response = await axios.get(`${this.rootUrl}registry-data/prequalified-amount`);
                this.formData.prequalifiedAmount = response.data;
            } catch (err) {
                this.error = err;
            }
        },

        businessTypes: async function () {
            try {
                const response = await axios.get(
                    `${this.rootUrl}registry-data/business-types`
                );
                this.formData.businessTypes = response.data;
            } catch (err) {
                this.error = err;
            }
        },

        loanTypes: async function () {
            try {
                const response = await axios.get(
                    `${this.rootUrl}registry-data/loan-types/`
                );
                this.formData.loanTypes = response.data;
            } catch (err) {
                this.error = err;
            }
        },

        loanProducts: async function () {
            try {
                const response = await axios.get(
                    `${this.rootUrl}registry-data/loan-products/`
                );
                this.formData.loanProducts = response.data;
            } catch (err) {
                this.error = err;
            }
        },

        accounts: async function () {
            try {
                const response = await axios.get(`${this.rootUrl}registry-data/accounts`);
                this.formData.accounts = response.data;
            } catch (err) {
                this.error = err;
            }
        },

        onComplete: function () {
            // disable the submit button to prevent double clicking.
            this.completed = true;

            toastr.options = {
                positionClass: "toast-top-center"
            };

            // Combine all form data into a single object
            let allData = Object.assign(
                {},
                this.personal,
                this.location,
                this.profession,
                this.loan
            );

            // Log the final data being submitted
            console.log("Final data being submitted:", allData);

            let app = this;

            axios
                .post(`${this.rootUrl}registry`, allData)
                .then(res => {
                    toastr.success("You have added a new customer successfully.");
                    setTimeout(function () {
                        window.location.replace(`${app.rootUrl}registry`);
                    }, 1000);
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        let messages = [];
                        Object.keys(error.response.data).some(key => {
                            messages.push(error.response.data[key]);
                        });
                        console.log("Validation errors:", messages);
                        toastr.error(messages[0]);
                    } else {
                        console.log("Error details:", error);
                        toastr.error(`An error occurred: ${error}`);
                    }
                    // Reset the completed flag to allow resubmission
                    app.completed = false;
                });

            return true;
        },

    }
};
</script>

<style>
/* Cyan theme */
.v-select {
    width: 100%;
    background-color: #fff;
    color: #1B372B;
}

.v-select .dropdown-toggle {
    padding: 0 0 8px;
    border-radius: 2px;
}

#v-select .dropdown-toggle::after {
    display: none;
}

.v-select .dropdown-toggle .clear {
    display: none;
}

#v-select .selected-tag {
    color: #1B372B;
    background-color: #d7f3f9;
    border-color: #1B372B;
}

#v-select.dropdown.open .dropdown-toggle,
#v-select.dropdown.open .dropdown-menu {
    border-color: #1B372B;
}

.dropdown-menu {
    max-height: 200px !important;
}

#v-select .active a {
    background: rgba(50, 50, 50, 0.1);
    color: #333;
}

#v-select.dropdown .highlight a,
#v-select.dropdown li:hover a {
    background: #1B372B;
    color: #fff;
}

input,
select {
    padding: 0.4em 0.5em;
    font-size: 100%;
    border: 1px solid #ccc;
    width: 100%;
    width: auto;
    height: auto;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter,
.fade-leave-to

/* .fade-leave-active below version 2.1.8 */
    {
    opacity: 0;
}
</style>
