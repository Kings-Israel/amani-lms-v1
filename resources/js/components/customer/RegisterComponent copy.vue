<template>
  <form-wizard @on-complete="onComplete" title subtitle color="#1B372B" errorColor="#8EC63F">
    <tab-content title="Personal information" icon="ti-user" :before-change="beforeTabSwitchPersonal" >
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

                <v-select
                    id="v-select"
                    name="customer type"
                    v-model.trim="$v.personal.customer_type.$model"
                    :options="['Individual','Company']"
                ></v-select>
                </div>

                <transition name="fade">
                <div
                    class="text-danger"
                    v-show="$v.personal.customer_type.$error && !$v.personal.customer_type.required"
                >* Customer type is required</div>
                </transition>
            </div>

          <!-- title -->
          <div class="col-md-3 my-1">
            <label class="block">Title</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>

              <v-select
                id="v-select"
                name="customer title"
                v-model="$v.personal.customer_title.$model"
                :options="['Mr','Mrs']"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.customer_title.$error && !$v.personal.customer_title.required"
              >* Customer title is required</div>
            </transition>
          </div>

          <!-- first name -->
          <div class="col-md-3 my-1">
            <label class="block">First Name</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <input
                v-model="$v.personal.first_name.$model"
                name="first_name"
                type="text"
                class="form-control"
                placeholder="First Name"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.first_name.$error && !$v.personal.first_name.required"
              >* First name is required</div>
            </transition>
          </div>

          <!-- middle name -->
          <div class="col-md-3 my-1">
            <label class="block">Middle Name</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <input
                v-model="$v.personal.middle_name.$model"
                name="middle_name"
                type="text"
                class="form-control"
                placeholder="Middle Name"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.middle_name.$error && !$v.personal.middle_name.required"
              >* Middle name is required</div>
            </transition>
          </div>

          <!-- last name -->
          <div class="col-md-3 my-1">
            <label class="block">Last Name</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <input
                v-model="$v.personal.last_name.$model"
                name="last_name"
                type="text"
                class="form-control"
                placeholder="Last Name"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.last_name.$error && !$v.personal.last_name.required"
              >* Last name is required</div>
            </transition>
          </div>

          <!-- relationship officer -->
          <div class="col-md-3 my-1">
            <label class="block">Relationship Officer</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <v-select
                id="v-select"
                name="$v.personal.relationship officer.$model"
                v-model="$v.personal.relationship_officer.$model"
                :options="Object.values( formData.relationshipOfficers )"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.relationship_officer.$error && !$v.personal.relationship_officer.required"
              >* Relationship officer is required</div>
            </transition>
          </div>

          <!-- tax pin -->
          <div class="col-md-3 my-1">
            <label class="block">Tax PIN</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-lock"></i>
              </span>
              <input
                v-model="$v.personal.tax_pin.$model"
                name="tax_pin"
                type="text"
                class="form-control"
                placeholder="Tax PIN"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.tax_pin.$error && !$v.personal.tax_pin.required"
              >* Tax PIN is required</div>
            </transition>
          </div>

          <!-- gender -->
          <div class="col-md-3 my-1">
            <label class="block">Gender</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <v-select
                id="v-select"
                name="gender"
                v-model="$v.personal.gender.$model"
                :options="['Male','Female']"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.gender.$error && !$v.personal.gender.required"
              >* Gender is required</div>
            </transition>
          </div>

          <!-- date of birth -->
          <div class="col-md-3 my-1">
            <label class="block">Date of Birth</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
                <input type="date"
                       :max="this.eighteenYearsAgo.format('YYYY-MM-DD')"
                       v-model="$v.personal.date_of_birth.$model"
                       name="date_of_birth"
                       class="form-control">

              <!-- <datepicker v-model="$v.personal.date_of_birth.$model" name="date_of_birth"></datepicker> -->
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.date_of_birth.$error && !$v.personal.date_of_birth.required"
              >* Date of birth is required</div>
            </transition>
          </div>

          <!-- mobile line -->
          <div class="col-md-3 my-1">
            <label class="block">Mobile Line (7xxxxxxxx)</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-mobile-phone"></i>
              </span>
              <input
                v-model="$v.personal.mobile_line.$model"
                name="mobile_line"
                type="tel"
                class="form-control"
                placeholder="Mobile phone number"
              >
            </div>
            <transition name="fade">
                <div>
                    <div
                        class="text-danger"
                        v-show="$v.personal.mobile_line.$error && !$v.personal.mobile_line.required"
                    >* Mobile line is required</div>

                    <div
                        class="text-danger"
                        v-show="$v.personal.mobile_line.$error && !$v.personal.mobile_line.uniquePhoneNumber"
                    >* Mobile line is already registered</div>

                    <div
                        class="text-danger"
                        v-show="$v.personal.mobile_line.$error && !$v.personal.mobile_line.minLength"
                    >* Mobile line should be at least 9 digits</div>
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
              <input
                v-model="$v.personal.email.$model"
                name="email"
                type="email"
                class="form-control"
                placeholder="Email Address"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.email.$error && !$v.personal.email.required"
              >* Email is required</div>
            </transition>

            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.email.$error && !$v.personal.email.email"
              >* Should be a valid email</div>
            </transition>
          </div>

          <!-- identity type -->
          <div class="col-md-3 my-1">
            <label class="block">Identity Type</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-id"></i>
              </span>
              <v-select
                id="v-select"
                name="identity type"
                v-model="$v.personal.identity_type.$model"
                :options="Object.values( formData.idTypes )"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.identity_type.$error && !$v.personal.identity_type.required"
              >* ID type is required</div>
            </transition>
          </div>

          <!-- identity number -->
          <div class="col-md-3 my-1">
            <label class="block">Identity Number</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-id"></i>
              </span>
              <input
                v-model="$v.personal.identity_number.$model"
                name="identity_number"
                type="text"
                class="form-control"
                placeholder="ID Number"
              >
            </div>
            <transition name="fade">
                <div>
                    <div
                        class="text-danger"
                        v-show="$v.personal.identity_number.$error && !$v.personal.identity_number.required"
                    >* ID number is required</div>

                    <div
                        class="text-danger"
                        v-show="$v.personal.identity_number.$error && !$v.personal.identity_number.uniqueIdNumber"
                    >* ID number is already registered</div>
                </div>
            </transition>
          </div>

          <!-- marital status -->
          <div class="col-md-3 my-1">
            <label class="block">Marital Status</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-users-alt-4"></i>
              </span>

              <v-select
                id="v-select"
                name="marital status"
                v-model="$v.personal.marital_status.$model"
                :options="['Single','Married', 'Divorced', 'Widowed']"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.marital_status.$error && !$v.personal.marital_status.required"
              >* Marital status is required</div>
            </transition>
          </div>

          <div class="col-md-3 offset-md-2"></div>

          <!-- next of kin full name -->
          <div class="col-md-3 my-1">
            <label class="block">Next of Kin Full Name</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <input
                v-model="$v.personal.next_of_kin.$model"
                name="next_of_kin"
                type="text"
                class="form-control"
                placeholder="Next of Kin Full Name"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.next_of_kin.$error && !$v.personal.next_of_kin.required"
              >* Next of kin is required</div>
            </transition>
          </div>

          <!-- next of kin relationship -->
          <div class="col-md-3 my-1">
            <label class="block">Relationship</label>

            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-users-alt-4"></i>
              </span>
              <v-select
                id="v-select"
                name="next of kin relationship"
                v-model="$v.personal.next_of_kin_relationship.$model"
                :options="Object.values( formData.kinRelations )"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.next_of_kin_relationship.$error && !$v.personal.next_of_kin_relationship.required"
              >* Next of kin relationship is required</div>
            </transition>
          </div>

          <!-- next of kin mobile line -->
          <div class="col-md-3 my-1">
            <label class="block">Next of Kin Mobile (7xxxxxxxx)</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-mobile-phone"></i>
              </span>
              <input
                v-model="$v.personal.next_of_kin_mobile_no.$model"
                name="next_of_kin_mobile_no"
                type="number"
                class="form-control"
                placeholder="Next of Kin Mobile Number"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.next_of_kin_mobile_no.$error && !$v.personal.next_of_kin_mobile_no.required"
              >* Next of kin mobile no is required</div>
            </transition>

            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.next_of_kin_mobile_no.$error && !$v.personal.next_of_kin_mobile_no.minLength"
              >* Mobile no. should be at least 9 digits</div>
            </transition>
          </div>

          <!-- prequalified amount -->
          <div class="col-md-3 my-1">
            <label class="block">Prequalified Amount</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-money"></i>
              </span>
              <v-select
                id="v-select"
                name="prequalified amount"
                v-model="$v.personal.prequalified_amount.$model"
                :options="Object.values( formData.prequalifiedAmount )"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.prequalified_amount.$error && !$v.personal.prequalified_amount.required"
              >* Prequalified amount is required</div>
            </transition>
          </div>

          <!-- alternate mobile line -->
          <div class="col-md-3 my-1">
            <label class="block">Alternate Mobile Line</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-mobile-phone"></i>
              </span>
              <input
                v-model="$v.personal.alternate_mobile_line.$model"
                name="alternate_mobile_line"
                type="number"
                class="form-control"
                placeholder="Alternate Mobile Line"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.alternate_mobile_line.$error && !$v.personal.alternate_mobile_line.required"
              >* Alternate mobile line is required</div>
            </transition>

            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.alternate_mobile_line.$error && !$v.personal.alternate_mobile_line.minLength"
              >* Should be at least 9 digits</div>
            </transition>
          </div>

        </div>
          <!-- referees-->
          <div class="form-group row" v-for="(referee,k) in personal.referees" :key="k">
              <!-- referee full name -->
              <div class="col-md-3 my-1">
                  <label class="block">{{  ordinal_suffix_of(k+1) }} Guarantor Full Name</label>
                  <div class="input-group mb-1">
                      <span class="input-group-addon">
                        <i class="icofont icofont-ui-user"></i>
                      </span>
                      <input
                          v-model="referee.full_name"
                          name="full_name"
                          type="text"
                          class="form-control"
                          placeholder="Referee Full Name"
                      >
                  </div>
                  <transition name="fade">
                      <div
                          class="text-danger"
                          v-show="$v.personal.referees.$each[k].full_name.$error && !$v.personal.referees.$each[k].full_name.required"
                      >* Kindly Fill in the Guarantor's Full Name</div>
                  </transition>
              </div>
              <!-- referee id -->
              <div class="col-md-3 my-1">
                  <label class="block"> {{  ordinal_suffix_of(k+1) }} Guarantor National ID Number</label>
                  <div class="input-group mb-1">
                      <span class="input-group-addon">
                        <i class="icofont icofont-id-card"></i>
                      </span>
                      <input
                          v-model="referee.id_number"
                          name="referee_id_number"
                          type="number"
                          class="form-control"
                          placeholder="Referee ID Number"
                      >
                  </div>
                  <transition name="fade">
                      <div
                          class="text-danger"
                          v-show="$v.personal.referees.$each[k].id_number.$error && !$v.personal.referees.$each[k].id_number.required"
                      >* Kindly Fill in the Guarantor's National ID Number</div>
                  </transition>
              </div>
              <!-- referee mobile line -->
              <div class="col-md-3 my-1">
                  <label class="block">{{  ordinal_suffix_of(k+1) }} Guarantor Mobile (07xxxxxxxx)</label>
                  <div class="input-group mb-1">
                      <span class="input-group-addon">
                        <i class="icofont icofont-mobile-phone"></i>
                      </span>
                      <input
                          v-model="referee.phone_number"
                          name="referee_phone_number"
                          type="tel" pattern="\d{4}\d{3}\d{3}" title="'Phone Number (Format: 0712345678)'"
                          class="form-control"
                          placeholder="Referee Mobile Number"
                      >
                  </div>
                  <transition name="fade">
                      <div
                          class="text-danger"
                          v-show="$v.personal.referees.$each[k].phone_number.$error && !$v.personal.referees.$each[k].phone_number.required"
                      >* Kindly Fill in the Guarantor's Phone Number</div>
                  </transition>
              </div>

              <div class="col-md-3">
                  <span>
                      <button  class="btn btn-sm btn-secondary" @click="addReferee(k)" v-show="k == personal.referees.length -1 && k < 3">Add {{  ordinal_suffix_of(k+2) }} Guarantor </button>
                      <button type="button"  class="btn btn-sm btn-danger"  @click="removeReferee(k)" v-show="k || ( !k && personal.referees.length > 1)">Remove {{  ordinal_suffix_of(k+1) }} Guarantor</button>
                  </span>
              </div>
          </div>
      </div>
    </tab-content>

    <tab-content title="Location" icon="ti-location-pin" :before-change="beforeTabSwitchLocation">
      <div class="content-container">
        <h5 class="my-2 text-primary">Location</h5>
        <div class="form-group row">
          <!-- postal address -->
          <div class="col-md-6 my-1">
            <label class="block">Postal Address</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-envelope-open"></i>
              </span>
              <input
                v-model="$v.location.postal_address.$model"
                name="postal_address"
                type="text"
                class="form-control"
                placeholder="Postal Address"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.postal_address.$error && !$v.location.postal_address.required"
              >* Postal address is required</div>
            </transition>
          </div>

          <!-- postal code -->
          <div class="col-md-3 my-1">
            <label class="block">Postal Code</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-envelope-open"></i>
              </span>
              <input
                v-model="$v.location.postal_code.$model"
                name="postal_code"
                type="number"
                class="form-control"
                placeholder="Postal Code"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.postal_code.$error && !$v.location.postal_code.required"
              >* Postal code is required</div>
            </transition>
          </div>

          <!-- country -->
          <div class="col-md-3 my-1">
            <label class="block">Country</label>

            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <v-select
                id="v-select"
                name="country"
                v-model="$v.location.country.$model"
                :options="['Kenya']"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.country.$error && !$v.location.country.required"
              >* Country is required</div>
            </transition>
          </div>

          <!-- county -->
          <div class="col-md-3 my-1">
            <label class="block">County</label>

            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <v-select
                id="v-select"
                name="county"
                v-model="$v.location.county.$model"
                :options="Object.values( formData.counties )"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.county.$error && !$v.location.county.required"
              >* County is required</div>
            </transition>
          </div>

          <!-- constituency -->
          <div class="col-md-3 my-1">
            <label class="block">Constituency</label>

            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
                <input
                    v-model="$v.location.constituency.$model"
                    name="constituency"
                    type="text"
                    class="form-control"
                    placeholder="Enter Constituency"
                >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.constituency.$error && !$v.location.constituency.required"
              >* Constituency is required</div>
            </transition>
          </div>

          <!-- ward -->
          <div class="col-md-3 my-1">
            <label class="block">Ward</label>

            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
                <input
                    v-model="$v.location.ward.$model"
                    name="ward"
                    type="text"
                    class="form-control"
                    placeholder="Enter Ward"
                >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.ward.$error && !$v.location.ward.required"
              >* Ward is required</div>
            </transition>
          </div>

          <!-- physical address -->
          <div class="col-md-6 my-1">
            <label class="block">Home Physical Address</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-location-pin"></i>
              </span>
              <input id="physical_address"
                v-model="$v.location.physical_address.$model"
                name="physical_address"
                type="text"
                class="form-control"
                placeholder="Home Physical Address"
              >
                <input type="hidden" name="latitude" id="latitude" v-model="$v.location.latitude.$model">
                <input type="hidden" name="longitude" id="longitude" v-model="$v.location.longitude.$model">
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.physical_address.$error && !$v.location.physical_address.required"
              >* Physical address is required</div>
            </transition>
          </div>

          <!-- residence type -->
          <div class="col-md-3 my-1">
            <label class="block">Residence type</label>

            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-home"></i>
              </span>
              <v-select
                id="v-select"
                name="residence_type"
                v-model="$v.location.residence_type.$model"
                :options="['Rented', 'Owned']"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.residence_type.$error && !$v.location.residence_type.required"
              >* Residence type is required</div>
            </transition>
          </div>

          <!-- years lived at residence -->
          <div class="col-md-3 my-1">
            <label class="block">Years Lived At Residence</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-calendar"></i>
              </span>
              <input
                v-model="$v.location.years_lived_at_residence.$model"
                name="years_lived_at_residence"
                type="number"
                class="form-control"
                placeholder="0"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.years_lived_at_residence.$error && !$v.location.years_lived_at_residence.required"
              >* Years lived at residence is required</div>
            </transition>
          </div>

        <!-- business address -->
        <div class="col-md-6 my-1" >
            <label class="block">Business Physical Address</label>
            <div class="input-group mb-1">
          <span class="input-group-addon">
            <i class="icofont icofont-location-pin"></i>
          </span>
                <input id="business_physical_address"
                       v-model="$v.location.business_address.$model"
                       name="business_address"
                       type="text"
                       class="form-control"
                       placeholder
                >
                <input type="hidden" name="business_latitude" id="business_latitude" v-model="$v.location.business_latitude.$model">
                <input type="hidden" name="business_longitude" id="business_longitude" v-model="$v.location.business_longitude.$model">
            </div>
            <transition name="fade">
                <div
                    class="text-danger"
                    v-show="$v.location.business_address.$error && !$v.location.business_address.required"
                >* Business Physical Address is required</div>
            </transition>
        </div>

          <!-- home coordinates-->
          <div class="col-md-3 my-1" hidden>
            <label class="block">Home Coordinates</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-location-pin"></i>
              </span>
              <input
                v-model="$v.location.home_coordinates.$model"
                name="home_coordinates"
                type="text"
                class="form-control"
                placeholder
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.home_coordinates.$error && !$v.location.home_coordinates.required"
              >* Home coordinates is required</div>
            </transition>
          </div>

          <!-- business coordinates-->
          <div class="col-md-3 my-1" hidden>
            <label class="block">Business Coordinates</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-location-pin"></i>
              </span>
              <input
                v-model="$v.location.business_coordinates.$model"
                name="business_coordinates"
                type="text"
                class="form-control"
                placeholder
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.business_coordinates.$error && !$v.location.business_coordinates.required"
              >* Business coordinates is required</div>
            </transition>
          </div>
        </div>
      </div>
    </tab-content>

    <tab-content
      title="Profession/Industry"
      icon="ti-briefcase"
      :before-change="beforeTabSwitchProfession"
    >
      <div class="content-container">
        <h5 class="my-2 text-primary">Profession/Industry</h5>
        <div class="form-group row">
          <template v-if="!$v.profession.is_employed.$model">
            <div class="col-md-4 my-1">
              <label class="block">Industry Type</label>
              <div class="input-group mb-1">
                <span class="input-group-addon">
                  <i class="icofont icofont-ui-office"></i>
                </span>

                <v-select
                  id="v-select"
                  name="industry_type"
                  v-model="$v.profession.industry_type.$model"
                  :options="Object.values( formData.industries )"
                ></v-select>
              </div>
              <transition name="fade">
                <div
                  class="text-danger"
                  v-show="$v.profession.industry_type.$error && !$v.profession.industry_type.required"
                >* Industry type is required</div>
              </transition>
            </div>

            <div class="col-md-4 my-1">
              <label class="block">Business Type</label>
              <div class="input-group mb-1">
                <span class="input-group-addon">
                  <i class="icofont icofont-ui-office"></i>
                </span>

                <v-select
                  id="v-select"
                  name="business_type"
                  v-model="$v.profession.business_type.$model"
                  :options="Object.values( formData.businessTypes )"
                ></v-select>
              </div>
              <transition name="fade">
                <div
                  class="text-danger"
                  v-show="$v.profession.business_type.$error && !$v.profession.business_type.required"
                >* Business type is required</div>
              </transition>
            </div>

            <div class="col-md-3 my-1">
              <label class="block">Income Range</label>
              <div class="input-group mb-1">
                <span class="input-group-addon">
                  <i class="icofont icofont-id"></i>
                </span>

                <v-select
                  id="v-select"
                  name="income_range"
                  v-model="$v.profession.income_range.$model"
                  :options="Object.values( formData.incomeRanges )"
                ></v-select>
              </div>
              <transition name="fade">
                <div
                  class="text-danger"
                  v-show="$v.profession.income_range.$error && !$v.profession.income_range.required"
                >* Income range is required</div>
              </transition>
            </div>
          </template>

          <div class="col-md-12">
            <div class="bag py-3">
              <input
                style="cursor:pointer"
                v-model="$v.profession.is_employed.$model"
                name="is_employed"
                type="checkbox"
                id="is_employed"
              >
              <label style="cursor:pointer" for="is_employed" class="inline-block mx-1">Is employed?</label>
            </div>
          </div>

          <template v-if="$v.profession.is_employed.$model">
            <div class="col-md-3 my-1">
              <label class="block">Employment Status</label>
              <div class="input-group mb-1">
                <span class="input-group-addon">
                  <i class="icofont icofont-ui-user"></i>
                </span>

                <v-select
                  id="v-select"
                  name="employment_status"
                  v-model="$v.profession.employment_status.$model"
                  :options="['Employed', 'Self employed']"
                ></v-select>
              </div>
              <transition name="fade">
                <div
                  class="text-danger"
                  v-show="$v.profession.employment_status.$error && !$v.profession.employment_status.required"
                >* Employment status is required</div>
              </transition>
            </div>

            <div class="col-md-3 my-1">
              <label class="block">Employer</label>
              <div class="input-group mb-1">
                <span class="input-group-addon">
                  <i class="icofont icofont-ui-user"></i>
                </span>
                <input
                  v-model="$v.profession.employer.$model"
                  name="employer"
                  class="form-control"
                  type="text"
                  placeholder="Employer"
                >
              </div>
              <transition name="fade">
                <div
                  class="text-danger"
                  v-show="$v.profession.employer.$error && !$v.profession.employer.required"
                >* Employer is required</div>
              </transition>
            </div>

            <div class="col-md-3 my-1">
              <label class="block">Date of Employment</label>
              <div class="input-group mb-1">
                <span class="input-group-addon">
                  <i class="icofont icofont-ui-user"></i>
                </span>

                 <VueCtkDateTimePicker formatted="YYYY-MM-DD" :noButtonNow="true" :onlyDate="true" inputSize="sm" color="#8EC63E" :right="true" :noHeader="true" format='YYYY-MM-DD' outputFormat='YYYY-MM-DD' v-model="$v.profession.date_of_employment.$model" name="date_of_employment" />
              </div>
              <transition name="fade">
                <div
                  class="text-danger"
                  v-show="$v.profession.date_of_employment.$error && !$v.profession.date_of_employment.required"
                >* Date of employment is required</div>
              </transition>
            </div>

            <div class="col-md-3 my-1">
              <label class="block">Income Range</label>
              <div class="input-group mb-1">
                <span class="input-group-addon">
                  <i class="icofont icofont-id"></i>
                </span>

                <v-select
                  id="v-select"
                  name="income_range"
                  v-model="$v.profession.income_range.$model"
                  :options="Object.values( formData.incomeRanges )"
                ></v-select>
              </div>
              <transition name="fade">
                <div
                  class="text-danger"
                  v-show="$v.profession.income_range.$error && !$v.profession.income_range.required"
                >* Income range is required</div>
              </transition>
            </div>
          </template>
        </div>
      </div>
    </tab-content>

    <tab-content title="Account details" icon="ti-money" :before-change="beforeTabSwitchAccount">
      <div class="content-container">
        <h5 class="my-2 text-primary">Account Details</h5>
        <div class="form-group row">
          <div class="col-md-3">
            <label class="block">Savings product</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
               <v-select
                  id="v-select"
                  name="savings_product"
                  v-model="$v.account.savings_product.$model"
                  :options="Object.values( formData.accounts )"
                ></v-select>
            </div>
          </div>
        </div>
      </div>
      <div class="content-container">
        <h5 class="my-2 text-primary">Loan Details</h5>
        <div class="form-group row">
            <div class="col-md-3">
                <label class="block">Loan Product</label>
                <div class="input-group mb-1">
                    <span class="input-group-addon">
                        <i class="icofont icofont-ui-user"></i>
                    </span>
                    <v-select
                        id="v-select"
                        name="product_id"
                        v-model="loan.product_id"
                        :options="Object.values( formData.loanProducts )"
                    ></v-select>
                </div>
            </div>
            <div class="col-md-3">
                <label class="block">Loan Repayment Type</label>
                <div class="input-group mb-1">
                    <span class="input-group-addon">
                        <i class="icofont icofont-ui-user"></i>
                    </span>
                    <v-select
                        id="v-select"
                        name="loan_type"
                        v-model="loan.loan_type"
                        :options="Object.values( formData.loanTypes )"
                    ></v-select>
                </div>
            </div>
            <div class="col-md-3 my-1">
                <label class="block">Negotiated Installments</label>
                <div class="input-group mb-1">
                    <span class="input-group-addon">
                    <i class="icofont icofont-ui-user"></i>
                    </span>
                    <input
                        v-model="loan.installments"
                        name="installments"
                        class="form-control"
                        type="text"
                        placeholder="Installments"
                        readonly
                    >
                </div>
            </div>
            <div class="col-md-3 my-1">
                <label class="block">Applied Loan Amount</label>
                <div class="input-group mb-1">
                    <span class="input-group-addon"><i class="icofont icofont-money"></i></span>
                    <input
                        v-model="loan.loan_amount"
                        name="loan_amount"
                        class="form-control"
                        type="number"
                        placeholder="Loan Amount"
                    >
                </div>
            </div>
            <div class="col-md-3">
                <label class="block">Loan Purpose</label>
                <div class="input-group mb-1">
                    <span class="input-group-addon"><i
                        class="icofont icofont-location-pin"></i>
                    </span>
                <v-select
                    id="v-select"
                    name="product_id"
                    v-model="loan.purpose"
                    :options="Object.values( formData.loanPurposeType )"
                    ></v-select>
                </div>
            </div>
        </div>
      </div>
    </tab-content>

    <tab-content title="Confirm and Submit" icon="ti-check">
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

              <v-select
                id="v-select"
                name="customer type"
                v-model.trim="$v.personal.customer_type.$model"
                :options="['Individual','Company']"
              ></v-select>
            </div>

            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.customer_type.$error && !$v.personal.customer_type.required"
              >* Customer type is required</div>
            </transition>
          </div>

          <!-- title -->
          <div class="col-md-3 my-1">
            <label class="block">Title</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>

              <v-select
                id="v-select"
                name="customer title"
                v-model="$v.personal.customer_title.$model"
                :options="['Mr','Mrs']"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.customer_title.$error && !$v.personal.customer_title.required"
              >* Customer title is required</div>
            </transition>
          </div>

          <!-- first name -->
          <div class="col-md-3 my-1">
            <label class="block">First Name</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <input
                v-model="$v.personal.first_name.$model"
                name="first_name"
                type="text"
                class="form-control"
                placeholder="First Name"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.first_name.$error && !$v.personal.first_name.required"
              >* First name is required</div>
            </transition>
          </div>

          <!-- middle name -->
          <div class="col-md-3 my-1">
            <label class="block">Middle Name</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <input
                v-model="$v.personal.middle_name.$model"
                name="middle_name"
                type="text"
                class="form-control"
                placeholder="Middle Name"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.middle_name.$error && !$v.personal.middle_name.required"
              >* Middle name is required</div>
            </transition>
          </div>

          <!-- last name -->
          <div class="col-md-3 my-1">
            <label class="block">Last Name</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <input
                v-model="$v.personal.last_name.$model"
                name="last_name"
                type="text"
                class="form-control"
                placeholder="Last Name"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.last_name.$error && !$v.personal.last_name.required"
              >* Last name is required</div>
            </transition>
          </div>

          <!-- relationship officer -->
          <div class="col-md-3 my-1">
            <label class="block">Relationship Officer</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <v-select
                id="v-select"
                name="$v.personal.relationship officer.$model"
                v-model="$v.personal.relationship_officer.$model"
                :options="Object.values( formData.relationshipOfficers )"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.relationship_officer.$error && !$v.personal.relationship_officer.required"
              >* Relationship officer is required</div>
            </transition>
          </div>

          <!-- tax pin -->
          <div class="col-md-3 my-1">
            <label class="block">Tax PIN</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-lock"></i>
              </span>
              <input
                v-model="$v.personal.tax_pin.$model"
                name="tax_pin"
                type="text"
                class="form-control"
                placeholder="Tax PIN"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.tax_pin.$error && !$v.personal.tax_pin.required"
              >* Tax PIN is required</div>
            </transition>
          </div>

          <!-- gender -->
          <div class="col-md-3 my-1">
            <label class="block">Gender</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <v-select
                id="v-select"
                name="gender"
                v-model="$v.personal.gender.$model"
                :options="['Male','Female']"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.gender.$error && !$v.personal.gender.required"
              >* Gender is required</div>
            </transition>
          </div>

          <!-- date of birth -->
          <div class="col-md-3 my-1">
            <label class="block">Date of Birth</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>

               <VueCtkDateTimePicker formatted="YYYY-MM-DD" :noButtonNow="true" :onlyDate="true" inputSize="sm" color="#8EC63E" :right="true" :noHeader="true" :maxDate="this.eighteenYearsAgo.format('YYYY-MM-DD')" format='YYYY-MM-DD' outputFormat='YYYY-MM-DD' v-model="$v.personal.date_of_birth.$model" name="date_of_birth" />

              <!-- <datepicker v-model="$v.personal.date_of_birth.$model" name="date_of_birth"></datepicker> -->
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.date_of_birth.$error && !$v.personal.date_of_birth.required"
              >* Date of birth is required</div>
            </transition>
          </div>

          <!-- mobile line -->
          <div class="col-md-3 my-1">
            <label class="block">Mobile Line (7xxxxxxxx)</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-mobile-phone"></i>
              </span>
              <input
                v-model="$v.personal.mobile_line.$model"
                name="mobile_line"
                type="tel"
                class="form-control"
                placeholder="Mobile phone number"
              >
            </div>
            <transition name="fade">
                <div>
                    <div
                        class="text-danger"
                        v-show="$v.personal.mobile_line.$error && !$v.personal.mobile_line.required"
                    >* Mobile line is required</div>

                    <div
                        class="text-danger"
                        v-show="$v.personal.mobile_line.$error && !$v.personal.mobile_line.uniquePhoneNumber"
                    >* Mobile line is already registered</div>

                    <div
                        class="text-danger"
                        v-show="$v.personal.mobile_line.$error && !$v.personal.mobile_line.minLength"
                    >* Mobile line should be at least 9 digits</div>
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
              <input
                v-model="$v.personal.email.$model"
                name="email"
                type="email"
                class="form-control"
                placeholder="Email Address"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.email.$error && !$v.personal.email.required"
              >* Email is required</div>
            </transition>

            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.email.$error && !$v.personal.email.email"
              >* Should be a valid email</div>
            </transition>
          </div>

          <!-- identity type -->
          <div class="col-md-3 my-1">
            <label class="block">Identity Type</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-id"></i>
              </span>
              <v-select
                id="v-select"
                name="identity type"
                v-model="$v.personal.identity_type.$model"
                :options="Object.values( formData.idTypes )"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.identity_type.$error && !$v.personal.identity_type.required"
              >* ID type is required</div>
            </transition>
          </div>

          <!-- identity number -->
          <div class="col-md-3 my-1">
            <label class="block">Identity Number</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-id"></i>
              </span>
              <input
                v-model="$v.personal.identity_number.$model"
                name="identity_number"
                type="text"
                class="form-control"
                placeholder="ID Number"
              >
            </div>
            <transition name="fade">
                <div>
                    <div
                        class="text-danger"
                        v-show="$v.personal.identity_number.$error && !$v.personal.identity_number.required"
                    >* ID number is required</div>

                    <div
                        class="text-danger"
                        v-show="$v.personal.identity_number.$error && !$v.personal.identity_number.uniqueIdNumber"
                    >* ID number is already registered</div>
                </div>
            </transition>
          </div>

          <!-- marital status -->
          <div class="col-md-3 my-1">
            <label class="block">Marital Status</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-users-alt-4"></i>
              </span>

              <v-select
                id="v-select"
                name="marital status"
                v-model="$v.personal.marital_status.$model"
                :options="['Single','Married', 'Divorced', 'Widowed']"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.marital_status.$error && !$v.personal.marital_status.required"
              >* Marital status is required</div>
            </transition>
          </div>

          <div class="col-md-3 offset-md-2"></div>

          <!-- next of kin full name -->
          <div class="col-md-3 my-1">
            <label class="block">Next of Kin Full Name</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <input
                v-model="$v.personal.next_of_kin.$model"
                name="next_of_kin"
                type="text"
                class="form-control"
                placeholder="Next of Kin Full Name"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.next_of_kin.$error && !$v.personal.next_of_kin.required"
              >* Next of kin is required</div>
            </transition>
          </div>

          <!-- next of kin relationship -->
          <div class="col-md-3 my-1">
            <label class="block">Relationship</label>

            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-users-alt-4"></i>
              </span>
              <v-select
                id="v-select"
                name="next of kin relationship"
                v-model="$v.personal.next_of_kin_relationship.$model"
                :options="Object.values( formData.kinRelations )"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.next_of_kin_relationship.$error && !$v.personal.next_of_kin_relationship.required"
              >* Next of kin relationship is required</div>
            </transition>
          </div>

          <!-- next of kin mobile line -->
          <div class="col-md-3 my-1">
            <label class="block">Next of Kin Mobile (7xxxxxxxx)</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-mobile-phone"></i>
              </span>
              <input
                v-model="$v.personal.next_of_kin_mobile_no.$model"
                name="next_of_kin_mobile_no"
                type="number"
                class="form-control"
                placeholder="Next of Kin Mobile Number"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.next_of_kin_mobile_no.$error && !$v.personal.next_of_kin_mobile_no.required"
              >* Next of kin mobile no is required</div>
            </transition>

            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.next_of_kin_mobile_no.$error && !$v.personal.next_of_kin_mobile_no.minLength"
              >* Mobile no. should be at least 9 digits</div>
            </transition>
          </div>

          <!-- prequalified amount -->
          <div class="col-md-3 my-1">
            <label class="block">Prequalified Amount</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-money"></i>
              </span>
              <v-select
                id="v-select"
                name="prequalified amount"
                v-model="$v.personal.prequalified_amount.$model"
                :options="Object.values( formData.prequalifiedAmount )"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.prequalified_amount.$error && !$v.personal.prequalified_amount.required"
              >* Prequalified amount is required</div>
            </transition>
          </div>

          <!-- alternate mobile line -->
          <div class="col-md-3 my-1">
            <label class="block">Alternate Mobile Line</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-mobile-phone"></i>
              </span>
              <input
                v-model="$v.personal.alternate_mobile_line.$model"
                name="alternate_mobile_line"
                type="number"
                class="form-control"
                placeholder="Alternate Mobile Line"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.alternate_mobile_line.$error && !$v.personal.alternate_mobile_line.required"
              >* Alternate mobile line is required</div>
            </transition>

            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.personal.alternate_mobile_line.$error && !$v.personal.alternate_mobile_line.minLength"
              >* Should be at least 9 digits</div>
            </transition>
          </div>
        </div>
          <!-- referees-->
          <div class="form-group row" v-for="(referee,k) in personal.referees" :key="k">
              <!-- referee full name -->
              <div class="col-md-3 my-1">
                  <label class="block">{{  ordinal_suffix_of(k+1) }} Referee Full Name</label>
                  <div class="input-group mb-1">
                      <span class="input-group-addon">
                        <i class="icofont icofont-ui-user"></i>
                      </span>
                      <input
                          v-model="referee.full_name"
                          name="full_name"
                          type="text"
                          class="form-control"
                          placeholder="Referee Full Name"
                      >
                  </div>
                  <transition name="fade">
                      <div
                          class="text-danger"
                          v-show="$v.personal.referees.$each[k].full_name.$error && !$v.personal.referees.$each[k].full_name.required"
                      >* Kindly Fill in the Referee's Full Name</div>
                  </transition>
              </div>
              <!-- referee id -->
              <div class="col-md-3 my-1">
                  <label class="block"> {{  ordinal_suffix_of(k+1) }} Referee National ID Number</label>
                  <div class="input-group mb-1">
                      <span class="input-group-addon">
                        <i class="icofont icofont-id-card"></i>
                      </span>
                      <input
                          v-model="referee.id_number"
                          name="referee_id_number"
                          type="number"
                          class="form-control"
                          placeholder="Referee ID Number"
                      >
                  </div>
                  <transition name="fade">
                      <div
                          class="text-danger"
                          v-show="$v.personal.referees.$each[k].id_number.$error && !$v.personal.referees.$each[k].id_number.required"
                      >* Kindly Fill in the Referee's National ID Number</div>
                  </transition>
              </div>
              <!-- referee mobile line -->
              <div class="col-md-3 my-1">
                  <label class="block">{{  ordinal_suffix_of(k+1) }} Referee Mobile (07xxxxxxxx)</label>
                  <div class="input-group mb-1">
                      <span class="input-group-addon">
                        <i class="icofont icofont-mobile-phone"></i>
                      </span>
                      <input
                          v-model="referee.phone_number"
                          name="referee_phone_number"
                          type="tel" pattern="\d{4}\d{3}\d{3}" title="'Phone Number (Format: 0712345678)'"
                          class="form-control"
                          placeholder="Referee Mobile Number"
                      >
                  </div>
                  <transition name="fade">
                      <div
                          class="text-danger"
                          v-show="$v.personal.referees.$each[k].phone_number.$error && !$v.personal.referees.$each[k].phone_number.required"
                      >* Kindly Fill in the Referee's Phone Number</div>
                  </transition>
              </div>

              <div class="col-md-3">
                  <span>
                      <button  class="btn btn-sm btn-secondary" @click="addReferee(k)" v-show="k == personal.referees.length -1 && k < 3">Add {{  ordinal_suffix_of(k+2) }} Referee </button>
                      <button type="button"  class="btn btn-sm btn-danger"  @click="removeReferee(k)" v-show="k || ( !k && personal.referees.length > 1)">Remove {{  ordinal_suffix_of(k+1) }} Referee</button>
                  </span>
              </div>
          </div>
      </div>

      <!-- Location -->
      <div class="content-container">
        <h5 class="my-2 text-primary">Location</h5>
        <div class="form-group row">
          <!-- postal address -->
          <div class="col-md-6 my-1">
            <label class="block">Postal Address</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-envelope-open"></i>
              </span>
              <input
                v-model="$v.location.postal_address.$model"
                name="postal_address"
                type="text"
                class="form-control"
                placeholder="Postal Address"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.postal_address.$error && !$v.location.postal_address.required"
              >* Postal address is required</div>
            </transition>
          </div>

          <!-- postal code -->
          <div class="col-md-3 my-1">
            <label class="block">Postal Code</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-envelope-open"></i>
              </span>
              <input
                v-model="$v.location.postal_code.$model"
                name="postal_code"
                type="number"
                class="form-control"
                placeholder="Postal Code"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.postal_code.$error && !$v.location.postal_code.required"
              >* Postal code is required</div>
            </transition>
          </div>

          <!-- country -->
          <div class="col-md-3 my-1">
            <label class="block">Country</label>

            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <v-select
                id="v-select"
                name="country"
                v-model="$v.location.country.$model"
                :options="['Kenya']"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.country.$error && !$v.location.country.required"
              >* Country is required</div>
            </transition>
          </div>

          <!-- county -->
          <div class="col-md-3 my-1">
            <label class="block">County</label>

            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
              <v-select
                id="v-select"
                name="county"
                v-model="$v.location.county.$model"
                :options="Object.values( formData.counties )"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.county.$error && !$v.location.county.required"
              >* County is required</div>
            </transition>
          </div>

          <!-- constituency -->
          <div class="col-md-3 my-1">
            <label class="block">Constituency</label>

            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
                <input
                    v-model="$v.location.constituency.$model"
                    name="constituency"
                    type="text"
                    class="form-control"
                    placeholder="Enter Constituency"
                >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.constituency.$error && !$v.location.constituency.required"
              >* Constituency is required</div>
            </transition>
          </div>

          <!-- ward -->
          <div class="col-md-3 my-1">
            <label class="block">Ward</label>

            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>
                <input
                    v-model="$v.location.ward.$model"
                    name="ward"
                    type="text"
                    class="form-control"
                    placeholder="Enter Ward"
                >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.ward.$error && !$v.location.ward.required"
              >* Ward is required</div>
            </transition>
          </div>

          <!-- physical address -->
          <div class="col-md-6 my-1">
            <label class="block">Physical Address</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-location-pin"></i>
              </span>
              <input id="physical_address_confirm"
                v-model="$v.location.physical_address.$model"
                name="physical_address"
                type="text"
                class="form-control"
                placeholder="Physical Address"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.physical_address.$error && !$v.location.physical_address.required"
              >* Physical address is required</div>
            </transition>
          </div>

          <!-- residence type -->
          <div class="col-md-3 my-1">
            <label class="block">Residence type</label>

            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-home"></i>
              </span>
              <v-select
                id="v-select"
                name="residence_type"
                v-model="$v.location.residence_type.$model"
                :options="['Rented', 'Owned']"
              ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.residence_type.$error && !$v.location.residence_type.required"
              >* Residence type is required</div>
            </transition>
          </div>

          <!-- years lived at residence -->
          <div class="col-md-3 my-1">
            <label class="block">Years Lived At Residence</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-calendar"></i>
              </span>
              <input
                v-model="$v.location.years_lived_at_residence.$model"
                name="years_lived_at_residence"
                type="number"
                class="form-control"
                placeholder="0"
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.years_lived_at_residence.$error && !$v.location.years_lived_at_residence.required"
              >* Years lived at residence is required</div>
            </transition>
          </div>

            <!-- business address-->
            <div class="col-md-6 my-1" >
                <label class="block">Business Physical Address</label>
                <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-location-pin"></i>
              </span>
                <input id="business_physical_address_confirm"
                       v-model="$v.location.business_address.$model"
                       name="business_address"
                       type="text"
                       class="form-control"
                       placeholder
                >
                </div>
                <transition name="fade">
                    <div
                        class="text-danger"
                        v-show="$v.location.business_address.$error && !$v.location.business_address.required"
                    >* Business coordinates is required</div>
                </transition>
            </div>

          <!-- home coordinates-->
          <div class="col-md-3 my-1" hidden>
            <label class="block">Home Coordinates</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-location-pin"></i>
              </span>
              <input
                v-model="$v.location.home_coordinates.$model"
                name="home_coordinates"
                type="text"
                class="form-control"
                placeholder
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.home_coordinates.$error && !$v.location.home_coordinates.required"
              >* Home coordinates is required</div>
            </transition>
          </div>

          <!-- business coordinates-->
          <div class="col-md-3 my-1" hidden>
            <label class="block">Business Coordinates</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-location-pin"></i>
              </span>
              <input
                v-model="$v.location.business_coordinates.$model"
                name="business_coordinates"
                type="text"
                class="form-control"
                placeholder
              >
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.location.business_coordinates.$error && !$v.location.business_coordinates.required"
              >* Business coordinates is required</div>
            </transition>
          </div>
        </div>
      </div>

      <!-- Profession -->
       <div class="content-container">
        <h5 class="my-2 text-primary">Profession/Industry</h5>
        <div class="form-group row">
          <template v-if="!$v.profession.is_employed.$model">
            <!-- industry type -->
            <div class="col-md-4 my-1">
              <label class="block">Industry Type</label>
              <div class="input-group mb-1">
                <span class="input-group-addon">
                  <i class="icofont icofont-ui-office"></i>
                </span>

                <v-select
                  id="v-select"
                  name="industry_type"
                  v-model="$v.profession.industry_type.$model"
                  :options="Object.values( formData.industries )"
                ></v-select>
              </div>
              <transition name="fade">
                <div
                  class="text-danger"
                  v-show="$v.profession.industry_type.$error && !$v.profession.industry_type.required"
                >* Industry type is required</div>
              </transition>
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
                  v-model="$v.profession.business_type.$model"
                  :options="Object.values( formData.businessTypes )"
                ></v-select>
              </div>
              <transition name="fade">
                <div
                  class="text-danger"
                  v-show="$v.profession.business_type.$error && !$v.profession.business_type.required"
                >* Business type is required</div>
              </transition>
            </div>

            <!-- income range -->
            <div class="col-md-3 my-1">
              <label class="block">Income Range</label>
              <div class="input-group mb-1">
                <span class="input-group-addon">
                  <i class="icofont icofont-id"></i>
                </span>

                <v-select
                  id="v-select"
                  name="income_range"
                  v-model="$v.profession.income_range.$model"
                  :options="Object.values( formData.incomeRanges )"
                ></v-select>
              </div>
              <transition name="fade">
                <div
                  class="text-danger"
                  v-show="$v.profession.income_range.$error && !$v.profession.income_range.required"
                >* Income range is required</div>
              </transition>
            </div>
          </template>

          <!-- is employed -->
          <div class="col-md-12">
            <div class="bag py-3">
              <input
                style="cursor:pointer"
                v-model="$v.profession.is_employed.$model"
                name="is_employed"
                type="checkbox"
                id="is_employed"
              >
              <label style="cursor:pointer" for="is_employed" class="inline-block mx-1">Is employed?</label>
            </div>
          </div>

          <template v-if="$v.profession.is_employed.$model">
            <!-- employment status -->
            <div class="col-md-3 my-1">
              <label class="block">Employment Status</label>
              <div class="input-group mb-1">
                <span class="input-group-addon">
                  <i class="icofont icofont-ui-user"></i>
                </span>

                <v-select
                  id="v-select"
                  name="employment_status"
                  v-model="$v.profession.employment_status.$model"
                  :options="['Employed', 'Self employed']"
                ></v-select>
              </div>
              <transition name="fade">
                <div
                  class="text-danger"
                  v-show="$v.profession.employment_status.$error && !$v.profession.employment_status.required"
                >* Employment status is required</div>
              </transition>
            </div>

            <!-- employer -->
            <div class="col-md-3 my-1">
              <label class="block">Employer</label>
              <div class="input-group mb-1">
                <span class="input-group-addon">
                  <i class="icofont icofont-ui-user"></i>
                </span>
                <input
                  v-model="$v.profession.employer.$model"
                  name="employer"
                  class="form-control"
                  type="text"
                  placeholder="Employer"
                >
              </div>
              <transition name="fade">
                <div
                  class="text-danger"
                  v-show="$v.profession.employer.$error && !$v.profession.employer.required"
                >* Employer is required</div>
              </transition>
            </div>

            <!-- date of employment -->
            <div class="col-md-3 my-1">
              <label class="block">Date of Employment</label>
              <div class="input-group mb-1">
                <span class="input-group-addon">
                  <i class="icofont icofont-ui-user"></i>
                </span>

                 <VueCtkDateTimePicker formatted="YYYY-MM-DD" :noButtonNow="true" :onlyDate="true" inputSize="sm" color="#8EC63E" :right="true" :noHeader="true" format='YYYY-MM-DD' outputFormat='YYYY-MM-DD' v-model="$v.profession.date_of_employment.$model" name="date_of_employment" />

                <!-- <datepicker v-model="$v.profession.date_of_employment.$model" name="date_of_employment"></datepicker> -->
              </div>
              <transition name="fade">
                <div
                  class="text-danger"
                  v-show="$v.profession.date_of_employment.$error && !$v.profession.date_of_employment.required"
                >* Date of employment is required</div>
              </transition>
            </div>

            <!-- income range -->
            <div class="col-md-3 my-1">
              <label class="block">Income Range</label>
              <div class="input-group mb-1">
                <span class="input-group-addon">
                  <i class="icofont icofont-id"></i>
                </span>

                <v-select
                  id="v-select"
                  name="income_range"
                  v-model="$v.profession.income_range.$model"
                  :options="Object.values( formData.incomeRanges )"
                ></v-select>
              </div>
              <transition name="fade">
                <div
                  class="text-danger"
                  v-show="$v.profession.income_range.$error && !$v.profession.income_range.required"
                >* Income range is required</div>
              </transition>
            </div>
          </template>
        </div>
      </div>

      <!-- Account -->
      <div class="content-container">
        <h5 class="my-2 text-primary">Account Details</h5>
        <div class="form-group row">
          <!-- default savings product -->
          <div class="col-md-3">
            <label class="block">Savings product</label>
            <div class="input-group mb-1">
              <span class="input-group-addon">
                <i class="icofont icofont-ui-user"></i>
              </span>

               <v-select
                  id="v-select"
                  name="savings_product"
                  v-model="$v.account.savings_product.$model"
                  :options="Object.values( formData.accounts )"
                ></v-select>
            </div>
            <transition name="fade">
              <div
                class="text-danger"
                v-show="$v.account.savings_product.$error && !$v.account.savings_product.required"
              >* Savings product is required</div>
            </transition>
          </div>
        </div>
      </div>
      <div class="content-container">
        <h5 class="my-2 text-primary">Loan Details</h5>
        <div class="form-group row">
            <div class="col-md-3">
                <label class="block">Loan Product</label>
                <div class="input-group mb-1">
                    <span class="input-group-addon">
                        <i class="icofont icofont-ui-user"></i>
                    </span>
                    <v-select
                        id="v-select"
                        name="product_id"
                        v-model="loan.product_id"
                        :options="Object.values( formData.loanProducts )"
                    ></v-select>
                </div>
            </div>
            <div class="col-md-3">
                <label class="block">Loan Repayment Type</label>
                <div class="input-group mb-1">
                    <span class="input-group-addon">
                        <i class="icofont icofont-ui-user"></i>
                    </span>
                    <v-select
                        id="v-select"
                        name="loan_type"
                        v-model="loan.loan_type"
                        :options="Object.values( formData.loanTypes )"
                    ></v-select>
                </div>
            </div>
            <div class="col-md-3 my-1">
                <label class="block">Negotiated Installments</label>
                <div class="input-group mb-1">
                    <span class="input-group-addon">
                    <i class="icofont icofont-ui-user"></i>
                    </span>
                    <input
                        v-model="loan.installments"
                        name="installments"
                        class="form-control"
                        type="text"
                        placeholder="Installments"
                        readonly
                    >
                </div>
            </div>
            <div class="col-md-3 my-1">
                <label class="block">Applied Loan Amount</label>
                <div class="input-group mb-1">
                    <span class="input-group-addon"><i class="icofont icofont-money"></i></span>
                    <input
                        v-model="loan.loan_amount"
                        name="loan_amount"
                        class="form-control"
                        type="number"
                        placeholder="Loan Amount"
                    >
                </div>
            </div>
            <div class="col-md-3">
                <label class="block">Loan Purpose</label>
                <div class="input-group mb-1">
                    <span class="input-group-addon"><i
                        class="icofont icofont-location-pin"></i>
                    </span>
                <v-select
                    id="v-select"
                    name="product_id"
                    v-model="loan.purpose"
                    :options="Object.values( formData.loanPurposeType )"
                    ></v-select>
                </div>
            </div>
        </div>
      </div>
    </tab-content>

    <button slot="prev" class="btn btn-primary">Back</button>
    <button slot="next" class="btn btn-primary">Next</button>
    <button slot="finish" :disabled="completed" class="btn btn-primary">Submit</button>

  </form-wizard>
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

import { required, requiredIf, minLength, email, alphaNum, integer } from "vuelidate/lib/validators";

export default {
  name: "RegisterComponent",

  components: {
    FormWizard,
    TabContent,
    VueCtkDateTimePicker,
    Datepicker
  },

  data() {
    return {
      rootUrl: "/",
      completed : false,
      eighteenYearsAgo : moment().subtract(18, 'years'),
      personal: {
        customer_type: "",
        customer_title: "",
        first_name: "",
        middle_name: "",
        last_name: "",
        relationship_officer: "",
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
        //guarantor: "",
        prequalified_amount: "",
        alternate_mobile_line: "",
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
        physical_address: "",
        latitude: "",
        longitude: "",
        business_address: "",
        business_latitude: "",
        business_longitude: "",
        residence_type: "",
        years_lived_at_residence: "",
        home_coordinates: "",
        business_coordinates: ""
      },

      profession: {
        industry_type: "",
        business_type: "",
        is_employed: "",
        employment_status: "",
        employer: "",
        date_of_employment: "",
        income_range: ""
      },

      account: {
        savings_product: ""
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
      middle_name: {  },
      last_name: { required },
      relationship_officer: { required },
      tax_pin: { },
      gender: { required },
      date_of_birth: { required },
      mobile_line: { required, minLength: minLength(9), uniquePhoneNumber(phone_number) {
              if (phone_number === '') return true;
              let country_code = "254";
              let phone_country_code = country_code + phone_number.slice(-9);
              return axios.get(`${this.rootUrl}registry-data/unique-phone-number/${phone_country_code}`)
                  .then(res => {
                      return res.data
                  })
          }},
      email: { },
      identity_type: { required },
      identity_number: { required, alphaNum, uniqueIdNumber(id_number) {
              if (id_number === '') return true
              return axios.get(`${this.rootUrl}registry-data/unique-id-number/${id_number}`)
                  .then(res => {
                      return res.data
                  })
          }},
      marital_status: { required },
      next_of_kin: { required },
      next_of_kin_relationship: { required },
      next_of_kin_mobile_no: { required, minLength: minLength(9) },
      //guarantor: { required },
      prequalified_amount: { required },
      alternate_mobile_line: { },
      referees: {
          $each: {
              full_name: { required },
              id_number: { required },
              phone_number: { required, minLength: minLength(9) },
          }
      }
    },

    location: {
      postal_address: { },
      postal_code: { },
      country: { required },
      county: { required },
      constituency: { required },
      ward: { required },
      physical_address: { required },
      latitude: {  },
      longitude: {  },
      business_address: { required },
      business_latitude: {  },
      business_longitude: {  },
      residence_type: { required },
      years_lived_at_residence: { required, integer },
      home_coordinates: {  },
      business_coordinates: {  }
    },

    profession: {
      industry_type: {
        requiredIf: requiredIf(function() {
          return !this.profession.is_employed;
        })
      },
      business_type: {
        requiredIf: requiredIf(function() {
          return !this.profession.is_employed;
        })
      },
      is_employed: {},
      employment_status: {
        requiredIf: requiredIf(function() {
          return this.profession.is_employed;
        })
      },
      employer: {
        requiredIf: requiredIf(function() {
          return this.profession.is_employed;
        })
      },
      date_of_employment: {
        requiredIf: requiredIf(function() {
          return this.profession.is_employed;
        })
      },
      income_range: { required }
    },

    account: {
      savings_product: { required }
    },

    validationGroup: ["personal", "location", "profession", "account"]
  },

  created() {
    this.relationshipOfficers();
    this.idTypes();
    this.kinRelations();
    this.counties();
    this.industries();
    this.incomeRanges();
    this.prequalifiedAmount();
    this.accounts();
    this.loanTypes();
    this.loanProducts();
    this.formData.loanPurposeType = [
        { "label": "Business Expense", "value": "Business Expense"},
        { "label": "Start Business", "value": "Start Business"}
    ];
  },

  watch: {
    "location.county": function() {
      this.formData.constituencies = {};
      this.location.constituency = "";
    },

    "location.constituency": function() {
      this.formData.wards = {};
      this.location.ward = "";
    //   this.wards();
    },

    "profession.industry_type": function() {
      this.formData.businessTypes = {};
      this.profession.business_type = "";
      this.businessTypes();
    },

     "profession.is_employed": function() {
      this.profession.income_range = '';
    },

    "loan.product_id": function(newValue, currentValue) {
        this.loan.installments = newValue.installments
    },

    "personal.prequalified_amount": function (newValue, currentValue) {
        this.loan.loan_amount = newValue.label
    },
  },

    mounted: function() {
        //Google Maps - Home Address
        const autocompleteHome = new google.maps.places.Autocomplete(
            document.getElementById("physical_address"),
        );
        autocompleteHome.setComponentRestrictions({
            country: ["ke"]
        })

        google.maps.event.addListener(autocompleteHome, 'place_changed', ()=> {
            const home_coordinates = autocompleteHome.getPlace();
            this.location.physical_address = home_coordinates.name;
            this.location.latitude = home_coordinates.geometry.location.lat();
            this.location.longitude = home_coordinates.geometry.location.lng();
        });

        //Google Maps - Business Address
        const autocompleteBusiness = new google.maps.places.Autocomplete(
            document.getElementById("business_physical_address"),
        );
        autocompleteBusiness.setComponentRestrictions({
            country: ["ke"]
        })

        google.maps.event.addListener(autocompleteBusiness, 'place_changed',  ()=> {
            const business_coordinates = autocompleteBusiness.getPlace();
            this.location.business_address = business_coordinates.name;
            this.location.business_latitude = business_coordinates.geometry.location.lat();
            this.location.business_longitude = business_coordinates.geometry.location.lng();
        });


        //Google Maps - Home Address
        const autocompleteHomeConfirm = new google.maps.places.Autocomplete(
            document.getElementById("physical_address_confirm"),
        );
        autocompleteHomeConfirm.setComponentRestrictions({
            country: ["ke"]
        })

        google.maps.event.addListener(autocompleteHomeConfirm, 'place_changed', ()=> {
            const home_coordinates = autocompleteHomeConfirm.getPlace();
            this.location.physical_address = home_coordinates.name;
            this.location.latitude = home_coordinates.geometry.location.lat();
            this.location.longitude = home_coordinates.geometry.location.lng();
        });

        //Google Maps - Business Address
        const autocompleteBusinessConfirm = new google.maps.places.Autocomplete(
            document.getElementById("business_physical_address_confirm"),
        );
        autocompleteBusinessConfirm.setComponentRestrictions({
            country: ["ke"]
        })

        google.maps.event.addListener(autocompleteBusinessConfirm, 'place_changed',  ()=> {
            const business_coordinates = autocompleteBusinessConfirm.getPlace();
            this.location.business_address = business_coordinates.name;
            this.location.business_latitude = business_coordinates.geometry.location.lat();
            this.location.business_longitude = business_coordinates.geometry.location.lng();
        });
    },

  methods: {
    addReferee(index) {
        this.personal.referees.push({full_name: "", id_number: "", phone_number: "" });
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

    relationshipOfficers: async function() {
      try {
        const response = await axios.get(
          `${this.rootUrl}registry-data/relationship-officers`
        );
        this.formData.relationshipOfficers = response.data;
      } catch (err) {
        this.error = err;
      }
    },

    idTypes: async function() {
      try {
        const response = await axios.get(`${this.rootUrl}registry-data/id-types`);
        this.formData.idTypes = response.data;
      } catch (err) {
        this.error = err;
      }
    },

    kinRelations: async function() {
      try {
        const response = await axios.get(`${this.rootUrl}registry-data/kin-relations`);
        this.formData.kinRelations = response.data;
      } catch (err) {
        this.error = err;
      }
    },

    counties: async function() {
      try {
        const response = await axios.get(`${this.rootUrl}registry-data/counties`);
        this.formData.counties = response.data;
      } catch (err) {
        this.error = err;
      }
    },

    constituenciesAndWards: async function() {

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

    constituencies: function() {
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

    getUnique: function(arr, comp) {
      const unique = arr
        .map(e => e[comp])

        // store the keys of the unique objects
        .map((e, i, final) => final.indexOf(e) === i && i)

        // eliminate the dead keys & store unique objects
        .filter(e => arr[e])
        .map(e => arr[e]);

      return unique;
    },

    wards: function() {
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

    industries: async function() {
      try {
        const response = await axios.get(`${this.rootUrl}registry-data/industries`);
        this.formData.industries = response.data;
      } catch (err) {
        this.error = err;
      }
    },

    incomeRanges: async function() {
      try {
        const response = await axios.get(`${this.rootUrl}registry-data/income-ranges`);
        this.formData.incomeRanges = response.data;
      } catch (err) {
        this.error = err;
      }
    },

    prequalifiedAmount: async function() {
      try {
        const response = await axios.get(`${this.rootUrl}registry-data/prequalified-amount`);
        this.formData.prequalifiedAmount = response.data;
      } catch (err) {
        this.error = err;
      }
    },

    businessTypes: async function() {
      try {
        const response = await axios.get(
          `${this.rootUrl}registry-data/business-types/${this.profession.industry_type.value}`
        );
        this.formData.businessTypes = response.data;
      } catch (err) {
        this.error = err;
      }
    },

    loanTypes: async function() {
      try {
        const response = await axios.get(
          `${this.rootUrl}registry-data/loan-types/`
        );
        this.formData.loanTypes = response.data;
      } catch (err) {
        this.error = err;
      }
    },

    loanProducts: async function() {
      try {
        const response = await axios.get(
          `${this.rootUrl}registry-data/loan-products/`
        );
        this.formData.loanProducts = response.data;
      } catch (err) {
        this.error = err;
      }
    },

    accounts: async function() {
      try {
        const response = await axios.get(`${this.rootUrl}registry-data/accounts`);
        this.formData.accounts = response.data;
      } catch (err) {
        this.error = err;
      }
    },

    onComplete: function() {

      // disable the submit button to prevent double clicking.
      this.completed = true;

      toastr.options = {
        positionClass: "toast-top-center"
      };

      this.$v.$touch();
      // if its still pending or an error is returned do not submit '$error'
      if (this.$v.$pending || this.$v.$invalid) {
        toastr.options = {
          positionClass: "toast-top-center"
        };
        toastr.error(
          "Your form has some errors. <br> Correct them to proceed."
        );

        // enable the submit button again.
        this.completed = false;

        return false;
      }

      let allData = Object.assign(
        {},
        this.personal,
        this.location,
        this.profession,
        this.account,
        this.loan
      );

      let app = this;

      axios
        .post(`${this.rootUrl}registry`, allData)
        .then(res => {
          // console.log(res.data);
          toastr.success("You have added a new customer successfully.");
          const context = this
          setTimeout(function() {
            window.location.replace(`${context.rootUrl}registry`);
          }, 1000);
        })
        .catch(error => {
          console.log(error);
          app.completed = false;
          toastr.error(`An error occured: ${error}`);
          return false;
        });

      return true;
    },

    beforeTabSwitchPersonal: function() {
      // return true;
      this.$v.personal.$touch();
      // if its still pending or an error is returned do not submit '$error'
      if (this.$v.personal.$pending || this.$v.personal.$invalid) {
        toastr.options = {
          positionClass: "toast-top-center"
        };
        toastr.error(
          "Your form has some errors. <br> Correct them to proceed."
        );
        return false;
      }
      // to form submit after this
      return true;
    },

    beforeTabSwitchLocation: function() {
      // return true;
      this.$v.location.$touch();
      // if its still pending or an error is returned do not submit
      if (this.$v.location.$pending || this.$v.location.$invalid) {
        toastr.options = {
          positionClass: "toast-top-center"
        };
        toastr.error(
          "Your form has some errors. <br> Correct them to proceed."
        );

        return false;
      }
      // to form submit after this
      return true;
    },

    beforeTabSwitchProfession: function() {
      // return true;
      this.$v.profession.$touch();
      // if its still pending or an error is returned do not submit
      if (this.$v.profession.$pending || this.$v.profession.$invalid) {
        toastr.options = {
          positionClass: "toast-top-center"
        };
        toastr.error(
          "Your form has some errors. <br> Correct them to proceed."
        );

        return false;
      }
      // to form submit after this
      return true;
    },

    beforeTabSwitchAccount: function() {
      // return true;

      this.$v.account.$touch();
      // if its still pending or an error is returned do not submit
      if (this.$v.account.$pending || this.$v.account.$invalid) {
        toastr.options = {
          positionClass: "toast-top-center"
        };
        toastr.error(
          "Your form has some errors. <br> Correct them to proceed."
        );

        return false;
      }
      // to form submit after this
      return true;
    }
  }
};
</script>

<style>
/* Cyan theme */
.v-select {
  width: 100%;
  background-color: #fff;
  color: #1B372B;
  height: auto;
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
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}
</style>
