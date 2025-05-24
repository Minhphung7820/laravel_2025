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
import DetailCustomer from '../components/customer/Detail.vue'

import ListProduct from '../components/warehouse/product/List.vue'
import ListUnit from '../components/warehouse/unit/List.vue'
import ListBrand from '../components/warehouse/brand/List.vue'
import ListCategory from '../components/warehouse/category/List.vue'
import ListStock from '../components/warehouse/stock/List.vue'
import CreateCategory from '../components/warehouse/category/Create.vue'
import EditCategory from '../components/warehouse/category/Edit.vue'

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
  },
  {
    path: 'sale/customer/:id/detail',
    component: DetailCustomer
  },
  {
    path: 'warehouse/product',
    component: ListProduct
  },
  {
    path: 'warehouse/unit',
    component: ListUnit
  },
  {
    path: 'warehouse/brand',
    component: ListBrand
  },
  {
    path: 'warehouse/category',
    component: ListCategory
  },
  {
    path: 'warehouse/category/create',
    component: CreateCategory
  },
  {
    path: 'warehouse/category/:id/edit',
    component: EditCategory
  },
  {
    path: 'warehouse/stock',
    component: ListStock
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