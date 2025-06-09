function createLimitedMap(limit = 20) {
  const map = new Map()
  map.setLimit = function (key, value) {
    if (map.has(key)) map.delete(key)
    map.set(key, value)
    if (map.size > limit) {
      const oldestKey = map.keys().next().value
      map.delete(oldestKey)
    }
  }
  return map
}

export default {
  namespaced: true,
  state: () => ({
    cacheLimit: 20,
    customer: createLimitedMap(20),
    supplier: createLimitedMap(20),
    product: createLimitedMap(30)
  }),
  mutations: {
    setCache(state, { module, key, data }) {
      state[module].setLimit(key, data)
    },
    clearModuleCache(state, module) {
      state[module] = createLimitedMap(state.cacheLimit)
    },
    clearCacheKey(state, { module, key }) {
      if (state[module] && state[module].has(key)) {
        state[module].delete(key)
      }
    }
  },
  actions: {
    setCache({ commit }, { module, key, data }) {
      commit('setCache', { module, key, data })
    },
    clearModuleCache({ commit }, module) {
      commit('clearModuleCache', module)
    },
    clearCacheKey({ commit }, payload) {
      commit('clearCacheKey', payload)
    }
  },
  getters: {
    getCache: (state) => (module, key) => {
      return state[module] && state[module].get(key) || null
    },
    getAllCacheKeys: (state) => (module) => {
      return state[module] ? Array.from(state[module].keys()) : []
    }
  }
}