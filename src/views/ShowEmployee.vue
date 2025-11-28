<template>
  <div class="container mt-4">
    <h2 class="mb-4 text-center">รายชื่อพนักงาน</h2>
    
    <!-- Loading -->
    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">กำลังโหลดข้อมูล...</p>
    </div>

    <!-- Error -->
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <!-- Card Layout - 3 คอลัมน์ต่อแถว (ข้อ 3) -->
    <div v-if="!loading && !error" class="row g-4">
      <div 
        v-for="emp in employees" 
        :key="emp.emp_id" 
        class="col-12 col-md-6 col-lg-4"
      >
        <div class="card h-100 shadow-sm hover-shadow">
          <!-- รูปภาพ -->
          <div class="card-img-top-wrapper" style="height: 250px; overflow: hidden; background-color: #f8f9fa;">
            <img 
              v-if="emp.image" 
              :src="getImageUrl(emp.image)" 
              class="card-img-top"
              style="width: 100%; height: 100%; object-fit: cover;"
              :alt="emp.first_name"
              @error="handleImageError"
            />
            <div v-else class="d-flex align-items-center justify-content-center h-100">
              <i class="bi bi-person-circle" style="font-size: 5rem; color: #ccc;"></i>
            </div>
          </div>

          <!-- ข้อมูล -->
          <div class="card-body">
            <h5 class="card-title text-primary">
              {{ emp.first_name }} {{ emp.last_name }}
            </h5>
            
            <p class="card-text mb-2">
              <i class="bi bi-badge-tm text-muted"></i>
              <strong>รหัส:</strong> 
              <span class="badge bg-secondary">{{ String(emp.emp_id).padStart(8, '0') }}</span>
            </p>

            <p class="card-text mb-2">
              <i class="bi bi-geo-alt-fill text-danger"></i>
              <strong>ที่อยู่:</strong> 
              <span class="text-muted">{{ emp.address || '-' }}</span>
            </p>

            <p class="card-text mb-0">
              <i class="bi bi-telephone-fill text-success"></i>
              <strong>เบอร์โทร:</strong> 
              <span class="text-muted">{{ emp.phone || '-' }}</span>
            </p>
          </div>

          <!-- Footer -->
          <div class="card-footer bg-light text-center">
            <small class="text-muted">พนักงานรหัส {{ String(emp.emp_id).padStart(8, '0') }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- ไม่มีข้อมูล -->
    <div v-if="!loading && !error && employees.length === 0" class="alert alert-info text-center my-5">
      <i class="bi bi-info-circle"></i> ไม่พบข้อมูลพนักงาน
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'ShowEmployee',
  setup() {
    const employees = ref([]);
    const loading = ref(true);
    const error = ref(null);

    const API_URL = 'http://localhost:8081/Project/vue_php_api/Employee.php';
    const BASE_URL = 'http://localhost:8081/Project/vue_php_api/';

    // ฟังก์ชันสร้าง URL รูปภาพ
    const getImageUrl = (imagePath) => {
      if (!imagePath) return '';
      
      // ถ้ามี 'uploads/' อยู่แล้ว ไม่ต้องเพิ่ม
      if (imagePath.startsWith('uploads/')) {
        return BASE_URL + imagePath;
      }
      // ถ้าไม่มี ให้เพิ่ม uploads/
      return BASE_URL + 'uploads/' + imagePath;
    };

    // จัดการเมื่อรูปโหลดไม่ได้
    const handleImageError = (event) => {
      console.error('Image failed to load:', event.target.src);
      event.target.src = 'https://via.placeholder.com/300x250?text=No+Image';
    };

    const fetchEmployees = async () => {
      try {
        loading.value = true;
        const response = await fetch(API_URL);
        const result = await response.json();
        
        if (result.success) {
          employees.value = result.data;
        } else {
          error.value = result.message || 'ไม่สามารถโหลดข้อมูลได้';
        }
      } catch (err) {
        error.value = 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้';
        console.error('Fetch error:', err);
      } finally {
        loading.value = false;
      }
    };

    onMounted(fetchEmployees);

    return {
      employees,
      loading,
      error,
      getImageUrl,
      handleImageError
    };
  }
};
</script>

<style scoped>
.hover-shadow {
  transition: all 0.3s ease;
}

.hover-shadow:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.25) !important;
}

.card {
  border-radius: 10px;
  overflow: hidden;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 600;
}

.card-text {
  font-size: 0.95rem;
  line-height: 1.6;
}

.card-img-top {
  transition: transform 0.3s ease;
}

.card:hover .card-img-top {
  transform: scale(1.05);
}

/* Responsive */
@media (max-width: 768px) {
  .card-img-top-wrapper {
    height: 200px !important;
  }
}
</style>