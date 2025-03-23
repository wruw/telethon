<template>
    <tr class="item new_row">
	<template v-if="item.wpo_readonly_child_item">
	    <td class="item__wpo-readonly-child-item" colspan="6">
		<table>
		    <tbody>
			<tr>
			    <td class="related">
				<fa-icon icon="reply" class="reply-icon"/>
			    </td>
			    <td class="thumb">
				<div class="wc-order-item-thumbnail">
				    <img :src="item.thumbnail" class="attachment-thumbnail size-thumbnail wp-post-image" height="80" width="80">
				</div>
			    </td>
			    <td class="name">
				<a v-if="productLink" target="_blank" :href="productLink" class="wc-order-item-name">
				    {{ item.name }}
				</a>
				<div v-else class="wc-order-item-name">
				    {{ item.name }}
				</div>
				<div class="wc-order-item-name" v-if="item.is_subscribed" v-html="item.product_price_html"></div>
				<div class="wc-order-item-sku" v-if="item.sku">
				    <strong>
					{{ skuLabel }}:
				    </strong>
				    {{ item.sku }}
				</div>
				<div class="wc-order-item-variation" v-if="item.variation_id">
				    <strong>
					{{ variationIDLabel }}:
				    </strong>
				    {{ item.variation_id }}
				</div>
				 <div class="item-msg">
				     {{
					 item.in_stock === null || item.in_stock > tmpQty ?
					     ''
					 :
					     productStockMessage.replace('%s', item.in_stock)
				     }}
				 </div>
			    </td>
			     <td colspan="3">&nbsp;</td>
			</tr>
		    </tbody>
		</table>
	    </td>
	</template>
	<template v-else>
	    <td class="thumb">
		<div class="wc-order-item-thumbnail">
		    <div class="wc-order-item-thumbnail">
			<img :src="item.thumbnail" class="attachment-thumbnail size-thumbnail wp-post-image" height="80" width="80">
		    </div>
		</div>
	    </td>
	    <td class="name">
		<a v-if="productLink" target="_blank" :href="productLink" class="wc-order-item-name">
		    {{ item.name }}
		</a>
		<div v-else class="wc-order-item-name">
		    {{ item.name }}
		</div>
		<div class="wc-order-item-name" v-if="item.is_subscribed" v-html="item.product_price_html"></div>
		<div class="wc-order-item-sku" v-if="item.sku">
		    <strong>
			{{ skuLabel }}:
		    </strong>
		    {{ item.sku }}
		</div>

		<div class="wc-order-item-variation" v-if="item.variation_id">
		    <strong>
			{{ variationIDLabel }}:
		    </strong>
		    {{ item.variation_id }}
		</div>
		<product-missing-attribute
			v-bind="productMissingAttributeLabels"
			v-for="(variation_attribute, index) in this.missingVariationAttributes"
			:key="variation_attribute.key"
			:index="index"
			:attribute = "Object.assign({}, variation_attribute)"
			:itemKey="item.key"
		></product-missing-attribute>

		<product-custom-meta-fields
		    v-bind="productCustomMetaFieldsLabels"
		    :fields="customMetaFields"
		    @update="updateCustomMetaFields"
		></product-custom-meta-fields>

		 <div class="item-msg">
		     {{
			 item.in_stock === null || item.in_stock > tmpQty ?
			     ''
			 :
			     productStockMessage.replace('%s', item.in_stock)
		     }}
		 </div>
	    </td>
	    <td class="item_cost">
		<div class="edit">
		    <template v-if="originalPrice">
			<div class="sale_price">
			    <del>
				{{ originalPrice | formatPrice(precision) }}
			    </del>
			    <ins>
				{{ cost | formatPrice(precision) }}
			    </ins>
			</div>
		    </template>
		    <template v-else-if="isReadOnly">
			<div class="readonly_price">
			    {{ cost | formatPrice(precision) }}
			</div>
		    </template>
		    <template v-else>
			<input type="text" autocomplete="off" placeholder="0" v-model.lazy="costModel" size="4" v-bind:disabled="!cartEnabled">
		    </template>
		    <div class="cost_with_tax" style="padding: 4px" v-if="costWithTax">
			{{ costWithTax | formatPrice(precision) }}
		    </div>
		</div>
	    </td>
	    <td class="quantity">
		<div class="edit">
		    <div v-if="soldIndividually" style="padding: 4px">
			{{ qty }}
		    </div>
		    <input v-else
			ref="qty"
			type="number"
			:step="item.qty_step"
			min="1"
			autocomplete="off"
			placeholder="0"
			v-model.number="tmpQty"
			size="4"
			class="qty"
			:disabled="!cartEnabled"
			:max="item.in_stock"
			@keyup.enter="openProductSearchSelect"
			@blur="changeQty"
			@mousedown="setFocus"
		    />
		</div>
	     </td>
	     <td class="line_total">
		 <div class="total" style="padding: 4px;">
		     {{ total | formatPrice(precision) }}
		 </div>
		 <div class="total_with_tax" v-if="totalWithTax" style="padding: 4px;">
		     {{ totalWithTax | formatPrice(precision) }}
		 </div>
	     </td>
	     <td class="wc-order-edit-line-item">
		 <div class="wc-order-edit-line-item-actions">
		     <a @click.prevent.stop="cartEnabled ? removeItem(item) : null" class="delete-order-item tips" href="#" :title="deleteProductItemButtonTooltipText"></a>
		 </div>
	     </td>
	 </template>
     </tr>
</template>

<style>
    .item_cost .readonly_price, .item_cost .sale_price {
        padding: 4px;
    }

    .item__wpo-readonly-child-item table {
	width: 100%;
    }

    #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items tbody tr td.item__wpo-readonly-child-item table tr td {
	border-width: 0;
    }

    #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items td.item__wpo-readonly-child-item {
	padding-left: 4em;
    }

    #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items td.item__wpo-readonly-child-item .reply-icon {
	font-size: 20px;
	vertical-align: text-top;
	transform: rotate(180deg);
	color: #ccc;
    }

    #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items td.item__wpo-readonly-child-item td.related {
	text-align: center;
    }

</style>

<script>

    import {library} from '@fortawesome/fontawesome-svg-core';
    import {faReply} from '@fortawesome/free-solid-svg-icons';
    import {FontAwesomeIcon as FaIcon} from '@fortawesome/vue-fontawesome';

    library.add(faReply)

    import ProductMissingAttribute from './product_missing_attribute.vue';
    import ProductCustomMetaFields from './product_custom_meta_fields.vue';

    export default {
        props: {
            item: {
                default: function() {
                    return {};
                }
            },
            calculated: {
                default: function() {
                    return {};
                }
            },
            deleteProductItemButtonTooltipText: {
                default: function() {
                    return 'Delete item';
                }
            },
            skuLabel: {
                default: function() {
                    return 'SKU';
                }
            },
            productStockMessage: {
                default: function() {
                    return 'Only %s items can be purchased';
                }
            },
            variationIDLabel: {
                default: function() {
                    return 'Variation ID';
                }
            },
            productMissingAttributeLabels: {
                default: function() {
                    return {};
                }
            },
            productCustomMetaFieldsLabels: {
                default: function() {
                    return {};
                }
            },
        },
        data: function () {
            return {
                cost: this.item.item_cost,
                qty: this.item.qty,
                tmpQty: this.item.qty,
                missingVariationAttributes: this.item.missing_variation_attributes,
                customMetaFields: this.item.custom_meta_fields,
            };
        },
        created: function () {
            this.$root.bus.$on( 'change-missing-attribute', ( data ) => {
            	if ( data.itemKey !== this.item.key ) {
            		return false;
                }

                if ( this.missingVariationAttributes && this.missingVariationAttributes.length ) {
                    var temp_items = [];
                    this.missingVariationAttributes.forEach( function ( current ) {
                        temp_items.push( Object.assign( {}, current ) );
                    } );

                    temp_items[data.attributeIndex].value = data.attributeValue;
                    this.missingVariationAttributes = temp_items;

                    this.updateItem();
                }
            });
        },
        watch: {
            cost (newVal) {
                this.autoRecalculate && this.$store.commit('add_order/setIsLoadingWithoutBackground', true);
                this.updateItem();
            },
            qty (newVal, oldVal) {
            	if ( newVal !== oldVal
                     &&
                     // when autoRecalculate is disabled, qty is not updating in store, because calculatedQty is empty
                     // add "not autoRecalculate" check
                     ( ( this.calculatedQty && this.calculatedQty !== newVal ) || ! this.autoRecalculate )
                ) {
                    this.autoRecalculate && this.$store.commit('add_order/setIsLoadingWithoutBackground', true);
                    this.updateItem();
                }
            },
	        calculatedQty (newVal) {
            	if ( newVal ) {
		            this.tmpQty = newVal;
		            this.qty = newVal;
                }
            }
        },
        computed: {
            costModel: {
                get () {
                    return this.$options.filters.formatPrice(this.cost, this.precision);
                },
                set (newVal) {
                    return this.cost = newVal;
                },
            },
            costWithTax () {
                return typeof this.calculated.item_cost_with_tax !== 'undefined' ? this.calculated.item_cost_with_tax : '';
            },
            calculatedQty () {
	            return typeof this.calculated.qty !== 'undefined' ? this.calculated.qty : 0;
            },
            total () {
                return this.cost * this.qty;
            },
            totalWithTax () {
                return typeof this.calculated.line_total_with_tax !== 'undefined' ? this.calculated.line_total_with_tax : '';
            },
	        soldIndividually () {
		        return typeof this.calculated.sold_individually !== 'undefined' ? this.calculated.sold_individually : (
			        typeof this.item.sold_individually !== 'undefined' ? this.item.sold_individually : false
		        );
	        },
	        isReadOnly () {
		        return typeof this.calculated.is_readonly_price !== 'undefined' ? this.calculated.is_readonly_price : (
			        typeof this.item.is_readonly_price !== 'undefined' ? this.item.is_readonly_price : false
		        );
	        },
            // price before pricing plugin was applied
	        originalPrice () {
		        let originalPrice = false;
		        if ( typeof this.calculated.original_price !== 'undefined' ) {
			        originalPrice = this.calculated.original_price;
			        if ( originalPrice !== false ) {
				        this.cost = this.calculated.item_cost;
                    }
		        } else {
			        originalPrice = typeof this.item.original_price !== 'undefined' ? this.item.original_price : false;
		        }

		        return originalPrice;
	        },
            precision () {
                return this.getSettingsOption('item_price_precision');
            },
            autoRecalculate () {
                return this.getSettingsOption('auto_recalculate');
            },
            productKey () {
                return this.item.key ? this.item.key : (this.item.variation_id ? this.item.variation_id : this.item.product_id);
            },
            productLink () {
                return typeof window.wpo_frontend === 'undefined' ? this.item.product_link : this.item.permalink;
            },
        },
        methods: {
            updateItem () {
                this.$root.bus.$emit('clear-calculated-item', this.productKey);
                this.setToStoreUpdatedItem();
            },
            setToStoreUpdatedItem() {
                this.$store.commit('add_order/updateCartItem', {
                    key: this.item.key,
                    item: Object.assign(this.item,
                        {
                            item_cost: this.cost,
                            qty: this.qty,
                            missing_variation_attributes: this.missingVariationAttributes,
	                        custom_meta_fields: this.customMetaFields,
                            sold_individually: this.soldIndividually,
	                        is_readonly_price: this.isReadOnly,
	                        original_price: this.originalPrice,
                        }
                    ),
                });
            },
            removeItem (item) {
                this.$root.bus.$emit('clear-calculated-item', item.key);
                this.$root.bus.$emit('clear-selected-item', item.variation_id ? item.variation_id : item.product_id);
                this.$store.commit('add_order/removeCartItem', item.key);
            },
            openProductSearchSelect () {
                this.$root.bus.$emit('open-search-product');
            },
            changeQty () {
                this.qty = this.tmpQty;
            },
            setFocus (e) {
                e.target.focus();
            },
            updateCustomMetaFields(data) {

                var tmp = [];

                data.custom_meta_fields.forEach((v) => {
                    tmp.push(Object.assign({}, v));
                });

                this.customMetaFields = tmp;
                this.setToStoreUpdatedItem();
            },
        },
	    components: {
		    ProductMissingAttribute,
		    ProductCustomMetaFields,
		    FaIcon,
	    },
    }
</script>