<template>
    <table class="form-table">
        <tbody>
	    <tr>
		<td colspan="2">
		   <b>{{ frontendPageInstructions }}</b>
		</td>
		</tr>
	    <tr>
		<td>
		    {{ frontendPageLabel }}
		</td>
		<td>
		    <input type="checkbox" class="option" v-model="elFrontendPage" name="frontend_page">
		</td>
	    </tr>
	    <tr v-show="elFrontendPage">
		<td>
		    {{ frontendPageUrlLabel }}
		</td>
		<td>
		    <input type="text" class="option" v-model="elFrontendPageUrl" name="frontend_page_url">
		</td>
	    </tr>
	    <tr>
		<td>
		    {{ hideThemeHeaderLabel }}
		</td>
		<td>
		    <input type="checkbox" class="option" v-model="elHideThemeHeader" name="frontend_hide_theme_header">
		</td>
	    </tr>
	    <tr>
		<td>
		    {{ hideThemeFooterLabel }}
		</td>
		<td>
		    <input type="checkbox" class="option" v-model="elHideThemeFooter" name="frontend_hide_theme_footer">
		</td>
	    </tr>
	</tbody>
    </table>
</template>

<script>

    export default {
        props: {
	    title: {
                default: function() {
                    return 'Run at frontend';
                },
            },
            frontendPageInstructions: {
                default: function() {
                    return 'The user must be admin or has capability "edit_shop_orders"';
                },
            },
            frontendPageLabel: {
                default: function() {
                    return 'Accept orders at frontend page';
                },
            },
            frontendPage: {
                default: function() {
                    return false;
                },
            },
            frontendPageUrlLabel: {
                default: function() {
                    return 'Frontend page url';
                },
            },
            frontendPageUrl: {
                default: function() {
                    return '';
                },
            },
            hideThemeHeaderLabel: {
                default: function() {
                    return 'Hide theme header';
                },
            },
            hideThemeHeader: {
                default: function() {
                    return false;
                },
            },
            hideThemeFooterLabel: {
                default: function() {
                    return 'Hide theme footer';
                },
            },
            hideThemeFooter: {
                default: function() {
                    return false;
                },
            },
        },
        data () {
            return {
		elFrontendPage: this.frontendPage,
		elFrontendPageUrl: this.frontendPageUrl,
		elHideThemeHeader: this.hideThemeHeader,
		elHideThemeFooter: this.hideThemeFooter,
            };
        },
        methods: {
            getSettings () {
                return {
                    frontend_page: this.elFrontendPage,
                    frontend_page_url: this.elFrontendPageUrl,
                    frontend_hide_theme_header: this.elHideThemeHeader,
                    frontend_hide_theme_footer: this.elHideThemeFooter,
                };
            },
        },
    }
</script>