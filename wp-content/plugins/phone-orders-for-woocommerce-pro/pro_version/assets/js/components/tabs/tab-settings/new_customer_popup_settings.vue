<template>
    <tr>
        <td colspan=2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <td colspan=2>
                            <b>{{ title }}</b>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ disableCreatingCustomersLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="enableCreatingCustomers" name="disable_creating_customers">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ newcustomerShowPasswordFieldLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="showPassword" name="newcustomer_show_password_field">
                            <i>{{ newcustomerShowPasswordFieldNote }}</i>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ newcustomerShowUsernameFieldLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="showUsername" name="newcustomer_show_username_field">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ newcustomerShowRoleFieldLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="showRole" name="newcustomer_show_role_field">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ emailIsOptionalLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="emailOptional" name="newcustomer_email_is_optional">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ hideFieldsLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option_checkbox" v-model="emailHide" name="newcustomer_hide_email">{{ hideEmailLabel }}  &nbsp;

                            <input type="checkbox" class="option_checkbox" v-model="companyHide" name="newcustomer_hide_company">{{ hideCompanyLabel }}  &nbsp;

                            <input type="checkbox" class="option_checkbox" v-model="address1Hide" name="newcustomer_hide_address_1">{{ hideAddress1Label }} &nbsp;
                            <input type="checkbox" class="option_checkbox" v-model="address2Hide" name="newcustomer_hide_address_2">{{ hideAddress2Label }} &nbsp;
                            <input type="checkbox" class="option_checkbox" v-model="cityHide" name="newcustomer_hide_city">{{ hideCityLabel }} &nbsp;

                            <input type="checkbox" class="option_checkbox" v-model="postcodeHide" name="newcustomer_hide_postcode">{{ hidePostcodeLabel }} &nbsp;

                            <input type="checkbox" class="option_checkbox" v-model="countryHide" name="newcustomer_hide_country">{{ hideCountryLabel }} &nbsp;

                            <input type="checkbox" class="option_checkbox" v-model="stateHide" name="newcustomer_hide_state">{{ hideStateLabel }} &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ defaultCityLabel }}
                        </td>
                        <td>
                            <input type="text" class="option" v-model="cityDefault" name="default_city">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ defaultPostcodeLabel }}
                        </td>
                        <td>
                            <input type="text" class="option" v-model="postcodeDefault" name="default_postcode">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ defaultCountryLabel }}
                        </td>
                        <td>
                            <multiselect
                                :allow-empty="false"
                                :hide-selected="true"
                                :searchable="true"
                                style="width: 100%;max-width: 800px;"
                                label="title"
                                v-model="countryDefault"
                                :options="defaultCountryList"
                                track-by="value"
                                @input="onChangeDefaultCountry"
                                :show-labels="false"
                                :placeholder="selectPlaceholder"
                            >
                                <template slot="singleLabel" slot-scope="props">
                                    <span v-html="props.option.title"></span>
                                </template>
                                <template slot="option" slot-scope="props">
                                    <span v-html="props.option.title"></span>
                                </template>
                            </multiselect>
                        </td>
                    </tr>
                    <tr id="default-state-row">
                        <td>
                            {{ defaultStateLabel }}
                        </td>
                        <td>
                            <div id="default_state">
                                <span v-if="statesList.length">
                                    <multiselect
                                        :allow-empty="false"
                                        :hide-selected="true"
                                        :searchable="true"
                                        style="width: 100%;max-width: 800px;"
                                        v-model="stateDefault"
                                        :options="statesList"
                                        label="title"
                                        track-by="value"
                                        :show-labels="false"
                                        :placeholder="selectPlaceholder"
                                    >
                                        <template slot="singleLabel" slot-scope="props">
                                            <span v-html="props.option.title"></span>
                                        </template>
                                        <template slot="option" slot-scope="props">
                                            <span v-html="props.option.title"></span>
                                        </template>
                                    </multiselect>
                                </span>
                                <span v-else>
                                    <input type="text" v-model="stateDefault">
                                </span>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            {{ defaultRoleLabel }}
                        </td>
                        <td>
                            <multiselect
                                    :allow-empty="false"
                                    :hide-selected="true"
                                    :searchable="true"
                                    style="width: 100%;max-width: 800px;"
                                    label="title"
                                    v-model="roleDefault"
                                    :options="rolesList"
                                    track-by="value"
                                    :show-labels="false"
                                    :placeholder="selectPlaceholder"
                            >
                            </multiselect>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            {{ dontFillShippingAddressForNewCustomerLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="dntFillShipping" name="dont_fill_shipping_address_for_new_customer">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            {{ disableNewUserEmailLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="elDisableNewUserEmail" name="disable_new_user_email">
                        </td>
                    </tr>
               </tbody>
            </table>
        </td>
    </tr>
</template>

<style>
</style>

<script>

    import Multiselect from 'vue-multiselect';

    export default {
        props: {
            title: {
                default: function() {
                    return 'New Customer';
                },
            },
            newcustomerShowPasswordFieldLabel: {
                default: function() {
                    return 'Show Password field';
                },
            },
            newcustomerShowPasswordField: {
                default: function() {
                    return false;
                },
            },
            newcustomerShowPasswordFieldNote: {
                default: function() {
                    return 'You have to tell them the password';
                },
            },
            newcustomerShowUsernameFieldLabel: {
                default: function() {
                    return 'Show Username field';
                },
            },
            newcustomerShowUsernameField: {
                default: function() {
                    return false;
                },
            },
	        emailIsOptionalLabel: {
		        default: function() {
			        return 'Email is optional';
		        },
	        },
	        emailIsOptional: {
		        default: function() {
			        return true;
		        },
	        },
            hideFieldsLabel: {
                default: function() {
                    return 'Hide fields';
                },
            },
            hideCompanyLabel: {
                default: function() {
                    return 'Company';
                },
            },
            hideCompany: {
                default: function() {
                    return false;
                },
            },
            hideEmailLabel: {
                default: function() {
                    return 'Email';
                },
            },
            hideEmail: {
                default: function() {
                    return false;
                },
            },
            hideAddress1Label: {
                default: function() {
                    return 'Address 1';
                },
            },
            hideAddress1: {
                default: function() {
                    return false;
                },
            },
            hideAddress2Label: {
                default: function() {
                    return 'Address 2';
                },
            },
            hideAddress2: {
                default: function() {
                    return false;
                },
            },
            hideCityLabel: {
                default: function() {
                    return 'City';
                },
            },
            hideCity: {
                default: function() {
                    return false;
                },
            },
            hidePostcodeLabel: {
                default: function() {
                    return 'Postcode';
                },
            },
            hidePostcode: {
                default: function() {
                    return false;
                },
            },
            hideCountryLabel: {
                default: function() {
                    return 'Country';
                },
            },
            hideCountry: {
                default: function() {
                    return false;
                },
            },
            hideStateLabel: {
                default: function() {
                    return 'State';
                },
            },
            hideState: {
                default: function() {
                    return false;
                },
            },
            defaultCityLabel: {
                default: function() {
                    return 'Default city';
                },
            },
            defaultCity: {
                default: function() {
                    return '';
                },
            },
            defaultPostcodeLabel: {
                default: function() {
                    return 'Default postcode';
                },
            },
            defaultPostcode: {
                default: function() {
                    return '';
                },
            },
            defaultCountryLabel: {
                default: function() {
                    return 'Default country';
                },
            },
            defaultCountry: {
                default: function() {
                    return '';
                },
            },
            defaultStateLabel: {
                default: function() {
                    return 'Default  state/county';
                },
            },
            defaultState: {
                default: function() {
                    return '';
                },
            },
            selectPlaceholder: {
                default: function () {
                    return 'Select option';
                },
            },
	        dontFillShippingAddressForNewCustomer: {
                default: function () {
                    return false;
                },
            },
	        dontFillShippingAddressForNewCustomerLabel: {
                default: function () {
                    return 'Don\'t fill shipping address';
                },
            },
	        disableCreatingCustomersLabel: {
                default: function () {
                    return 'Disable creating customers';
                },
            },
	        disableCreatingCustomers: {
                default: function () {
                    return false;
                },
            },
            tabName: {
                default: function () {
                    return 'settings';
                },
            },
	        newcustomerShowRoleField: {
                default: function () {
                    return false;
                },
            },
            newcustomerShowRoleFieldLabel: {
                default: function () {
                    return 'Show Role field';
                },
            },
	        defaultRoleLabel: {
                default: function () {
                    return 'Default role';
                },
            },
	        defaultRole: {
                default: function () {
                    return '';
                },
            },
	        rolesList: {
                default: function () {
                    return [];
                },
            },
            disableNewUserEmailLabel: {
                default: function () {
                    return 'Disable user notification email';
                },
            },
            disableNewUserEmail: {
                default: function () {
                    return false;
                },
            },
        },
        data () {
            return {
                statesList: [],
	            enableCreatingCustomers: this.disableCreatingCustomers,
                showPassword: this.newcustomerShowPasswordField,
                showUsername: this.newcustomerShowUsernameField,
                showRole: this.newcustomerShowRoleField,
                companyHide: this.hideCompany,
                emailHide: this.hideEmail,
                address1Hide: this.hideAddress1,
                address2Hide: this.hideAddress2,
                cityHide: this.hideCity,
                postcodeHide: this.hidePostcode,
                countryHide: this.hideCountry,
                stateHide: this.hideState,
                cityDefault: this.defaultCity,
                postcodeDefault: this.defaultPostcode,
                countryDefault: this.defaultCountry,
                stateDefault: this.defaultState,
	            emailOptional: this.emailIsOptional,
	            roleDefault: this.getObjectByKeyValue(this.rolesList, 'value', this.defaultRole),
	            dntFillShipping: this.dontFillShippingAddressForNewCustomer,
                elDisableNewUserEmail: this.disableNewUserEmail,
            };
        },
        computed: {
            defaultCountryList () {
                return this.$root.defaultCountriesList;
            },
            defaultStatesList () {
                return this.$root.defaultStatesList;
            },
        },
        watch: {
            defaultCountryList () {
                this.countryDefault = this.getObjectByKeyValue(this.defaultCountryList, 'value', this.getKeyValueOfObject(this.countryDefault, 'value'));
            },
            defaultStatesList () {
                this.statesList   = this.defaultStatesList[this.getKeyValueOfObject(this.countryDefault, 'value')] || [];
                this.stateDefault = this.getObjectByKeyValue(
                    this.statesList,
                    'value',
                    this.getKeyValueOfObject(this.stateDefault, 'value'),
                    this.getKeyValueOfObject(this.stateDefault, 'value')
                );
            },
        },
        methods: {
	        getSettings() {
		        return {
			        disable_creating_customers: this.enableCreatingCustomers,
			        newcustomer_show_password_field: this.showPassword,
			        newcustomer_show_username_field: this.showUsername,
			        newcustomer_show_role_field: this.showRole,
			        newcustomer_email_is_optional: this.emailOptional,
			        newcustomer_hide_email: this.emailHide,
			        newcustomer_hide_company: this.companyHide,
			        newcustomer_hide_address_1: this.address1Hide,
			        newcustomer_hide_address_2: this.address2Hide,
			        newcustomer_hide_city: this.cityHide,
			        newcustomer_hide_postcode: this.postcodeHide,
			        newcustomer_hide_country: this.countryHide,
			        newcustomer_hide_state: this.stateHide,
			        default_city: this.cityDefault,
			        default_postcode: this.postcodeDefault,
			        default_role: this.getKeyValueOfObject( this.roleDefault, 'value' ),
			        dont_fill_shipping_address_for_new_customer: this.dntFillShipping,
			        default_country: this.getKeyValueOfObject( this.countryDefault, 'value' ),
			        default_state: this.getKeyValueOfObject( this.stateDefault, 'value' ),
                    disable_new_user_email: this.elDisableNewUserEmail,
		        };
	        },
            onChangeDefaultCountry () {

                this.stateDefault = '';
                this.statesList   = this.defaultStatesList[this.countryDefault.value] || [];

                if (this.statesList.length) {
                    this.stateDefault = this.getObjectByKeyValue(this.statesList, 'value', this.stateDefault);
                }
            },
         },
        components: {
            Multiselect,
        },
    }
</script>