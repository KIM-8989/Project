import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import ShowEmployee from '../views/ShowEmployee.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/show-employee',
    name: 'ShowEmployee',
    component: ShowEmployee
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
  path: '/employee',
  name: 'Employee',
  component: () => import('../views/Employee.vue')
  }
  ,
  {
  path: '/edit_employee',
  name: 'EditEmployee',
  component: () => import('../views/Edit_Employee.vue')
  },
  {
  path: '/add_employee',
  name: 'AddEmployee',
  component: () => import('../views/Add_Employee.vue')
  },
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})
router.beforeEach((to, from, next) => {
  const isLoggedIn = localStorage.getItem("adminLogin") === "true";

  if (to.meta.requiresAuth && !isLoggedIn) {
    alert("⚠ กรุณาเข้าสู่ระบบก่อนใช้งานหน้านี้");
    next("/login");
  }
  else if (to.path === "/login" && isLoggedIn) {
    next("/");
  } 
  // อื่น ๆ ไปต่อได้ตามปกติ
  else {
    next();
  }
});


export default router
