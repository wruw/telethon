var mixin = {
    methods: {
        getAllSettings() {
            return this.$store.getters['settings/getAll'];
        },
        setAllSettings(settings) {
            this.$store.commit('settings/setState', settings);
        },
        getSettingsOption(optionName, defaultValue) {
            var val = this.$store.getters['settings/get'](optionName);
            return typeof val !== 'undefined' ? val : defaultValue;
        },
        setSettingsOption(optionName, optionValue) {
            this.$store.commit('settings/set', {name: optionName, value: optionValue});
        },
        getSettingsTabs() {
            return this.$store.getters['settings/getTabs'];
        },
        addSettingsTab(tab) {
            this.$store.commit('settings/addTab', tab);
        },
        getSettingsCurrentTab() {
            return this.$store.getters['settings/getCurrentTab'];
        },
        setSettingsCurrentTab(tab) {
            this.$store.commit('settings/setCurrentTab', tab);
        },
        getComponentsSettings() {
            return this.$store.getters['settings/getComponentsSettings'];
        },
        setComponentsSettings(settings) {
            this.$store.commit('settings/setComponentsSettings', settings);
        },
    },
};

const state = {
    tabs: [],
    currentTab: null,
    settings: {},
    componentsSettings: {},
};

const getters = {
    get: function (state) {
        return function (option_name) {
            return state.settings[option_name];
        };
    },
    getAll: function (state) {
        return state.settings;
    },
    getTabs: function (state) {
        return state.tabs;
    },
    getCurrentTab: function (state) {
        return state.currentTab;
    },
    getComponentsSettings: function (state) {
        return state.componentsSettings;
    },
};

const mutations = {
    set: function (state, option) {
        state.settings[option.name] = option.value;
    },
    setState: function (state, newState) {
        for (var option in newState) {
            state.settings[option] = newState[option];
        }
    },
    addTab: function (state, tab) {
        state.tabs.push(tab)
    },
    setCurrentTab: function (state, tab) {
        state.currentTab = tab
    },
    setComponentsSettings: function (state, settings) {
        state.componentsSettings = Object.assign(state.componentsSettings, settings)
    },
};

var store = {
    namespaced: true,
    state,
    getters,
    mutations,
}

export {store, mixin}
