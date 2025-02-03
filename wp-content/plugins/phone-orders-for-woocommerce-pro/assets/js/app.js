var Vue         = require( 'vue' );
var store       = require('./store');
var components  = require('./components');
var axios       = require('axios');
var qs          = require('qs');

var BootstrapVue = require('bootstrap-vue');

Vue.use( BootstrapVue );

var VueClipboard = require('vue-clipboard2');

Vue.use(VueClipboard);

Vue.filter("formatPrice", require('./filters/price'));

// Create a global mixin to expose strings, global config, and single backend resource.
Vue.mixin( {
    computed: {
        nonce: function() {
            return PhoneOrdersData.nonce;
        },
        edd_wpo_nonce: function() {
            return PhoneOrdersData.edd_wpo_nonce;
        },
        search_customers_nonce: function() {
            return PhoneOrdersData.search_customers_nonce;
        },
        url: function() {
            return PhoneOrdersData.ajax_url;
        },
        base_cart_url: function() {
            return PhoneOrdersData.base_cart_url;
        },
        base_admin_url: function() {
            return PhoneOrdersData.base_admin_url;
        },
        axios: function() {
            return axios;
        },
        qs: function() {
            return qs;
        },
    },
    methods: {
        getObjectByKeyValue (arrayOfObjects, key, value, defaultValue) {

            var found = typeof defaultValue !== 'undefined' ? defaultValue : null;

            arrayOfObjects.forEach(function (obj) {
                if (obj[key] === value) {
                    found = obj;
                }
            });

            return found;
        },
        getKeyValueOfObject (object, key) {
            return object && typeof object === 'object' ? object[key] : object;
        },
        openModal (modalID) {
            this.$root.$emit('bv::show::modal', modalID);
        },
        saveSettingsByEvent: function () {
            this.$root.bus.$emit('save-settings');
        },
        getWindowLocationHash: function() {
            return window.location.hash;
        },
        registerGoogleMapJs(key, successCallback, errorCallback) {

            document.querySelectorAll('script[src*="maps.google"]').forEach((script) => {
                script.remove();
            });

            if (typeof google === 'object') {
                google.maps = false;
            }

            window.gm_authFailure = errorCallback;

            var scriptTag = document.createElement('script');
            scriptTag.src = 'https://maps.googleapis.com/maps/api/js?key='+ key +'&libraries=places';

            scriptTag.onload             = successCallback;
            scriptTag.onreadystatechange = successCallback;
            scriptTag.async              = true;
            scriptTag.defer              = true;

            document.body.appendChild(scriptTag);
        },
	    removeGetParameter( parameterName ) {
		    var result = null,
			    clean_uri = null,
			    tmp = [];

		    location.search
		            .substr( 1 )
		            .split( "&" )
		            .forEach( function ( item ) {
			            tmp = item.split( "=" );
			            if ( tmp[0] === parameterName ) {
				            result = decodeURIComponent( tmp[1] );
				            clean_uri = window.location.toString().replace( "&" + tmp[0] + "=" + tmp[1], "");
				            clean_uri = clean_uri.replace( tmp[0] + "=" + tmp[1], "");
				            clean_uri = clean_uri.replace(/\?$/ig, "");
			            }
		            } );

		    if ( result && clean_uri ) {
			    window.history.replaceState({}, document.title, clean_uri)
            }
		    return result;
	    },
    }
} );

axios.interceptors.response.use( function ( response ) {
	if ( response.data.unexpected_output ) {
		console.log( response.data.unexpected_output )
	}
	return response;
} );

// Main Vue instance that bootstraps the frontend.
new Vue( {
    el: '#phone-orders-app',
    data: {
        bus: new Vue({}),
        defaultCountriesList: [],
        defaultStatesList: [],
    },
    created: function () {
        this.$root.$on('changed::tab', (instance, val, tab) => {

            if ( typeof tab['$children'][0] !== 'undefined') {
                if ( typeof tab['$children'][0].update !== 'undefined') {
                    tab['$children'][0].update();
                }
            }

            window.location = tab.href;
        });

        window.addEventListener('hashchange', () => {

            var hash = this.getWindowLocationHash();

            this.$refs.tabs.tabs.forEach((tab, index) => {
                if (tab.href === hash) {
                    this.$refs.tabs.setTab(index);
                }
            });
        });

        this.$root.bus.$on(['settings-loaded', 'settings-saved'], () => {
            this.loadCountryAndStatesList();
        });

        var initAutocomplete = () => {

            var self = this;

            this.registerGoogleMapJs(this.getSettingsOption('google_map_api_key'),  () => {

                var addressGeo = 'Stephansplatz 1 Vienna 1010 Austria';
                var geocoder   = new google.maps.Geocoder();

                geocoder.geocode( { 'address': addressGeo}, function(results, status) {

                    var success = false;

                    if (status === google.maps.GeocoderStatus.OK) {
                        success = true;
                    } else if (status === google.maps.GeocoderStatus.REQUEST_DENIED) {
                        //alert( 'ERROR: Access denied' );
                    } else {
                        //alert( 'ERROR: Error occured accessing the API.' );
                    }

                    self.bus.$emit('google-map-autocomplete-ready', {status: success});
                });
            }, () => {
                self.bus.$emit('google-map-autocomplete-ready', {status: false});
            });
        }

        this.$root.bus.$on(['settings-saved'], initAutocomplete);
        document.addEventListener('DOMContentLoaded', initAutocomplete);
    },
    mounted () {
        this.loadAllSettings(JSON.parse(this.$el.dataset['allSettings']));
        this.bus.$emit('app-loaded');
    },
    watch: {
        cart: {
            handler: function (newVal, oldVal) {
                if (this.$store.state.add_order.force_cart_set ) {
	                this.$store.commit('add_order/setForceCartSet', 0);
	                this.$store.commit('add_order/setCartParamsChangedByBackend', 0);
                    return;
                }
                console.log('changed')

                let localOldVal = JSON.parse(JSON.stringify(oldVal));
                let localNewVal = JSON.parse(JSON.stringify(newVal));

                let listen = ['items', 'coupons', 'customer', 'discount', 'shipping', 'fee'];

//                let excludeItemObjectKeys = ['custom_meta_fields', 'loaded_product', 'rand', 'key', 'original_price', 'item_cost' ];
                let excludeItemObjectKeys = ['custom_meta_fields' ];

                let newValListen = {};

                for (let key in Object.assign({}, localNewVal)) {
                    if (listen.indexOf(key) !== -1) {
                        newValListen[key] = localNewVal[key];
                    }
                }

                let oldValListen = {};

                for (let key in Object.assign({}, localOldVal)) {
                    if (listen.indexOf(key) !== -1) {
                        oldValListen[key] = localOldVal[key];
                    }
                }

                oldValListen['items'].forEach((item) => {
                    excludeItemObjectKeys.forEach((key) => {
                        typeof item[key] !== 'undefined' && delete item[key];
                    });
                });

                newValListen['items'].forEach((item) => {
                    excludeItemObjectKeys.forEach((key) => {
                        typeof item[key] !== 'undefined' && delete item[key];
                    });
                });

                console.log(oldValListen, newValListen)

                console.log('change')

                if (JSON.stringify(newValListen) === JSON.stringify(oldValListen) || this.$store.state.add_order.cart_params_changed_by_backend) {
                    this.$store.commit('add_order/setCartParamsChangedByBackend', 0);
                    return;
                }

                console.log('emit')

                this.bus.$emit('recalculate-cart');
            },
            deep: true,
        },
    },
    computed: {
        cart: function () {
            return this.$store.state.add_order.cart;
        },
    },
    methods: {
        loadCountryAndStatesList () {
            this.axios.get(this.url, {params: {
                action: 'phone-orders-for-woocommerce',
                method: 'get_countries_and_states_list',
                tab: 'add-order',
                wpo_cache_references_key: this.getSettingsOption('cache_references_session_key'),
            }}).then( ( response ) => {
                this.defaultCountriesList = response.data.data.countries_list;
                this.defaultStatesList    = response.data.data.states_list;
            });
        },
        loadAllSettings(settings) {
            this.setAllSettings(settings);
            this.bus.$emit('settings-loaded');
        },
    },
    components,
    store,
} );

