<template>
    <div>
        <b-modal id="addCustomItemModal"
                 ref="modal"
                 :title="addCustomItemLabel"
                 size="lg"
                 @shown="shown"
        >
            <b-form @submit.stop.prevent="submit">
                <div>
                    <label class="col-4">{{ lineItemNameLabel }}
                        <b-form-input type="text" name="name" v-model.trim="name" ref="name" required></b-form-input>
                    </label>
                    <label class="col-2">{{ pricePerItemLabel }}
                        <b-form-input type="text" name="price" v-model.trim="price" ref="price" required></b-form-input>
                    </label>
                    <label class="col-2">{{ quantityLabel }}
                        <b-form-input type="text" name="quantity" v-model.trim="quantity" ref="quantity" required></b-form-input>
                    </label>
                    <b-button type="submit" v-show="false"></b-button>
                </div>

                <div>
                    <label class="col-2" v-if="isShowSKU" >{{ skuNameLabel }}
                        <b-form-input type="text" name="sku" v-model.trim="sku" ref="sku"></b-form-input>
                    </label>
                    <label class="col-4" v-if="isShowTaxClass" >{{ taxClassLabel }}
                        <multiselect
                                :allow-empty="false"
                                :hide-selected="true"
                                :searchable="false"
                                style="width: 100%;max-width: 800px;"
                                label="title"
                                v-model="taxClass"
                                :options="taxClasses"
                                track-by="slug"
                                :show-labels="false"
                                ref="taxClass"
                        ></multiselect>
                    </label>
                </div>

            </b-form>

            <div slot="modal-footer">
                <b-button @click="close">{{ cancelLabel }}</b-button>
                <b-button @click="save" variant="primary" :disabled="!isAllowedSubmit">
                    {{ saveLabel }}
                </b-button>
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
			saveLabel: {
				default: function () {
					return 'Save';
				}
			},
			addCustomItemLabel: {
				default: function () {
					return 'Add custom item';
				}
			},
			skuNameLabel: {
				default: function () {
					return 'SKU';
				}
			},
			taxClassLabel: {
				default: function () {
					return 'Tax class';
				}
			},
			lineItemNameLabel: {
				default: function () {
					return 'Line item name';
				}
			},
			pricePerItemLabel: {
				default: function () {
					return 'Price per item';
				}
			},
			quantityLabel: {
				default: function () {
					return 'Quantity';
				}
			},
			skuName: {
				default: function () {
					return '';
				}
			},
			lineItemName: {
				default: function () {
					return '';
				}
			},
			pricePerItem: {
				default: function () {
					return '';
				}
			},
			quantityItems: {
				default: function () {
					return '';
				}
			},
            itemTaxClasses: {
				default: function () {
					return [];
				}
			},
			tabName: {
				default: function () {
					return 'add-order';
				}
			},
		},
		data: function () {
			return {
				sku: this.sku,
				taxClass: this.getObjectByKeyValue(this.itemTaxClasses, 'slug', this.itemTaxClass),
				taxClasses: this.itemTaxClasses,
				name: this.lineItemName,
				price: this.pricePerItem,
				quantity: this.quantityItems,
				tab: this.tabName,
			};
		},
		computed: {
			isValidName() {
				return ! ! this.name.length;
			},
			isValidPrice() {
				return parseFloat( this.price ) >= 0.0;
			},
			isValidQuantity() {
				return parseInt( this.quantity ) > 0;
			},
			isAllowedSubmit() {
				return this.isValidName && this.isValidPrice && this.isValidQuantity;
			},
			isShowSKU() {
				return this.getSettingsOption( 'new_product_ask_sku' );
			},
			isShowTaxClass() {
				return this.getSettingsOption( 'new_product_ask_tax_class' );
			},
			itemTaxClass() {
				return this.getSettingsOption( 'item_tax_class' );
			},

		},
		methods: {
			close() {
				this.$refs.modal.hide();
			},
			shown() {

				this.sku = this.skuName;
				this.taxClass = this.getObjectByKeyValue(this.itemTaxClasses, 'slug', this.itemTaxClass);
				this.name = this.lineItemName;
				this.price = this.pricePerItem;
				this.quantity = this.quantityItems;

                this.$refs.name.focus();
			},
			submit() {

				if ( this.isAllowedSubmit ) {
					this.save();
					return;
				}

				if ( ! this.isValidName ) {
					this.$refs.name.focus();
					return;
				}

				if ( ! this.isValidPrice ) {
					this.$refs.price.focus();
					return;
				}

				if ( ! this.isValidQuantity ) {
					this.$refs.quantity.focus();
					return;
				}

			},
			save() {
				this.createItem();
				this.close();
			},
			createItem: function () {

				let $args = {
					action: 'phone-orders-for-woocommerce',
					method: 'create_item',
					_wp_http_referer: this.referrer,
					_wpnonce: this.nonce,
					tab: this.tabName,
					data: {
						sku: this.sku,
						tax_class: this.taxClass,
						name: this.name,
						price: this.price,
						quantity: this.quantity,
					},
				};

				this.axios.post( this.url, this.qs.stringify( $args ) ).then( ( response ) => {
					this.addProductItemToStore( response.data.data.item );
				} );
			},
		},
		components: {
			Multiselect,
		},
	}
</script>
