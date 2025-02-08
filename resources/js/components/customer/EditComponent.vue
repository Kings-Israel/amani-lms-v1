<template>
    <form @submit.prevent="onUpdate">
        <!-- Personal information -->
        <div class="content-container">
            <h5 class="my-2 text-primary">Personal Information</h5>
            <div class="form-group row">
                <!-- type -->
                <div class="col-md-3 my-1">
                    <label class="block">Type</label>
                    <div class="input-group mb-1">
                        <span class="input-group-addon">
                            <i class="icofont icofont-ui-user"></i>
                        </span>

                        <v-select id="v-select" name="customer type" v-model.trim="$v.personal.customer_type.$model"
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

                        <v-select id="v-select" name="customer title" v-model="$v.personal.customer_title.$model"
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
                        <input v-model="$v.personal.first_name.$model" name="first_name" type="text"
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
                        <input v-model="$v.personal.last_name.$model" name="last_name" type="text" class="form-control"
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
                        <v-select id="v-select" name="$v.personal.relationship officer.$model"
                            v-model="$v.personal.relationship_officer.$model"
                            :options="formData.relationshipOfficers"></v-select>
                    </div>
                    <transition name="fade">
                        <div class="text-danger"
                            v-show="$v.personal.relationship_officer.$error && !$v.personal.relationship_officer.required">
                            * Relationship officer is required</div>
                    </transition>
                </div>

                <!-- mobile line -->
                <div class="col-md-3 my-1">
                    <label class="block">Mobile Line (7xxxxxxxx)</label>
                    <div class="input-group mb-1">
                        <span class="input-group-addon">
                            <i class="icofont icofont-mobile-phone"></i>
                        </span>
                        <input v-model="$v.personal.mobile_line.$model" name="mobile_line" type="tel"
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
                        <div class="text-danger" v-show="$v.personal.email.$error && !$v.personal.email.required">*
                            Email is required</div>
                    </transition>

                    <transition name="fade">
                        <div class="text-danger" v-show="$v.personal.email.$error && !$v.personal.email.email">* Should
                            be a valid email</div>
                    </transition>
                </div>

                <!-- identity number -->
                <div class="col-md-3 my-1">
                    <label class="block">Identity Number</label>
                    <div class="input-group mb-1">
                        <span class="input-group-addon">
                            <i class="icofont icofont-id"></i>
                        </span>
                        <input v-model="$v.personal.identity_number.$model" name="identity_number" type="text"
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

                <!-- prequalified amount -->
                <div class="col-md-3 my-1">
                    <label class="block">Prequalified Amount</label>
                    <div class="input-group mb-1">
                        <span class="input-group-addon">
                            <i class="icofont icofont-money"></i>
                        </span>
                        <v-select id="v-select" name="prequalified amount"
                            v-model="$v.personal.prequalified_amount.$model"
                            :options="formData.prequalifiedAmount"></v-select>
                    </div>
                    <transition name="fade">
                        <div class="text-danger"
                            v-show="$v.personal.prequalified_amount.$error && !$v.personal.prequalified_amount.required">
                            * Prequalified amount is required</div>
                    </transition>
                </div>

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
                            type="number" class="form-control" placeholder="No of times customer was given a loan" min="0">
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

                <div class="col-md-3">
                    <label class="block">Business Type</label>
                    <div class="input-group mb-1">
                        <span class="input-group-addon">
                        <i class="icofont icofont-ui-office"></i>
                        </span>

                        <v-select
                            id="v-select"
                            name="business_type"
                            v-model="profession.business_type"
                            :options="Object.values( formData.businessTypes )"
                        ></v-select>
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
                        <v-select id="v-select" name="country" v-model="$v.location.country.$model"
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
                        <v-select id="v-select" name="county" v-model="$v.location.county.$model"
                            :options="formData.counties"></v-select>
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
                        <v-select id="v-select" name="constituency" v-model="$v.location.constituency.$model"
                            :options="formData.constituencies"></v-select>
                    </div>
                    <transition name="fade">
                        <div class="text-danger"
                            v-show="$v.location.constituency.$error && !$v.location.constituency.required">*
                            Constituency is required</div>
                    </transition>
                </div>

                <!-- ward -->
                <div class="col-md-3 my-1">
                    <label class="block">Ward</label>

                    <div class="input-group mb-1">
                        <span class="input-group-addon">
                            <i class="icofont icofont-ui-user"></i>
                        </span>
                        <v-select id="v-select" name="ward" v-model="$v.location.ward.$model"
                            :options="formData.wards"></v-select>
                    </div>
                    <transition name="fade">
                        <div class="text-danger" v-show="$v.location.ward.$error && !$v.location.ward.required">* Ward
                            is required</div>
                    </transition>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <button type="submit" :disabled="completed" class="btn btn-primary">Update</button>
        </div>
    </form>
</template>

<script>
import "vue-form-wizard/dist/vue-form-wizard.min.css";

import Datepicker from "vuejs-datepicker";

import VueCtkDateTimePicker from 'vue-ctk-date-time-picker';
import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css';

import moment from "moment";

import axios from "axios";
import toastr from "toastr";

import { required, requiredIf, minLength, email, alphaNum, integer, minValue } from "vuelidate/lib/validators";

export default {
    name: "RegisterComponent",

    props: ["customerId"],

    components: {
        Datepicker,
        VueCtkDateTimePicker
    },

    data() {
        return {
            rootUrl: "/",
            completed: false,
            eighteenYearsAgo: moment().subtract(18, 'years'),

            original_phone_number: 0,
            original_identity_number: 0,

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
                identity_number: "",
                marital_status: "",
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

            profession: {
                business_type: "",
            },

            personalTemp: {
                customer_type: "",
                customer_title: "",
                first_name: "",
                middle_name: "",
                last_name: "",
                relationship_officer: "",
                date_of_birth: "",
                mobile_line: "",
                email: "",
                identity_number: "",
                prequalified_amount: "",
                alternate_mobile_line: "",
                loan_applications_number: 0,
            },

            location: {
                postal_address: "",
                postal_code: "",
                country: "",
                county: "",
                constituency: "",
                ward: "",
            },

            formData: {
                relationshipOfficers: [],
                idTypes: [],
                kinRelations: [],
                counties: [],
                industries: [],
                businessTypes: [],
                accounts: [],
                incomeRanges: [],
                prequalifiedAmount: [],
                constituenciesAndWards: [],
                constituencies: [],
                wards: []
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
            tax_pin: {},
            gender: { required },
            date_of_birth: { required },
            mobile_line: {
                required, minLength: minLength(9), uniquePhoneNumber(phone_number) {
                    if (phone_number === '') return true;
                    if (phone_number === this.original_phone_number) return true;

                    // phone number has changed therefore validate
                    let country_code = "254";
                    let phone_country_code = country_code + phone_number.slice(-9);
                    return axios.get(`${this.rootUrl}registry-data/unique-phone-number/${phone_country_code}`)
                        .then(res => {
                            return res.data
                        })
                }
            },
            email: {},
            identity_type: { required },
            identity_number: {
                required, alphaNum, uniqueIdNumber(id_number) {
                    if (id_number === '') return true;
                    if (id_number == this.original_identity_number) return true;

                    // id number has changed therefore validate
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
            ward: { required },
        },
    },

    created() {
        this.relationshipOfficers();
        this.idTypes();
        this.kinRelations();
        //this.guarantors();
        this.counties();
        this.prequalifiedAmount();
        this.accounts();
        this.customerPersonalDetails();
        this.customerLocationDetails();
        this.businessTypes()
    },

    watch: {
        "location.county": function () {
            // this.formData.constituencies = {};
            // this.location.constituency = "";
        },

        "location.constituency": function () {
            // this.formData.wards = {};
            // this.location.ward = "";
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
            this.wards()
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

        industries: async function () {
            try {
                const response = await axios.get(`${this.rootUrl}registry-data/industries`);
                this.formData.industries = response.data;
            } catch (err) {
                this.error = err;
            }
        },

        incomeRanges: async function () {
            try {
                const response = await axios.get(`${this.rootUrl}registry-data/income-ranges`);
                this.formData.incomeRanges = response.data;
            } catch (err) {
                this.error = err;
            }
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

        accounts: async function () {
            try {
                const response = await axios.get(`${this.rootUrl}registry-data/accounts`);
                this.formData.accounts = response.data;
            } catch (err) {
                this.error = err;
            }
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

        customerPersonalDetails: async function () {
            try {
                const response = await axios.get(
                    `${this.rootUrl}registry-data/customer-personal-details/${this.customerId}`
                );

                this.personal = response.data;
                // to know when value changes
                this.original_phone_number = response.data.mobile_line;
                this.original_identity_number = response.data.identity_number;

            } catch (err) {
                this.error = err;
            }
        },

        customerLocationDetails: async function () {
            try {
                const response = await axios.get(
                    `${this.rootUrl}registry-data/customer-location-details/${this.customerId}`
                );
                this.location = response.data;
                // console.log(reponse.data)
            } catch (err) {
                this.error = err;
            }
        },

        customerAccountDetails: async function () {
            try {
                const response = await axios.get(
                    `${this.rootUrl}registry-data/customer-account-details/${this.customerId}`
                );
                this.account = response.data;
                // console.log(reponse.data)
            } catch (err) {
                this.error = err;
            }
        },

        onUpdate: function () {
            // disable the submit button to prevent double clicking.
            this.completed = true;

            toastr.options = {
                positionClass: "toast-top-center"
            };

            this.$v.$touch();

            let allData = Object.assign(
                {},
                this.personal,
                this.location,
                this.profession,
                this.account
            );

            let app = this;

            axios
                .put(`${this.rootUrl}registry/${this.customerId}/update`, allData)
                .then(res => {
                    // console.log(res.data);
                    toastr.success("You have edited the customer successfully.");
                    const context = this
                    setTimeout(function () {
                        window.location.replace(`${context.rootUrl}registry`);
                    }, 1000);
                })
                .catch(error => {
                    if (error.response.status == 422) {
                        toastr.error('Invalid data. Check before submitting')
                    } else {
                        toastr.error(`An error occurred: ${error}`);
                    }
                    app.completed = false;
                    console.log(error);
                    return false;
                });

            return true;
        },

        beforeTabSwitchPersonal: function () {
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

        beforeTabSwitchLocation: function () {
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

        beforeTabSwitchProfession: function () {
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

        beforeTabSwitchAccount: function () {
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
    color: #333;
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
    color: #8ec63f;
    background-color: #d7f3f9;
    border-color: #8ec63f;
}

#v-select.dropdown.open .dropdown-toggle,
#v-select.dropdown.open .dropdown-menu {
    border-color: #8ec63f;
}

#v-select .active a {
    background: rgba(50, 50, 50, 0.1);
    color: #333;
}

#v-select.dropdown .highlight a,
#v-select.dropdown li:hover a {
    background: #8ec63f;
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
