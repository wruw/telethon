<template>
    <b-row>
        <b-col cols="12" class="google-autocomplete-component">
            <div class="autocomplete-block alert alert-primary" v-show="isValidAPIKey">
                <b-form-input :placeholder="inputPlaceholder" ref="autocomplete_address" v-model="autocompleteInput" :key="key"/>
                <fa-icon icon="search-location" class="autocomplete-icon"/>
            </div>
            <b-alert show variant="warning" class="alert-autocomplete" v-show="!isValidAPIKey">
                {{ invalidMessage }} <fa-icon icon="exclamation-triangle" class="alert-icon"/>
            </b-alert>
        </b-col>
    </b-row>
</template>

<style>
    .pac-container.pac-logo {
        z-index: 1000000;
    }

    .alert-autocomplete .alert-icon {
        float: right;
        margin-top: 4px;
    }

    .autocomplete-block {
        position: relative;
    }

    .autocomplete-block .autocomplete-icon {
        position: absolute;
        top: 22px;
        right: 25px;
        font-size: 20px;
        color: #aaa;
    }

    .autocomplete-block.alert {
        padding-right: 15px;
        padding-left: 15px;
    }

    .alert-autocomplete,
    .autocomplete-block.alert {
        margin-bottom: 0;
    }

    .google-autocomplete-component {
        margin: 15px 0;
    }
</style>

<script>

    import {library} from '@fortawesome/fontawesome-svg-core';
    import {faExclamationTriangle, faSearchLocation} from '@fortawesome/free-solid-svg-icons';
    import {FontAwesomeIcon as FaIcon} from '@fortawesome/vue-fontawesome';

    library.add(faExclamationTriangle, faSearchLocation)

    export default {
        props: {
            inputPlaceholder: {
                default: function () {
                    return 'Input your address';
                }
            },
            invalidMessage: {
                default: function () {
                    return 'Please, enter valid Places API key at tab Settings';
                }
            },
        },
        created() {
            this.$root.bus.$on('google-map-autocomplete-ready', (data) => {
                this.isValidAPIKey = data.status;
                this.init();
            });
        },
        data() {
            return {
                autocompleteInput: '',
                autocomplete: null,
                isValidAPIKey: false,
                key: +(new Date),
            };
        },
        methods: {
            init() {

                this.autocomplete = null;
                this.key          = +(new Date);

                this.$nextTick(() => {

                    var autocomplete = new google.maps.places.Autocomplete(
                        this.$refs.autocomplete_address.$el,
                        {types: ['geocode']}
                    );

                    autocomplete.addListener('place_changed', () => {
                        this.onChanged(autocomplete.getPlace());
                    });

                    this.autocomplete = autocomplete;
                });
            },
            onChanged(place) {

                var componentForm = {
                    street_number: 'short_name',
                    route: 'long_name',
                    locality: 'long_name',
                    administrative_area_level_1: 'long_name',
                    administrative_area_level_2: 'long_name',
                    country: 'short_name',
                    postal_code: 'short_name',
                    postal_code_suffix: 'short_name',
                    sublocality_level_1: 'long_name',
                };

                var fillFieldsKeys = {
                    address_1: function (components) {
                        return [components.street_number, components.route]
                                    .filter((v) => typeof v !== 'undefined' && v.trim() !== '')
                                    .join(' ');
                    },
                    address_2: function (components) {
                        return '';
                    },
                    city: function (components) {
                        return components.locality || components.sublocality_level_1;
                    },
                    postcode: function (components) {
                        return [components.postal_code]
                                    .filter((v) => typeof v !== 'undefined' && v.trim() !== '')
                                    .join(' ');
                    },
                    country: function (components) {
                        return components.country;
                    },
                    state: function (components) {
                        return components.administrative_area_level_1 || components.administrative_area_level_2;
                    },
                };

                var dataComponents = {};

                // Get each component of the address from the place details
                // and fill the corresponding field on the form.
                for (var i = 0; i < place.address_components.length; i++) {
                    var addressType = place.address_components[i].types[0];
                    if (componentForm[addressType]) {
                        var val = place.address_components[i][componentForm[addressType]];
                        dataComponents[addressType] = val;
                    }
                }

                var fields = {};

                for (let fieldKey in fillFieldsKeys) {
                    fields[fieldKey] = fillFieldsKeys[fieldKey](dataComponents);
                }

                this.$emit('change', fields);

                this.clear();
            },
            clear() {
                this.autocompleteInput = '';
            },
        },
        components: {
            FaIcon,
        },
    }
</script>
