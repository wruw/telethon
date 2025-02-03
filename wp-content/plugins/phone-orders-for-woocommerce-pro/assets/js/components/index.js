var components = {
    TabAddOrder: require( './tabs/add_order.vue' ),
    FindOrCreateCustomer: require( './tabs/tab-add-order/find_or_create_customer.vue' ),
    OrderDate: require( './tabs/tab-add-order/order_date.vue' ),
    OrderStatus: require( './tabs/tab-add-order/order_status.vue' ),
    TabSettings: require( './tabs/settings.vue' ),
    TabLog: require( './tabs/log.vue' ),
    TabHelp: require( './tabs/help.vue' ),
    OrderDetails: require( './tabs/tab-add-order/order_details.vue' ),
    BaseSettings: require( './tabs/tab-settings/settings.vue'),
    CommonSettings: require( './tabs/tab-settings/common_settings.vue'),
    InterfaceSettings: require( './tabs/tab-settings/interface_settings.vue'),
    CouponsSettings: require( './tabs/tab-settings/coupons_settings.vue'),
    ReferencesSettings: require( './tabs/tab-settings/references_settings.vue'),
};

try {
    components = Object.assign(components, require( './../../../pro_version/assets/js/components' ));
} catch (e) {}

module.exports = components;
