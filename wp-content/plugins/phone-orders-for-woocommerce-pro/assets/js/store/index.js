var Vue      = require('vue');
var Vuex     = require('vuex');
var order    = require('./modules/order');
var settings = require('./modules/settings');

Vue.use(Vuex);

module.exports = new Vuex.Store({
    modules: {
       add_order: order,
       settings: settings,
    },
})