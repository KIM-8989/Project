<template>
  <nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
    <div class="container-fluid">
      <router-link class="navbar-brand" to="/">Final</router-link>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <!-- Home - ทุกคนเห็น -->
          <li class="nav-item">
            <router-link class="nav-link active" aria-current="page" to="/">Home</router-link>
          </li>
            
            <!-- เมนูสำหรับ Admin - แสดงทุกอย่างเหมือนเดิม -->

          <li class="nav-item">
            <router-link class="nav-link" to="/employee">Employee</router-link>
          </li>
          <router-link class="nav-link" to="/show-employee">Employee</router-link>
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