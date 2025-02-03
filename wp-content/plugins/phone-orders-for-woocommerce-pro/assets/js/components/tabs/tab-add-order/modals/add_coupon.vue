<template>
    <div>
        <b-modal id="addCoupon"
                 ref="modal"
                 :title="addCouponLabel"
                 size="sm"
                 @shown="shown"
        >
            <b-form inline>
                <multiselect
                    v-model="coupon"
                    :options="couponsList"
                    id="searchCouponsMultiselect"
                    :placeholder="typeToSearchLabel"
                    :loading="isLoading"
                    :internal-search="false"
                    :show-no-results="true"
                    @search-change="asyncFind"
                    :hide-selected="true"
                    :searchable="true"
                    open-direction="bottom"
                    @select="select"
                    ref="selectCoupon"
                    :show-labels="false"
                >

                    <span slot="noResult">{{noResultLabel}}</span>
                    <template slot="singleLabel" slot-scope="props">
                        <span v-html="props.option"></span>
                    </template>
                    <template slot="option" slot-scope="props">
                        <span v-html="props.option"></span>
                    </template>
                </multiselect>
            </b-form>
            <div slot="modal-footer">
                <b-button @click="close">{{ cancelLabel }}</b-button>
                <b-button @click="apply" variant="primary" :disabled="!coupon">{{ applyLabel }}</b-button>
            </div>
        </b-modal>
    </div>
</template>

<script>

    import Multiselect from 'vue-multiselect';

    export default {
        props: {
                cancelLabel: {
                        default: function () {
                                return 'Cancel';
                        }
                },
                applyLabel: {
                        default: function () {
                                return 'Apply';
                        }
                },
                addCouponLabel: {
                        default: function () {
                                return 'Add Coupon';
                        }
                },
                typeToSearchLabel: {
                        default: function () {
                                return 'Type to search';
                        }
                },
                tabName: {
                        default: function () {
                                return 'add-order';
                        }
                },
                noResultLabel: {
                        default: function () {
                                return 'Oops! No elements found. Consider changing the search query.';
                        }
                },

        },
	    data: function () {
		    return {
			    isLoading: false,
			    coupon: null,
			    couponsList: [],
			    lastRequest: null,
		    };
	    },
        computed: {
            excludedCouponList: function () {
                return this.$store.state.add_order.cart.coupons;
            },
            cacheCouponsSessionKey: function () {
                return this.getSettingsOption('cache_coupons_session_key');
            },
        },
        methods: {
                select(coupon) {
                    this.saveToStore(coupon);
                    this.close();
                },
                apply() {
                    this.saveToStore(this.coupon);
                    this.close();
                },
                saveToStore(coupon) {

                    if (!coupon) {
                        return;
                    }

                    this.$store.commit('add_order/addCouponItem', {title: coupon});
                },
                close() {
                    this.$refs.modal.hide();
                },
                asyncFind( query ) {

	                const CancelToken = this.axios.CancelToken;
	                const source      = CancelToken.source();

	                this.lastRequest && this.lastRequest.cancel();

	                if ( ! query && query !== null ) {
		                this.isLoading    = false;
		                this.lastRequest  = null;
		                return;
	                }

                    this.isLoading = true;
	                this.lastRequest = source;

	                this.axios.get( this.url, {
		                params: {
			                action: 'phone-orders-for-woocommerce',
			                method: 'get_coupons_list',
			                term: query,
			                exclude: this.excludedCouponList,
			                tab: this.tabName,
			                wpo_cache_coupons_key: this.cacheCouponsSessionKey,
		                },
		                cancelToken: source.token,
	                } ).then( ( response ) => {

		                var couponsList = [];

		                for ( var id in response.data ) {
			                couponsList.push( response.data[id].title );
		                }

		                this.couponsList = couponsList;

		                this.isLoading = false;
	                }, (thrown) => {
		                if (!this.axios.isCancel(thrown)) {
			                this.isLoading = false;
		                }
	                } );
                },
                shown() {
                    this.coupon = null;
                    this.couponsList = [];
                    this.$refs.selectCoupon.activate();
                },
        },
        components: {
                Multiselect,
        },
	}
</script>
