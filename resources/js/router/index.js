import { createRouter, createWebHistory } from 'vue-router'
import store from '../store'

import MainLayout from '../layouts/MainLayout.vue'
import AuthLayout from '../layouts/AuthLayout.vue'

import Home from '../components/Home.vue'
import About from '../components/About.vue'
import Login from '../components/Login.vue'
import ListCustomer from '../components/customer/List.vue'
import CreateCustomer from '../components/customer/Create.vue'
import EditCustomer from '../components/customer/Edit.vue'

const routes = [{
  path: '/',
  component: MainLayout,
  meta: { requiresAuth: true },
  children: [{
    path: '',
    component: Home
  },
  {
    path: 'about',
    component: About
  },
  {
    path: 'sale/customer',
    component: ListCustomer
  },
  {
    path: 'sale/customer/create',
    component: CreateCustomer
  },
  {
    path: 'sale/customer/:id/edit',
    component: EditCustomer
  }
  ]
},
{
  path: '/login',
  component: AuthLayout,
  children: [
    { path: '', component: Login }
  ]
}
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  if (to.path !== '/login' && store.getters['auth/currentUser'] === null) {
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