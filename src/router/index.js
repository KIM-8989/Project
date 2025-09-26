import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/about',
    name: 'about',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "about" */ '../views/AboutView.vue')
  }
  ,
  {
    path: '/showproduct',
    name: 'showproduct',
    component: () => import(/* webpackChunkName: "about" */ '../views/ShowProduct.vue')
  }
  ,
  {
    path: '/Customers',
    name: 'Customers',
    component: () => import( '../views/Customers.vue')
  }
   ,
   {
    path: '/Student',
    name: 'Student',
    component: () => import( '../views/student.vue')
  }
   ,
  {
    path: '/add_customers',
    name: 'add_customers',
    component: () => import(/* webpackChunkName: "about" */ '../views/Add_customer.vue')
  } ,
  {
    path: '/add_student',
    name: 'add_student',
    component: () => import(/* webpackChunkName: "about" */ '../views/add_student.vue')
  } ,
  {
    path: '/add_products',
    name: 'add_products',
    component: () => import(/* webpackChunkName: "about" */ '../views/Add_product.vue')
  } ,
  
  {
    path: '/product',
    name: 'product',
    component: () => import(/* webpackChunkName: "about" */ '../views/Product.vue')
  }
  
  

]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
