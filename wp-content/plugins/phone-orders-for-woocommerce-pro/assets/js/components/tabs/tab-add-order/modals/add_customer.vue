<template>
    <div>
        <b-modal id="addCustomer"
                 ref="modal"
                 :title="addCustomerLabel"
                 @shown="formToDefault"
                 @hidden="hideModal"
                 size="lg"
        >
            <!--required fields!!!-->


            <b-form>
                <b-container v-for="section in sectionFields">
                    <b-form-group :label="section.label">
                        <template v-for="(group, groupKey) in section.groups">
                            <google-autocomplete
                                ref="google_autocomplete"
                                :input-placeholder="autocompleteInputPlaceholder"
                                :invalid-message="autocompleteInvalidMessage"
                                @change="onAutocompleteChanged"
                                v-if="groupKey === 'address'"
                                v-show="!!googleAutocompleteAPIKey && !!group.fields.length"
                            ></google-autocomplete>
                            <b-row>
                                <b-col cols="6" v-for="field in group.fields">
                                    <span>
                                        <label class="mr-sm-2">{{ field.label }}</label>

                                        <div v-if="field.key === 'country'">
                                            <multiselect
                                                :ref="field.key"
                                                :allow-empty="false"
                                                :hide-selected="true"
                                                :searchable="true"
                                                label="title"
                                                v-model="field.value"
                                                :options="defaultCountryList"
                                                track-by="value"
                                                @input="onChangeDefaultCountry(field.key)"
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


                                        <div v-else-if="field.key === 'state'">
                                            <multiselect
                                                :ref="field.key"
                                                v-if="statesList.length"
                                                :allow-empty="false"
                                                :hide-selected="true"
                                                :searchable="true"
                                                label="title"
                                                v-model="field.value"
                                                :options="statesList"
                                                track-by="value"
                                                @input="onEnter(field.key)"
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
                                                    type="text"
                                                    class="mb-2 mr-sm-2 mb-sm-0"
                                                    v-model="field.value"
                                                    :ref="field.key"
                                                    @keydown.enter.native="onEnter(field.key)"
                                            >
                                            </b-form-input>
                                        </div>

                                        <div v-else-if="field.key === 'role'">
                                            <multiselect
                                                    :ref="field.key"
                                                    :allow-empty="false"
                                                    :hide-selected="true"
                                                    label="title"
                                                    v-model="field.value"
                                                    :options="rolesList"
                                                    track-by="value"
                                                    @input="onEnter(field.key)"
                                                    :show-labels="false"
                                                    :placeholder="selectPlaceholder"
                                            >

                                            </multiselect>
                                        </div>

                                        <div v-else>
                                            <b-form-input
                                                :ref="field.key"
                                                type="text"
                                                class="mb-2 mr-sm-2 mb-sm-0"
                                                v-model="field.value"
                                                @keydown.enter.native="onEnter(field.key)"
                                            >
                                            </b-form-input>
                                        </div>
                                    </span>
                                </b-col>
                            </b-row>
                        </template>
                    </b-form-group>
                </b-container>
                <slot name="add-customer-address-modal-footer"></slot>
            </b-form>

            <div slot="modal-footer">
                <b-alert :show="!!error" variant="danger" class="error-alert">{{ this.error }}</b-alert>
                <b-button @click="cancel()">{{ cancelLabel }}</b-button>
                <b-button @click="createCustomer()" variant="primary">{{ saveCustomerLabel }}</b-button>
            </div>
        </b-modal>
    </div>
</template>
<script>

	import Multiselect from 'vue-multiselect';
        import GoogleAutocomplete from '../google-autocomplete.vue';

	export default {
            props: {
                fieldsToShow: {
                    type: Object,
                    default: function () {
                        return {}
                    }
                },
                cancelLabel: {
                    default: function () {
                        return 'Cancel';
                    }
                },
                saveCustomerLabel: {
                    default: function () {
                        return 'Save customer';
                    }
                },
                addCustomerLabel: {
                    default: function () {
                        return 'New customer';
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
	            rolesList: {
		            default: function () {
			            return [];
		            },
	            },
            },
        created: function() {
	        this.$root.bus.$on('edit-customer-address-custom-fields-updated', (fields) => {
		        this.customFields = fields;
	        });
        },
            data: function () {
                return Object.assign({
                    error: '',
                    statesList: [],
	                customFields: {},
                }, this.initForm());
            },
            computed: {
                sectionFields: function () {

                    var sections = [];

                    for (let section in this.fieldsToShow) {

                        var groups = {
                            personal: {
                                keys: this.personalFields,
                                fields: [],
                            },
                            address: {
                                keys: this.addressFields,
                                fields: [],
                            },
                        };

                        for ( let $fieldName in this.fieldsToShow[section].fields ) {
                            if ( this.fieldsToShow[section].fields.hasOwnProperty( $fieldName ) ) {
                                for (let group in groups) {
                                    if (groups[group].keys.indexOf($fieldName) > -1 && this.fields[$fieldName].visibility) {
                                        groups[group].fields.push(Object.assign(this.fields[$fieldName], {key: $fieldName}));
                                    }
                                }
                            }
                        }

                        sections.push({
                            label: this.fieldsToShow[section].label,
                            groups: groups,
                        });
                    }

                    return sections;
                },
                settingsData () {

                    var visibility          = {};

                    visibility['password']  = this.getSettingsOption('newcustomer_show_password_field');
                    visibility['username']  = this.getSettingsOption('newcustomer_show_username_field');
                    visibility['role']      = this.getSettingsOption('newcustomer_show_role_field');
                    visibility['email']     = ! this.getSettingsOption('newcustomer_hide_email');
                    visibility['company']   = ! this.getSettingsOption('newcustomer_hide_company');
                    visibility['address_1'] = ! this.getSettingsOption('newcustomer_hide_address_1');
                    visibility['address_2'] = ! this.getSettingsOption('newcustomer_hide_address_2');
                    visibility['city']      = ! this.getSettingsOption('newcustomer_hide_city');
                    visibility['postcode']  = ! this.getSettingsOption('newcustomer_hide_postcode');
                    visibility['country']   = ! this.getSettingsOption('newcustomer_hide_country');
                    visibility['state']     = ! this.getSettingsOption('newcustomer_hide_state');

                    var defaultValues = {};

                    defaultValues['city']      = this.getSettingsOption('default_city');
                    defaultValues['postcode']  = this.getSettingsOption('default_postcode');
                    defaultValues['country']   = this.getSettingsOption('default_country');
                    defaultValues['state']     = this.getSettingsOption('default_state');
                    defaultValues['role']      = this.getSettingsOption('default_role');

                    return {
                        visibility,
                        defaultValues,
                    };
                },
                defaultCountryList () {
                    return this.$root.defaultCountriesList;
                },
                defaultStatesList () {
                    return this.$root.defaultStatesList;
                },
                googleAutocompleteAPIKey() {
                    return this.getSettingsOption('google_map_api_key');
                },
                addressFields () {
	                return ['country', 'address_1', 'address_2', 'city', 'state', 'postcode'];
                },
                personalFields () {
	                return ['username', 'password', 'email', 'role', 'first_name', 'last_name', 'company', 'phone'];
                },
	            doNotSubmitOnEnterLastField() {
		            return this.getSettingsOption( 'do_not_submit_on_enter_last_field' );
	            },
            },
		methods: {
			initForm( $reactFields ) {

                                if ( typeof $reactFields === 'undefined' ) {
					var $reactFields = {};
				}
				$reactFields['fields'] = {};
				$reactFields['visibleFields'] = [];

				// copy without reference
				var $fieldsToShow = JSON.parse( JSON.stringify( this.fieldsToShow ) );

                                for ( let $container in $fieldsToShow ) {
					if ( $fieldsToShow.hasOwnProperty( $container ) ) {
						let $fields = $fieldsToShow[$container]['fields'];
						for ( let $field in $fields ) {
							if ( $fields.hasOwnProperty( $field ) ) {
                                                                $reactFields['fields'][$field] = $fields[$field];

                                                                if (this.settingsData) {

                                                                    if (typeof this.settingsData.visibility[$field] !== 'undefined') {
                                                                        $reactFields['fields'][$field]['visibility'] = this.settingsData.visibility[$field];
                                                                    }

                                                                    if (typeof this.settingsData.defaultValues[$field] !== 'undefined') {
                                                                        $reactFields['fields'][$field]['value'] = this.settingsData.defaultValues[$field];
                                                                    }

                                                                }

                                                            if ($reactFields['fields'][$field]['visibility']) {
                                                                $reactFields['visibleFields'].push($field);
                                                            }
							}
						}

						if ( $fields.hasOwnProperty( 'country' )
                                                        && typeof this.defaultCountryList !== 'undefined'
                                                        && typeof this.defaultStatesList !== 'undefined'
                                                ) {
							var country = this.getObjectByKeyValue( this.defaultCountryList, 'value',
								$fields['country'].value );

							var statesList = this.defaultStatesList[$fields['country'].value] || [];

                                                        this.statesList = statesList;

							$reactFields['fields']['country']['value'] = country || {value: ''};

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

						if ( $fields.hasOwnProperty( 'role' )
                                                        && typeof this.rolesList !== 'undefined'
                                                ) {
							var role = null;

							if ( this.rolesList.length ) {
								role = this.getObjectByKeyValue(this.rolesList, 'value', $fields['role'].value);
							} else {
								role = $fields['role'].value;
							}

							$reactFields['fields']['role']['value'] = role;
						}
					}
				}

				return $reactFields;
			},
			formToDefault() {

                                let $reactFields = this.initForm();

				this.fields         = $reactFields.fields;
				this.visibleFields  = $reactFields.visibleFields;

                                this.$nextTick(() => {
                                    if (this.visibleFields.length) {
                                        this.$refs[this.visibleFields[0]][0].focus();
                                    }
                                });

                                this.error = '';

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
			createCustomer() {
				let $newFields = {};
				for ( let $field in this.fields ) {
					if ( this.fields.hasOwnProperty( $field ) ) {
						if ( this.fields[$field].hasOwnProperty( 'value' ) ) {
							if ( typeof this.fields[$field].value !== 'undefined' &&
							     this.fields[$field].value !== null &&
                                 this.fields[$field].value.hasOwnProperty( 'value' ) ) {
								$newFields[$field] = this.fields[$field].value.value; // for multiselect fields
							} else {
								$newFields[$field] = this.fields[$field].value;
							}
						}
					}
				}

				$newFields['custom_fields'] = this.customFields;

				let $args = {
					action: 'phone-orders-for-woocommerce',
					method: 'create_customer',
					_wp_http_referer: this.referrer,
					_wpnonce: this.nonce,
					tab: this.tabName,
					data: this.qs.stringify( $newFields ),
				};

                                this.error = '';

				this.axios.post( this.url, this.qs.stringify( $args ) ).then( ( response ) => {
					let $data = response.data.data;
					let newId = $data.id;

					if ( response.data.success === true ) {
						this.$root.bus.$emit('update-customer', newId);
						this.$refs.modal.hide();
					} else {
                                            this.error = $data;
                                        }
				}, () => {
				} );
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
					this.createCustomer();
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
