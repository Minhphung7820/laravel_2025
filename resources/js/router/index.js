import { createRouter, createWebHistory } from 'vue-router'
import store from '../store'

const routes = [{
  path: '/',
  component: () =>
    import('../components/Home.vue'),
  meta: { requiresAuth: true }
},
{
  path: '/about',
  component: () =>
    import('../components/About.vue'),
  meta: { requiresAuth: true }
},
{
  path: '/login',
  component: () =>
    import('../components/Login.vue')
}
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  if (store.getters['auth/currentUser'] === null) {
    await store.dispatch('auth/fetchUser')
  }

  const isLoggedIn = store.getters['auth/isAuthenticated']

  if (to.meta.requiresAuth && !isLoggedIn) {
    return next('/login')
  }

  if (to.path === '/login' && isLoggedIn) {
    return next('/')
  }

  next()
})

export default router