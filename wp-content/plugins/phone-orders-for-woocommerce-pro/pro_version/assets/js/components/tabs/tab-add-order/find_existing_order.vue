<template>
    <div class="postbox" v-if="showFindOrders">
        <div id="find_order_div" class="disable-on-order">
            <span class="flex_block">
                {{ title }}
            </span>
            <multiselect
                :style="'width: ' + (showButtonCopyOrder ? 85 : 100 )+ '%;'"
                label="formated_output"
                v-model="order"
                :options="orderList"
                track-by="loaded_order_id"
                id="ajax"
                :placeholder="selectExistingOrdersPlaceholder"
                :loading="isLoading"
                :internal-search="false"
                :show-no-results="true"
                @search-change="asyncFind"
                :hide-selected="false"
                :searchable="true"
                open-direction="bottom"
                :disabled="!cartEnabled"
                @input="selectOrder"
                :allow-empty="false"
                @open="openSelectOrder"
                :show-labels="false"
            >
                <span slot="noResult">{{ noResultLabel }}</span>
                <template slot="singleLabel" slot-scope="props">
                    <span v-html="props.option.formated_output"></span>
                </template>
                <template slot="option" slot-scope="props">
                    <span v-html="props.option.formated_output"></span>
                </template>
            </multiselect>
            <span v-if="showButtons && editButtonLabel">
                <input class="btn btn-primary edit-order__button" type="button" :value="editButtonLabel" @click="editOrder" :disabled="!cartEnabled || !order">
            </span>
            <span v-if="showButtons && copyButtonLabel">
                <input class="btn btn-primary copy-order__button" type="button" :value="copyButtonLabel" @click="copyOrder" :disabled="!cartEnabled || !order">
            </span>
        </div>
        <div class="find-order-alert">
            <div v-if="editedOrderID">
                <b-alert
                    show
                    fade
                    variant="primary"
                >
                    <span>
                        {{ noticeEditedLabel }}
                    </span>
                    <a :href="base_admin_url + 'post.php?post=' + editedOrderID + '&action=edit'" target="_blank" v-if="availableEditOrderLink">
                        #{{ editedOrderID }}
                    </a>
		    <span v-else>
                        #{{ editedOrderID }}
		    </span>
               </b-alert>
            </div>
            <div v-else-if="draftedOrderID">
                <b-alert
                    show
                    fade
                    variant="warning"
                >
                    <span>
                        {{ noticeDraftedLabel }}
                    </span>
                </b-alert>
            </div>
            <div v-else-if="loadedOrderID">
                <b-alert
                    show
                    fade
                    variant="primary"
                >
                    <span>
                        {{ noticeLoadedLabel }}
                    </span>
                    <a :href="base_admin_url + 'post.php?post=' + loadedOrderID + '&action=edit'" target="_blank" v-if="availableEditOrderLink">
                        #{{ loadedOrderID }}
                    </a>
		    <span v-else>
                        #{{ loadedOrderID }}
		    </span>
                </b-alert>
            </div>
        </div>
    </div>
</template>

<style>
    #find_order_div .edit-order__button {
        margin-left: 10px;
        padding: 4px 30px;
    }
</style>

<script>

    import Multiselect from 'vue-multiselect';

    export default {
        props: {
            title: {
                default: function() {
                    return 'Find existing order';
                }
            },
            copyButtonForFindOrdersLabel: {
                default: function() {
                    return 'Copy order';
                }
            },
            editButtonForFindOrdersLabel: {
                default: function() {
                    return 'Edit order';
                }
            },
            noticeLoadedLabel: {
                default: function() {
                    return 'Current order was copied from order';
                }
            },
            noticeEditedLabel: {
                default: function() {
                    return 'You edit order';
                }
            },
            noticeDraftedLabel: {
                default: function() {
                    return 'You edit unfinished order';
                }
            },
            tabName: {
                default: function() {
                    return 'add-order';
                }
            },
            selectExistingOrdersPlaceholder: {
                default: function() {
                    return 'Type to search';
                }
            },
            noResultLabel: {
                default: function() {
                    return 'Oops! No elements found. Consider changing the search query.';
                }
            },
        },
        data: function () {
            return {
                order: null,
                orderList: [],
                isLoading: false,
                lastRequest: null,
            };
        },
        created: function() {
	        this.$root.bus.$on( 'app-loaded', () => {
	            this.$nextTick(() => {
                    let edit_order_id = this.removeGetParameter( "edit_order_id" );
                    if ( this.showEditButtonInWC ) {
                        if ( edit_order_id ) {
                            this.loadOrder( edit_order_id, true );
                        }
                    }
                });
	        } );
        },
        computed: {
            copyButtonLabel: function () {
                return this.order ? this.order.copy_button_value : this.copyButtonForFindOrdersLabel;
            },
            editButtonLabel: function () {
                return this.order ? this.order.edit_button_value : this.editButtonForFindOrdersLabel;
            },
            loadedOrderID: function () {
                return this.$store.state.add_order.cart.loaded_order_id;
            },
            cartEnabled: function () {
                return this.$store.state.add_order.cart_enabled && ! this.editedOrderID;
            },
            editedOrderID: function () {
                return this.$store.state.add_order.cart.edit_order_id;
            },
            draftedOrderID: function () {
                return this.$store.state.add_order.cart.drafted_order_id;
            },
            wpoCacheOrdersKey: function () {
                return this.getSettingsOption('cache_orders_session_key');
            },
	        showButtons: function () {
		        return this.getSettingsOption('button_for_find_orders');
	        },
	        showEditButtonInWC: function () {
		        return this.getSettingsOption( 'show_edit_order_in_wc' );
	        },
            showFindOrders: function () {
		        return !!!this.getSettingsOption( 'hide_find_orders' );
	        },
	        availableEditOrderLink: function () {
		        return typeof window.wpo_frontend === 'undefined';
	        },
        },
        methods: {
            selectOrder () {

                if (this.showButtons) {
                    return;
                }

                this.copyOrder();
            },
            copyOrder () {
	            this.loadOrder( this.order.loaded_order_id, false )
            },
            editOrder () {
	            this.loadOrder( this.order.loaded_order_id, true )
            },
	        loadOrder( order_id, is_edit ) {
		        this.isLoading = true;
		        this.$store.commit( 'add_order/setIsLoading', true );

		        this.axios.get( this.url, {
			        params: {
				        action: 'phone-orders-for-woocommerce',
				        method: 'load_order',
				        order_id: order_id,
				        tab: this.tabName,
				        is_edit: is_edit,
			        }
		        } ).then( ( response ) => {

			        this.$store.commit(
				        'add_order/setCartCustomFields',
				        Object.assign( {}, this.getDefaultCustomFieldsValues(
					        this.getCustomFieldsList( this.getSettingsOption( 'order_custom_fields' ) )
				        ), response.data.data.cart.custom_fields )
			        );

			        delete response.data.data.cart.custom_fields;

			        this.$store.commit( 'add_order/setCart', Object.assign( {
				        drafted_order_id: null,
				        edit_order_id: null,
			        }, response.data.data.cart ) );

			        delete response.data.data.cart;

			        this.$store.commit( 'add_order/setState', response.data.data );

			        this.updateStoredCartHash();

			        this.order = null;
			        this.isLoading = false;
			        this.$store.commit( 'add_order/setIsLoading', false );

		        }, () => {
			        this.isLoading = false;
		        } );
	        },
            openSelectOrder () {
                this.orderList = [];
            },
            asyncFind (query) {

                const CancelToken = this.axios.CancelToken;
                const source      = CancelToken.source();

                this.lastRequest && this.lastRequest.cancel();

                if (!query) {
                    this.isLoading   = false;
                    this.lastRequest = null;
                    this.orderList   = [];
                    return;
                }

                this.isLoading   = true;
                this.lastRequest = source;

                this.axios.get(this.url, {
                    params: {
                        action: 'phone-orders-for-woocommerce',
                        wpo_cache_orders_key: this.wpoCacheOrdersKey,
                        method: 'find_orders',
                        tab: this.tabName,
                        term: query,
                    },
                   cancelToken: source.token,
                }).then( ( response ) => {
                    this.orderList = response.data;
                    this.isLoading = false;
                }, (thrown) => {
                    if (!this.axios.isCancel(thrown)) {
                        this.isLoading = false;
                    }
                });
            },
        },
        components: {
            Multiselect,
        },
    }
</script>