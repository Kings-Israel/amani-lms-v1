import Vue from "vue";
import Vuex from "vuex";
import createPersistedState from 'vuex-persistedstate'

Vue.use(Vuex);

const state = {

    userInput: {

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
        guarantor: "",
        prequalified_amount: "",
        alternate_mobile_line: "",

        savings_product: "",

        postal_address: "",
        postal_code: "",
        country: "kenya",
        county: "",
        contituency: "",
        ward: "",
        physical_address: "",
        residence_type: "",
        years_lived_at_residence: "",
        home_coordinates: "",
        business_coordinates: "",

        industry_type: "",
        business_type: "",
        is_employed: "",
        employment_status: "",
        employer: "",
        date_of_employment: "",
        employment_end_date: "",
        income_range: "",
    },

    presetData: {},
};

const store = new Vuex.Store({
    state,
    plugins: [createPersistedState()]
});

export default store;