import { createStore } from 'vuex';

export default createStore({
    state: {
        user: null,
        roles: [],
    },
    mutations: {
        setUser(state, user) {
            state.user = user;
        },
        setRoles(state, roles) {
            state.roles = roles;
        },
    },
    actions: {
        initializeStore({ commit }, { user, roles }) {
            commit('setUser', user);
            commit('setRoles', roles);
        },
    },
});
