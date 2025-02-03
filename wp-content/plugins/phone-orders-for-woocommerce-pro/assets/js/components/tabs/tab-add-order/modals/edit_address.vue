<template>
    <div>
        <b-modal id="editAddress"
                 ref="modal"
                 :title="editAddressLabel"
                 @shown="formLoad"
                 @hidden="hideModal"
                 size="lg"
        >
            <b-container>

                <b-row v-for="(group, groupKey) in groupFields">

                    <b-col cols="12">

                        <google-autocomplete
                            ref="google_autocomplete"
                            :input-placeholder="autocompleteInputPlaceholder"
                            :invalid-message="autocompleteInvalidMessage"
                            @change="onAutocompleteChanged"
                            v-if="groupKey === 'address'"
                            v-show="!!googleAutocompleteAPIKey && !!group.rows.length"
                        ></google-autocomplete>

                        <b-row v-for="row in group.rows">

                            <b-col v-for="(loopField, numIndex) in row" cols="6">

                                <div v-if="loopField.key === 'country'">
                                    <strong><label class="mr-sm-2">{{ loopField.label }}</label></strong>
                                    <multiselect
                                        :ref="loopField.key"
                                        :allow-empty="false"
                                        :hide-selected="true"
                                        :searchable="true"
                                        label="title"
                                        v-model="loopField.value"
                                        :options="defaultCountryList"
                                        track-by="value"
                                        @input="onChangeDefaultCountry(loopField.key)"
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
                                </div>

                                <div v-else-if="loopField.key === 'state'">
                                    <strong><label class="mr-sm-2">{{ loopField.label }}</label></strong>
                                    <multiselect
                                        :ref="loopField.key"
                                        v-if="statesList.length"
                                        :allow-empty="false"
                                        :hide-selected="true"
                                        :searchable="true"
                                        label="title"
                                        v-model="loopField.value"
                                        :options="statesList"
                                        track-by="value"
                                        @input="onEnter(loopField.key)"
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
                                    <b-form-input
                                        v-else
                                        :ref="loopField.key"
                                        type="text"
                                        class="mb-2 mr-sm-2 mb-sm-0"
                                        v-model="loopField.value"
                                        @keydown.enter.native="onEnter(loopField.key)"
                                    >
                                    </b-form-input>
                                </div>

                                <div v-else>
                                    <strong><label class="mr-sm-2">{{ loopField.label }}</label></strong>
                                    <b-form-input
                                        :ref="loopField.key"
                                        type="text"
                                        class="mb-2 mr-sm-2 mb-sm-0"
                                        v-model="loopField.value"
                                        @keydown.enter.native="onEnter(loopField.key)"
                                    >
                                    </b-form-input>
                                </div>
                            </b-col>
                        </b-row>
                    </b-col>
                </b-row>
            </b-container>
            <slot name="edit-customer-address-modal-footer"></slot>
            <div slot="modal-footer">
                <b-alert :show="!!error" variant="danger" class="error-alert">{{ this.error }}</b-alert>
                <b-button @click="cancel()">{{ cancelLabel }}</b-button>
                <b-button @click="saveAddress()" variant="primary">{{ saveAddressLabel }}</b-button>
            </div>
        </b-modal>
    </div>
</template>

<script>

        import Multiselect from 'vue-multiselect';
        import GoogleAutocomplete from '../google-autocomplete.vue';

	export default {
		props: {
                    cancelLabel: {
                        default: function () {
                            return 'Cancel';
                        }
                    },
                    editAddressLabel: {
                        default: function () {
                            return 'Edit address';
                        }
                    },
                    saveAddressLabel: {
                        default: function () {
                            return 'Save address';
                        }
                    },
                    tabName: {
                        default: function () {
                            return 'add-order';
                        },
                    },
                    selectPlaceholder: {
                        default: function () {
                            return 'Select option';
                        },
                    },
                    autocompleteInputPlaceholder: {
                        default: function () {
                            return null;
                        },
                    },
                    autocompleteInvalidMessage: {
                        default: function () {
                            return null;
                        },
                    },
		},
		created: function () {
                    this.$root.bus.$on('edit-customer-address', (data) => {
                        this.addressType = data.addressType;
                        this.customer = data.customer;
	                    this.customFields = {};
                        this.fieldsToShow = data.fields;
                        this.$refs.modal.show()
                    });

			this.$root.bus.$on('edit-customer-address-custom-fields-updated', (fields) => {
				this.customFields = fields;
			});

                    this.$root.bus.$on('update-customer-request', (data) => {
                        this.updateCustomerRequest(data.customer, data.callback);
                    });
                },
		data: function () {
                    let $react_fields = this.initForm();
                    $react_fields['addressType'] = '';
                    $react_fields['customer'] = {};
                    $react_fields['fieldsToShow'] = {};
                    $react_fields['customFields'] = {};
                    $react_fields['error']          = '';

                    return $react_fields;
		},
		computed: {
                    groupFields: function () {

                        var groups = {
                            personal: {
                                keys: this.personalFields,
                                fields: [],
                                rows: [],
                            },
                            address: {
                                keys: this.addressFields,
                                fields: [],
                                rows: [],
                            },
                        };

                        for ( let $fieldName in this.fields ) {
                            if ( this.fields.hasOwnProperty( $fieldName ) ) {
                                for (let group in groups) {
                                    if (groups[group].keys.indexOf($fieldName) > -1 && this.fields[$fieldName].visibility) {
                                        groups[group].fields.push(Object.assign(this.fields[$fieldName], {key: $fieldName}));
                                    }
                                }
                            }
                        }

                        let $elementsInRow = 2;

                        for (let group in groups) {

                            let $row      = [];
                            let $numIndex = 0;

                            groups[group].fields.forEach (function (v) {

                                if (v.key === 'email') {
                                    groups[group].rows.push([v]);
                                    return true;
                                }

                                $row.push(v);

                                if ($row.length < $elementsInRow) {
                                    return true;
                                }

                                groups[group].rows.push($row);

                                $row = [];
                            });

                            if ($row.length) {
                                groups[group].rows.push($row);
                            }
                        }

                        return groups;
                    },
                    defaultCountryList () {
                        return this.$root.defaultCountriesList;
                    },
                    defaultStatesList () {
                        return this.$root.defaultStatesList;
                    },
                    googleAutocompleteAPIKey () {
                        return this.getSettingsOption('google_map_api_key');
                    },
                    addressFields () {
	                    return ['country', 'address_1', 'address_2', 'city', 'state', 'postcode'];
                    },
                    personalFields () {
                        return ['email', 'first_name', 'last_name', 'company', 'phone'];
                    },
		    doNotSubmitOnEnterLastField() {
			    return this.getSettingsOption( 'do_not_submit_on_enter_last_field' );
		    },
		    storedCustomer() {
			return this.$store.state.add_order.cart.customer;
		    },

		},
		methods: {
			initForm( $reactFields ) {
				if ( typeof $reactFields === 'undefined' ) {
					var $reactFields = {};
				}
				$reactFields['fields'] = {};
                                $reactFields['visibleFields'] = [];

				if ( typeof this.fieldsToShow === 'undefined') {
					return $reactFields;
				}

				// copy without reference
				var $fields = JSON.parse( JSON.stringify( this.fieldsToShow ) );

				var defaultValues = {
				    city: this.getSettingsOption('default_city'),
				    country: this.getSettingsOption('default_country'),
				    state: this.getSettingsOption('default_state'),
				    postcode: this.getSettingsOption('default_postcode'),
				};

				var isEmptyCustomer = typeof this.storedCustomer.billing_first_name === 'undefined';

				for ( let $field in $fields ) {
					if ( $fields.hasOwnProperty( $field ) ) {
						$reactFields['fields'][$field] = $fields[$field];

                                                if (this.addressType === 'shipping' && ['email', 'phone'].indexOf($field) !== -1) {
                                                    $reactFields['fields'][$field]['visibility'] = false;
                                                } else {
                                                    $reactFields['fields'][$field]['visibility'] = true;
                                                }

						if (typeof defaultValues[$field] !== 'undefined' && isEmptyCustomer) {
						    $reactFields['fields'][$field]['value'] = defaultValues[$field];
						}
					}

                                        if ($reactFields['fields'][$field]['visibility']) {
                                            $reactFields['visibleFields'].push($field);
                                        }
				}

				if ( $fields.hasOwnProperty( 'country' )
                                        && typeof this.defaultCountryList !== 'undefined'
                                        && typeof this.defaultStatesList !== 'undefined'
                                ) {
					var country = this.getObjectByKeyValue( this.defaultCountryList, 'value',
						$fields['country'].value );

					var statesList = this.defaultStatesList[$fields['country'].value] || [];

					$reactFields['fields']['country']['value'] = country || {value: ''};

					//	if formToDefault() we need to update 'statesList'
					this.statesList = statesList;

					$reactFields['statesList'] = statesList;
				}

				if ( $fields.hasOwnProperty( 'state' )
                                    && typeof statesList !== 'undefined'
                                ) {
					var state = null;

					if ( statesList.length ) {
						state = this.getObjectByKeyValue( statesList, 'value', $fields['state'].value );
					} else {
						state = $fields['state'].value;
					}

					$reactFields['fields']['state']['value'] = state;
				}

				return $reactFields;
			},
			formLoad() {

                            let $reactFields       = this.initForm();
                            this.fields            = $reactFields.fields;
                            this.visibleFields     = $reactFields.visibleFields;
                            this.error             = '';

                            this.$nextTick(() => {
                                if (this.visibleFields.length) {
                                    this.$refs[this.visibleFields[0]][0].focus();
                                }
                            });
			},
                        hideModal() {
                            this.$refs.google_autocomplete.forEach((component) => {
                               component.clear();
                               component.init();
                            });
			},
			cancel() {
				this.$refs.modal.hide();
			},
			saveAddress() {

                            if ( ! this.customer ) {
                                    this.customer = {};
                            }

                            for ( let $field in this.fields ) {
                                if ( this.fields.hasOwnProperty($field) ) {
                                    if ( this.fields[$field].hasOwnProperty( 'value' ) ) {
                                        if ( typeof this.fields[$field].value === 'object' && this.fields[$field].value.hasOwnProperty( 'value' ) ) {
                                            this.customer[this.addressType + '_' + $field] = this.fields[$field].value.value; // for multiselect fields
                                        } else {
                                            this.customer[this.addressType + '_' + $field] = this.fields[$field].value;
                                        }
                                    }
                                }
                            }
                this.customer['custom_fields'] = Object.assign( {}, this.customFields );

			    this.updateCustomerRequest(this.customer, (response) => {

				let $data = response.data.data;

				if ( response.data.success === true ) {
					this.$store.commit('add_order/updateCustomer', $data.customer);
					this.$refs.modal.hide();
				} else {
				    this.error = $data;
				}
			    });
			},
			updateCustomerRequest(customer, callback) {

				let $args = {
					customer_data: customer,
					action: 'phone-orders-for-woocommerce',
					method: 'update_customer',
					_wp_http_referer: this.referrer,
					_wpnonce: this.nonce,
					tab: this.tabName,
				};

                                this.error = '';

				this.axios.post( this.url, this.qs.stringify( $args ) ).then( ( response ) => {
				    callback(response);
				}, () => {} );
			},
			onChangeDefaultCountry: function (fieldKey) {
				this.fields.state.value = '';
				this.statesList = this.defaultStatesList[this.fields.country.value.value] || [];

				if ( this.statesList.length ) {
					this.fields.state.value = this.getObjectByKeyValue( this.statesList, 'value', this.fields.state.value );
				}

                                this.$nextTick(() => {
                                    this.onEnter(fieldKey);
                                })
			},
			onEnter: function ( fieldKey ) {

				var currentIndex = this.visibleFields.indexOf( fieldKey );
				var nextIndex = currentIndex + 1;

				if ( typeof this.visibleFields[nextIndex] !== 'undefined' ) {

					if ( typeof this.$refs[this.visibleFields[nextIndex]][0].activate === 'function' ) {
						this.$refs[this.visibleFields[nextIndex]][0].activate();
					} else {
						this.$refs[this.visibleFields[nextIndex]][0].focus();
					}

					return;
				}

				if ( ! this.doNotSubmitOnEnterLastField ) {
					this.saveAddress();
				}

			},
                        onAutocompleteChanged: function (fields) {

                            for (let fieldKey in fields) {
                                if (this.fields[fieldKey]) {

                                    this.fields[fieldKey].value = null;

                                    if (this.visibleFields.indexOf(fieldKey) > -1) {
                                        this.fields[fieldKey].value = fields[fieldKey];
                                        if (fieldKey === 'country') {
                                            this.fields[fieldKey].value = this.getObjectByKeyValue(
                                                this.defaultCountryList,
                                                'value',
                                                this.fields[fieldKey].value
                                            );
                                            this.statesList = this.defaultStatesList[this.fields.country.value.value] || [];
                                        }
                                    }
                                }
                            }

                            if ( this.statesList.length ) {
                                this.fields.state.value = this.getObjectByKeyValue( this.statesList, 'title', this.fields.state.value );
                            }
                        },
		},
		components: {
                    Multiselect,
                    GoogleAutocomplete,
		},
	}
</script>
