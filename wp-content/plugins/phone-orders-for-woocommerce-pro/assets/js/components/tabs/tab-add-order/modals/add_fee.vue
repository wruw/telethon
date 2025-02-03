<template>
    <div>
        <b-modal id="addFee"
                 ref="modal"
                 :title="addFeeLabel"
                 @shown="shown"
                 size="lg"
        >
            <b-form inline @submit.stop.prevent="submit">

                <label class="mr-sm-2">{{ feeNameLabel }}</label>
                <b-form-input
                    ref="feeName"
                    type="text"
                    class="mb-2 mr-sm-2 mb-sm-0"
                    v-model.trim="elFeeName"
                    required
                >
                </b-form-input>

                <label class="mr-sm-2">{{ feeAmountLabel }}</label>
                <b-form-input
                    type="text"
                    ref="feeAmount"
                    class="mb-2 mr-sm-2 mb-sm-0"
                    v-model.trim="elFeeAmount"
                    required
                >
                </b-form-input>
                <b-button type="submit" v-show="false"></b-button>
            </b-form>
            <div slot="modal-footer">
                <b-button @click="close">{{ cancelLabel }}</b-button>
                <b-button @click="apply" variant="primary" :disabled="!isAllowedSubmit">{{ applyLabel }}</b-button>
            </div>
        </b-modal>
    </div>
</template>

<script>

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
			addFeeLabel: {
				default: function () {
					return 'Add Fee';
				}
			},
			feeNameLabel: {
				default: function () {
					return 'Fee name';
				}
			},
			feeAmountLabel: {
				default: function () {
					return 'Fee amount';
				}
			},
		},
		data: function () {
                    return {
                        elFeeName: '',
                        elFeeAmount: '',
                    };
		},
                computed: {
                    feeName: function () {
                        return this.getSettingsOption('default_fee_name');
                    },
                    feeAmount: function () {
                        return this.getSettingsOption('default_fee_amount');
                    },
                    isValidFeeName: function () {
                        return !!this.elFeeName.length;
                    },
                    isValidFeeAmount: function () {
                        return (parseFloat(this.elFeeAmount) < 0.0)  || (parseFloat(this.elFeeAmount) > 0.0);
                    },
                    isAllowedSubmit: function () {
                        return this.isValidFeeName && this.isValidFeeAmount;
                    },
                },
		methods: {
			apply() {
                            this.$store.commit('add_order/addFeeItem', {name: this.elFeeName, amount: this.elFeeAmount});
                            this.close();
			},
			shown( e ) {
                            this.elFeeName = this.feeName;
                            this.elFeeAmount = this.feeAmount;
                            this.$refs.feeName.focus();
			},
                        close() {
                            this.$refs.modal.hide();
			},
                        submit() {

                            if (this.isAllowedSubmit) {
                                this.apply();
                                return;
                            }

                            if (!this.isValidFeeName) {
                                this.$refs.feeName.focus();
                                return;
                            }

                            if (!this.isValidFeeAmount) {
                                this.$refs.feeAmount.focus();
                                return;
                            }
			},
		},
	}
</script>
