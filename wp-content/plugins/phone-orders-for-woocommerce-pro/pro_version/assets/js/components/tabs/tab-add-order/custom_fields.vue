<template>
    <div :class="pluralClassName">
        <div :class="singularClassName" v-for="(field, index) in fieldList">

            <template v-if="isAllowedFieldType(field.type)">
                <label :for="'field_' + fieldName(field,index)" class="wpo_custom_field" :data-field_name="field.name">
                    <strong>{{ field.label }}</strong>
                </label>
            </template>

            <template v-if="field.type === 'hidden'">
                <input type="text" readonly :id="'field_' + fieldName(field,index)" :name="fieldName(field,index)" v-model="fields[field.name]"
                       v-bind:disabled="!cartEnabled">
            </template>

            <template v-if="field.type === 'text'">
                <textarea :id="'field_' + fieldName(field,index)" :class="inputClassName" rows=2    cols=20
                          v-model.lazy="fields[field.name]"
                          v-bind:disabled="!cartEnabled"></textarea>
            </template>

            <template v-if="field.type === 'select'">
                <multiselect
                        :class="inputClassName"
                        :id="'field_' + fieldName(field,index)"
                        :allow-empty="false"
                        v-model="fields[field.name]"
                        :hide-selected="true"
                        :searchable="false"
                        style="width: 100%;"
                        :options="field.value.length ? field.value : []"
                        :disabled="!cartEnabled"
                        :show-labels="false"
                ></multiselect>
            </template>

            <template v-if="field.type === 'radio'">
                <template v-for="option in field.value">
                    <input type="radio" :class="inputClassName" :id="option" :name="fieldName(field,index)" v-model="fields[field.name]"
                           :value="option" v-bind:disabled="!cartEnabled">
                    <label :for="option">{{ option }}</label>
                    <span></span>
                </template>
            </template>

            <template v-if="field.type === 'checkbox'">
                <template v-for="option in field.value">
                    <input type="checkbox" :class="inputClassName" :id="option + '_field_' + fieldName(field,index)" :name="option + '_name_' + fieldName(field,index)"
                           v-model="fields[field.name]" :value="option" v-bind:disabled="!cartEnabled">
                    <label :for="option + '_field_' + fieldName(field,index)">
                        {{ option }}
                    </label>
                    <span></span>
                </template>
            </template>

            <template v-if="field.type === 'date'">
                <div class="date-picker">
                    <datepicker
                            v-model="fields[field.name]"
                            v-bind:disabled="!cartEnabled"
                            :format="formatter"
                            :class="inputClassName"
                            :id="option + '_field_' + fieldName(field,index)"
                            :name="fieldName(field,index)"
                    ></datepicker>
                </div>
                <br class="clear">
            </template>
        </div>
    </div>
</template>

<script>

	import Multiselect from 'vue-multiselect';
	import Datepicker from 'vuejs-datepicker';
	import moment from 'moment'

	export default {
		props: {
			id: {
				default: function () {
					return ""
				}
			},
			dateFormat: {
				default: function () {
					return "YYYY-MM-DD"
				}
			},
			storedFields: {
				default: function () {
					return []
				}
			},
			fieldList: {
				default: function () {
					return []
				}
			},
			singularClassName: {
				default: function () {
					return ""
				}
			},
			pluralClassName: {
				default: function () {
					return ""
				}
			},
			inputClassName: {
				default: function () {
					return "custom-field-input"
				}
			},
		},
		watch: {
			storedFields( newVal, oldVal ) {
				let fields = Object.assign( {}, newVal );

				this.fieldList.forEach( function ( field ) {
					if ( field.type === 'checkbox' ) {
						// convert single checkbox value to empty array of checkboxes
                        // convert array of checkboxes to boolean True if array is not empty
						if ( field.value.length === 1 ) {
							if ( fields[field.name] ) {
								fields[field.name] = Array.isArray(fields[field.name]) ? !! fields[field.name].length : fields[field.name] === "true";
                            } else {
								fields[field.name] = false;
                            }
						} else if ( field.value.length > 1 ) {
							if ( ! fields[field.name] || ! Array.isArray(fields[field.name]) ) {
								fields[field.name] = [];
							}
						}
					}

					if ( field.type === 'hidden' ) {
						fields[field.name] = typeof field.value[0] !== 'undefined' ? field.value[0] : '';
                    }
				} );

				fields = this.processDateFieldsToDate( fields );

				this.fields = fields;
			},
			fields: {
				handler: function ( newVal, oldVal ) {
					newVal = this.processDateFieldsToString( newVal );

					if ( JSON.stringify( newVal ) !== JSON.stringify( this.storedFields ) ) {
						this.$emit( 'fieldsUpdated', newVal )
					}
				},
				deep: true,
			},
		},
		data: function () {
			return {
				fields: {},
			};
		},
		methods: {
			fieldName( field, index ) {
                return this.id + '_' + field.name + '_' + index;
			},
			isAllowedFieldType( type ) {
				return ['text', 'radio', 'checkbox', 'select', 'date', 'hidden' ].indexOf( type ) !== - 1;
			},
			formatter( date ) {
				return moment( date ).format( this.dateFormat );
			},
			getDateFields() {
				return this.fieldList.map( function ( field ) {
					if ( field.type === 'date' ) {
						return field.name;
					}
				} );
			},
			processDateFieldsToDate( fields ) {
				let dateFields = this.getDateFields();

				fields = Object.assign( {}, fields );

				for ( let key in fields ) {
					if ( ! fields.hasOwnProperty( key ) || dateFields.indexOf( key ) === - 1 ) {
						continue;
					}

					let moment_date = moment( fields[key], this.dateFormat );

					if ( moment_date.isValid() ) {
						fields[key] = moment_date.toDate()
					}
				}

				return fields;
			},
			processDateFieldsToString( fields ) {
				let dateFields = this.getDateFields();

				fields = Object.assign( {}, fields );

				for ( let key in fields ) {
					if ( ! fields.hasOwnProperty( key ) || dateFields.indexOf( key ) === - 1 ) {
						continue;
					}

					let moment_date = moment( fields[key] );

					if ( moment_date.isValid() ) {
						fields[key] = moment_date.format( this.dateFormat )
					}
				}

				return fields;
			},
		},
		components: {
			Multiselect,
			Datepicker,
		},
	}
</script>