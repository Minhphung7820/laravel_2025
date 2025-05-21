import axios from 'axios'

export default {
  namespaced: true,
  state: () => ({
    user: null
  }),
  mutations: {
    setUser(state, user) {
      state.user = user
    },
    clearUser(state) {
      state.user = null
    }
  },
  actions: {
    async fetchUser({ commit }) {
      try {
        const res = await axios.get('/api/user')
        console.log(res);

        commit('setUser', res.data)
      } catch {
        commit('clearUser')
      }
    },
    async logout({ commit }) {
      await axios.post('/api/logout')
      commit('clearUser')
    }
  },
  getters: {
    isAuthenticated: state => !!state.user,
    currentUser: state => state.user
  }
}