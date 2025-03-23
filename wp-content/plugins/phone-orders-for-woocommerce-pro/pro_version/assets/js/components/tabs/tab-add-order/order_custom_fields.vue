<template>
    <custom-fields
            v-bind:id="'order_custom_fields'"
            v-bind:storedFields="storedFields"
            v-bind:fieldList="fieldList"
            v-bind:dateFormat="dateFormat"
            v-bind:singularClassName="'order-footer__custom_field'"
            v-bind:pluralClassName="'order-footer__custom_fields'"
            @fieldsUpdated="fieldsUpdated"
    ></custom-fields>
</template>

<style>
    .postbox.disable-on-order .order-footer__custom_fields .date-picker {
        width: 70%;
    }

</style>

<script>
	import customFields from './custom_fields.vue';

	export default {
		props: {
			dateFormat: {
				default: function () {
					return "YYYY-MM-DD"
				}
			},
		},
		computed: {
			fieldList() {

				if ( ! this.getSettingsOption( 'order_custom_fields' ) ) {
					return [];
				}

				return this.getCustomFieldsList( this.getSettingsOption( 'order_custom_fields' ) );
			},
			storedFields() {
				return this.$store.state.add_order.cart.custom_fields;
			},
		},
		watch: {
			fieldList( newVal, oldVal ) {
				this.$store.commit(
					'add_order/setDefaultCartCustomFields',
					Object.assign( {}, this.getDefaultCustomFieldsValues( newVal ) )
				);
			},
		},
		methods: {
			fieldsUpdated( newVal ) {
                this.$store.commit( 'add_order/setCartCustomFields', newVal );
			},
		},
		components: {
			customFields,
		},
	}
</script>