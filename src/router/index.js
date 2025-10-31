import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
  path: "/login",
  name: "login",
  component: () => import("../views/Login.vue"),
  meta: { hideNavbar: true } // เพิ่มบรรทัดนี้
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
  },
  {
    path: '/edit_customers',
    name: 'EditCustomer',
    component: () => import(/* webpackChunkName: "about" */ '../views/EditCustomer.vue')
  },
  {
    path: '/edit_student',
    name: 'EditStudent',
    component: () => import(/* webpackChunkName: "about" */ '../views/EditStudent.vue')
  },
  {
  path: '/edit_product',
  name: 'EditProduct',
  component: () => import('../views/EditProduct.vue'),
  meta: { requiresAuth: true },
  },
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
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})
router.beforeEach((to, from, next) => {
  const isLoggedIn = localStorage.getItem("adminLogin") === "true";

  // ถ้าหน้านั้นต้องล็อกอินก่อน แต่ยังไม่ได้ล็อกอิน
  if (to.meta.requiresAuth && !isLoggedIn) {
    alert("⚠ กรุณาเข้าสู่ระบบก่อนใช้งานหน้านี้");
    next("/login");
  }
  // ถ้าเข้าสู่ระบบแล้วแต่พยายามกลับไปหน้า login อีก → ส่งกลับหน้าแรก
  else if (to.path === "/login" && isLoggedIn) {
    next("/");
  } 
  // อื่น ๆ ไปต่อได้ตามปกติ
  else {
    next();
  }
});


export default router
