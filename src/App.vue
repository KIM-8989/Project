<template>
  <nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
    <div class="container-fluid">
      <router-link class="navbar-brand" to="/">Project</router-link>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <!-- Home - ทุกคนเห็น -->
          <li class="nav-item">
            <router-link class="nav-link active" aria-current="page" to="/">Home</router-link>
          </li>

          <!-- แสดงเฉพาะเมื่อ Login แล้ว -->
          <template v-if="isLoggedIn">
            
            <!-- เมนูสำหรับ Admin - แสดงทุกอย่างเหมือนเดิม -->
            <template v-if="userRole === 'admin'">
              <li class="nav-item">
                <router-link class="nav-link" to="/showproduct">Show Product</router-link>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Product
                </a>
                <ul class="dropdown-menu">
                  <li><router-link class="dropdown-item" to="/product">Product</router-link></li>
                  <li><router-link class="dropdown-item" to="/edit_product">Edit Customer</router-link></li>
                </ul>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Customers
                </a>
                <ul class="dropdown-menu">
                  <li><router-link class="dropdown-item" to="/Customers">Customer</router-link></li>
                  <li><router-link class="dropdown-item" to="/edit_customers">Edit Customer</router-link></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><router-link class="dropdown-item" to="/add_customers">Add Customer</router-link></li>
                </ul>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Employee
                </a>
                <ul class="dropdown-menu">
                  <li><router-link class="dropdown-item" to="/employee">Employee</router-link></li>
                  <li><router-link class="dropdown-item" to="/edit_employee">Edit Employee</router-link></li>
                </ul>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Student
                </a>
                <ul class="dropdown-menu">
                  <li><router-link class="dropdown-item" to="/Student">Student</router-link></li>
                  <li><router-link class="dropdown-item" to="/edit_student">Edit Student</router-link></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><router-link class="dropdown-item" to="/add_student">Add Student</router-link></li>
                </ul>
              </li>
            </template>

            <!-- เมนูสำหรับ Customer - แสดงเฉพาะ Show Product และ Product -->
            <template v-if="userRole === 'customer'">
              <li class="nav-item">
                <router-link class="nav-link" to="/showproduct">Show Product</router-link>
              </li>
              <li class="nav-item">
                <router-link class="nav-link" to="/product">Product</router-link>
              </li>
            </template>
          </template>

          <!-- เมนู Login/Logout -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              {{ isLoggedIn ? 'Account' : 'Login' }}
            </a>
            <ul class="dropdown-menu">
              <template v-if="!isLoggedIn">
                <li><router-link class="dropdown-item" to="/login">Login</router-link></li>
                <li><hr class="dropdown-divider"></li>
                <li><router-link class="dropdown-item" to="/add_customers">Register</router-link></li>
              </template>
              <template v-else>
                <li><a class="dropdown-item" href="#" @click.prevent="handleLogout">Logout</a></li>
              </template>
            </ul>
          </li>

          <!-- About - ทุกคนเห็น -->
          <li class="nav-item">
            <router-link class="nav-link" to="/about">About</router-link>
          </li>
        </ul>

        <!-- Search - แสดงเฉพาะเมื่อ Login แล้ว -->
        <form v-if="isLoggedIn" class="d-flex" role="search" @submit.prevent>
          <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search"/>
          <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
      </div>
    </div>
  </nav>

  <router-view/>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isLoggedIn = ref(false)
const userRole = ref('')

// ตรวจสอบสถานะ Login
const checkLoginStatus = () => {
  const user = sessionStorage.getItem('user') || localStorage.getItem('user')
  if (user) {
    try {
      const userData = JSON.parse(user)
      isLoggedIn.value = true
      userRole.value = userData.role || 'customer'
    } catch (e) {
      isLoggedIn.value = false
      userRole.value = ''
    }
  } else {
    isLoggedIn.value = false
    userRole.value = ''
  }
}

// Logout
const handleLogout = () => {
  sessionStorage.removeItem('user')
  localStorage.removeItem('user')
  localStorage.removeItem('adminLogin')
  isLoggedIn.value = false
  userRole.value = ''
  router.push('/login')
}

// เช็คสถานะเมื่อเริ่มต้น
onMounted(() => {
  checkLoginStatus()
  // อัพเดทสถานะเมื่อมีการเปลี่ยนแปลง
  router.afterEach(() => {
    checkLoginStatus()
  })
})
</script>

<style scoped>
/* เพิ่ม style ถ้าต้องการ */
</style>