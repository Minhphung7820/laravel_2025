import { createStore } from 'vuex'
import auth from './auth'
import cache from './cache'
const store = createStore({
  modules: {
    auth,
    cache
  }
})

export default store