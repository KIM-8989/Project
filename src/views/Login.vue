<template>
      <div class="flex flex-col items-center mb-8">
        <div class="container mt-5" style="max-width:400px;">
        <h3 class="text-center mb-4">🔐 เข้าสู่ระบบผู้ดูแล</h3>
        </div>
      </div>
      <form @submit.prevent="handleLogin" class="container mt-5" style="max-width:400px;">
        <div>
          <div class="card p-5 shadow">
            <label class="form-label">ชื่อผู้ใช้</label>
            <input v-model="username" type="text" class="form-control" />
            <label class="form-label">รหัสผ่าน</label>
            <input v-model="password" type="password" class="form-control" />
            <label class="block text-gray-700 font-medium mb-2 text-sm">ประเภทผู้ใช้</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">⚙️</span>
            <select
              v-model="role">
              <option value="customer">ลูกค้า</option>
              <option value="admin">ผู้ดูแลระบบ</option>
            </select>
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none ">▼</span>
            <label class="form-label">! กรุณาเลือกผู้ใช้ </label>
            <button @click="login" class="btn btn-primary w-100 mt-100 ">เข้าสู่ระบบ</button>
        </div>
    </div>
</div>
        <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-center text-sm animate-shake mt-3">
          <span class="inline-block mr-2">❌</span>{{ errorMessage }}
        </div>
      </form>

      <!-- Additional Links -->
      <div class="mt-6 text-center text-sm text-gray-500">
        <a href="#" class="hover:text-blue-600 transition-colors duration-200">ลืมรหัสผ่าน?</a>
      </div>
    <!-- </div>
  </div> -->
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const username = ref('')
const password = ref('')
const role = ref('customer')
const errorMessage = ref('')

const handleLogin = async () => {
  try {
    const res = await axios.post('http://localhost:8081/Project/vue_php_api/login.php', {
      username: username.value,
      password: password.value,
      role: role.value
    })

    if (res.data.status === 'success') {
      sessionStorage.setItem('user', JSON.stringify(res.data.user))
      localStorage.setItem('user', JSON.stringify(res.data.user))
      if (res.data.user.role === 'admin' || role.value === 'admin') {
        localStorage.setItem('adminLogin', 'true')
      }
      window.location.href = '/'
    } else {
      errorMessage.value = res.data.message
    }
  } catch (err) {
    errorMessage.value = 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap');

* {
  font-family: 'Prompt', sans-serif;
}

/* Animations */
@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
.animate-slideUp {
  animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

/* @keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}
.animate-shake {
  animation: shake 0.4s ease-in-out;
} */
</style>
