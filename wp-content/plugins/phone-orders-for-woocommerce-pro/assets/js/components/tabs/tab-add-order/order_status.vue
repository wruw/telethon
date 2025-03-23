<template>
    <div class="postbox disable-on-order">
        <h2>
            <span>{{ title }}</span>
        </h2>
        <div class="order-status-select">
            <multiselect
                    :allow-empty="false"
                    :hide-selected="true"
                    :searchable="false"
                    style="width: 100%;max-width: 800px;"
                    label="title"
                    v-model="statusOrder"
                    :options="orderStatusesList"
                    track-by="value"
                    :show-labels="false"
                    @input="onChange"
                    :disabled="!cartEnabled"
            ></multiselect>
        </div>
    </div>
</template>

<style>
    .postbox.disable-on-order .order-status-select {
        padding: 5px;
    }
</style>


<script>

    import Multiselect from 'vue-multiselect';

	export default {
		props: {
			title: {
				default: function () {
					return 'Order status';
				}
			},
            orderStatusesList: {
                default: function() {
                    return [];
                },
            },
		},
		data: function () {
			return {
                statusOrder: this.getObjectByKeyValue(this.orderStatusesList, 'value', this.orderStatusOption),
			};
		},
		watch: {
            storedOrderStatus( newVal, oldVal ) {
                if (this.showOrderStatus) {
                    this.statusOrder = this.getObjectByKeyValue(this.orderStatusesList, 'value', newVal);
                } else {
                    this.statusOrder = this.getObjectByKeyValue(this.orderStatusesList, 'value', this.orderStatusOption);
                    this.onChange();
                }
            },
            orderStatusOption(newVal, oldVal) {
                this.statusOrder = this.getObjectByKeyValue(this.orderStatusesList, 'value', newVal);
                this.onChange();
            },
		},
		computed: {
			storedOrderStatus: {
				get: function () {
					return this.$store.state.add_order.order_status;
				},
				set: function ( newVal ) {
					this.$store.commit( 'add_order/updateOrderStatus', newVal );
				},
			},
            showOrderStatus () {
                return this.getSettingsOption('show_order_status');
            },
            orderStatusOption () {
                return this.getSettingsOption('order_status');
            },
		},
		methods: {
            onChange: function () {
                this.storedOrderStatus = this.getKeyValueOfObject(this.statusOrder, 'value');
            },
		},
		components: {
            Multiselect,
		},
	}
</script>