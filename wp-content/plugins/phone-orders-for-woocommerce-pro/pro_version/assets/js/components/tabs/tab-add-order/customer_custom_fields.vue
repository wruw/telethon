<template>
    <div class="col-12" v-if="Object.keys(fieldList).length !== 0">
        <hr>
        <strong><label class="mr-sm-2">{{ customFieldsLabel }}</label></strong>
        <custom-fields
                v-bind:id="'customer_custom_fields'"
                v-bind:storedFields="fields"
                v-bind:fieldList="fieldList"
                v-bind:dateFormat="dateFormat"
                v-bind:singularClassName="'customer-modal-footer__custom_field col-6'"
                v-bind:pluralClassName="'customer-modal-footer__custom_fields row'"
                @fieldsUpdated="fieldsUpdated"
        ></custom-fields>
    </div>
</template>

<style>
    .customer-modal-footer__custom_field .date-picker {
        width: 70%;
    }

    .customer-modal-footer__custom_field .wpo_custom_field,.customer-modal-footer__custom_field textarea {
        width: 100%;
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
			customFieldsLabel: {
				default: function () {
					return "Custom fields"
				}
			},
			empty: {
				default: function () {
					return true
				}
			},
		},
		created: function () {
			this.$root.bus.$on('edit-customer-address', (data) => {
                // manually trigger update to purge fields which not in option
				this.fieldsUpdated( this.fields );
			});
		},
		computed: {
			fieldList() {

				if ( ! this.getSettingsOption( 'customer_custom_fields' ) ) {
					return [];
				}

				return this.getCustomFieldsList( this.getSettingsOption( 'customer_custom_fields' ) );
			},
			storedFields() {
				return this.$store.state.add_order.cart.customer && ! this.empty ? this.$store.state.add_order.cart.customer.custom_fields : {};
			},
			availableFieldNames() {
				return this.fieldList.map(function(field) {
					return field.name;
				});
			},
			fields() {
				var result = {}, key;

				for ( key in this.storedFields ) {
					if ( this.storedFields.hasOwnProperty( key ) && this.availableFieldNames.indexOf( key ) !== -1 ) {
						result[key] = this.storedFields[key];
					}
				}

				return result;
			},
		},
		watch: {
			fieldList( newVal, oldVal ) {
				this.$store.commit(
					'add_order/setDefaultCustomerCustomFields',
					Object.assign( {}, this.getDefaultCustomFieldsValues( newVal ) )
				);
			},
		},
		methods: {
			fieldsUpdated( newVal ) {
				this.$root.bus.$emit( 'edit-customer-address-custom-fields-updated', Object.assign( {}, newVal ) );
			},
		},
		components: {
			customFields,
		},
	}
</script>