import { createRouter, createWebHistory } from 'vue-router'
import store from '../store'

import MainLayout from '../layouts/MainLayout.vue'
import AuthLayout from '../layouts/AuthLayout.vue'

import Home from '../components/Home.vue'
import About from '../components/About.vue'
import Login from '../components/Login.vue'
//
import ListCustomer from '../components/customer/List.vue'
import CreateCustomer from '../components/customer/Create.vue'
import EditCustomer from '../components/customer/Edit.vue'
import DetailCustomer from '../components/customer/Detail.vue'
//
import ListSupplier from '../components/supplier/List.vue'
import CreateSupplier from '../components/supplier/Create.vue'
import EditSupplier from '../components/supplier/Edit.vue'
import DetailSupplier from '../components/supplier/Detail.vue'
//
import ListProduct from '../components/warehouse/product/List.vue'
import CreateProductVariable from '../components/warehouse/product/CreateVariable.vue'
import EditProductVariable from '../components/warehouse/product/EditVariable.vue'
import EditProductCombo from '../components/warehouse/product/EditCombo.vue'
import EditProductSingle from '../components/warehouse/product/EditSingle.vue'
import CreateProductCombo from '../components/warehouse/product/CreateCombo.vue'
//
import ListUnit from '../components/warehouse/unit/List.vue'
import CreateUnit from '../components/warehouse/unit/Create.vue'
import EditUnit from '../components/warehouse/unit/Edit.vue'
//
import ListBrand from '../components/warehouse/brand/List.vue'
import CreateBrand from '../components/warehouse/brand/Create.vue'
import EditBrand from '../components/warehouse/brand/Edit.vue'
//
import ListStock from '../components/warehouse/stock/List.vue'
import CreateStock from '../components/warehouse/stock/Create.vue'
import EditStock from '../components/warehouse/stock/Edit.vue'
//
import ListCategory from '../components/warehouse/category/List.vue'
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
    path: 'purchase/supplier',
    component: ListSupplier
  },
  {
    path: 'purchase/supplier/create',
    component: CreateSupplier
  },
  {
    path: 'purchase/supplier/:id/edit',
    component: EditSupplier
  },
  {
    path: 'purchase/supplier/:id/detail',
    component: DetailSupplier
  },
  {
    path: 'warehouse/product',
    component: ListProduct
  },
  {
    path: 'warehouse/product/create/variable',
    component: CreateProductVariable
  },
  {
    path: 'warehouse/product/edit/:id/combo',
    component: EditProductCombo
  },
  {
    path: 'warehouse/product/edit/:id/variable',
    component: EditProductVariable
  },
  {
    path: 'warehouse/product/edit/:id/single',
    component: EditProductSingle
  },
  {
    path: 'warehouse/unit',
    component: ListUnit
  },
  {
    path: 'warehouse/unit/create',
    component: CreateUnit
  },
  {
    path: 'warehouse/unit/:id/edit',
    component: EditUnit
  },
  {
    path: 'warehouse/brand',
    component: ListBrand
  },
  {
    path: 'warehouse/brand/create',
    component: CreateBrand
  },
  {
    path: 'warehouse/brand/:id/edit',
    component: EditBrand
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
  },
  {
    path: 'warehouse/stock/create',
    component: CreateStock
  },
  {
    path: 'warehouse/stock/:id/edit',
    component: EditStock
  },
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