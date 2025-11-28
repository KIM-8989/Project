<template>
  <div class="container mt-4">
    <h2 class="mb-3">รายชื่อพนักงาน</h2>
    
    <!-- ปุ่มเพิ่มพนักงานใหม่ -->
    <div class="mb-3">
      <button class="btn btn-primary" @click="showAddModal = true">
        <i class="fas fa-plus me-1"></i> เพิ่มพนักงานใหม่
      </button>
    </div>

    <table class="table table-bordered table-striped">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>รูปภาพ</th>
          <th>ชื่อ</th>
          <th>นามสกุล</th>
          <th>ที่อยู่</th>
          <th>เบอร์โทร</th>
          <th>จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="employee in employees" :key="employee.emp_id">
          <td>{{ employee.emp_id }}</td>
          <td>
            <img v-if="employee.image" :src="getImageUrl(employee.image)" alt="Employee" class="employee-img">
            <span v-else class="text-muted">ไม่มีรูป</span>
          </td>
          <td>{{ employee.first_name }}</td>
          <td>{{ employee.last_name }}</td>
          <td>{{ employee.address }}</td>
          <td>{{ employee.phone }}</td>
          <td>
            <button class="btn btn-warning btn-sm me-1" @click="editEmployee(employee)">
              <i class="fas fa-edit"></i> แก้ไข
            </button>
            <button class="btn btn-danger btn-sm" @click="deleteEmployee(employee.emp_id)">
              <i class="fas fa-trash"></i> ลบ
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loading" class="text-center">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">กำลังโหลดข้อมูล...</p>
    </div>

    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <!-- Modal เพิ่ม/แก้ไขพนักงาน -->
    <div v-if="showAddModal || showEditModal" class="modal-backdrop show" @click="closeModal"></div>
    <div class="modal" :class="{ 'show d-block': showAddModal || showEditModal }" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ showAddModal ? 'เพิ่มพนักงานใหม่' : 'แก้ไขข้อมูลพนักงาน' }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveEmployee">
              <div class="mb-3">
                <label for="first_name" class="form-label">ชื่อ</label>
                <input type="text" class="form-control" id="first_name" 
                       v-model="currentEmployee.first_name" required>
              </div>
              <div class="mb-3">
                <label for="last_name" class="form-label">นามสกุล</label>
                <input type="text" class="form-control" id="last_name" 
                       v-model="currentEmployee.last_name" required>
              </div>
              <div class="mb-3">
                <label for="address" class="form-label">ที่อยู่</label>
                <textarea class="form-control" id="address" rows="3" 
                          v-model="currentEmployee.address"></textarea>
              </div>
              <div class="mb-3">
                <label for="phone" class="form-label">เบอร์โทร</label>
                <input type="text" class="form-control" id="phone" 
                       v-model="currentEmployee.phone">
              </div>
              <div class="mb-3">
                <label for="image" class="form-label">รูปภาพ</label>
                <input type="file" class="form-control" id="image" 
                       @change="handleImageUpload" accept="image/*">
                <div class="form-text">อัพโหลดรูปภาพพนักงาน (JPG, PNG, GIF)</div>
                <div v-if="currentEmployee.image && !imageFile" class="mt-2">
                  <img :src="getImageUrl(currentEmployee.image)" alt="Current Image" class="current-image-preview">
                </div>
              </div>
              <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-secondary me-2" @click="closeModal">
                  ยกเลิก
                </button>
                <button type="submit" class="btn btn-primary" :disabled="saving">
                  <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                  {{ showAddModal ? 'เพิ่ม' : 'อัพเดท' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  name: "EmployeeList",
  setup() {
    const employees = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const saving = ref(false);
    const showAddModal = ref(false);
    const showEditModal = ref(false);
    const currentEmployee = ref({
      emp_id: null,
      first_name: '',
      last_name: '',
      address: '',
      phone: '',
      image: ''
    });
    const imageFile = ref(null);

    const apiBaseUrl = 'http://localhost:8081/Project/api';
    const baseImageUrl = 'http://localhost:8081/Project/uploads/';

    const getImageUrl = (imagePath) => {
      if (!imagePath) return `${baseImageUrl}default.png`;
      return `${baseImageUrl}${imagePath}`;
    };

    const fetchEmployees = async () => {
      loading.value = true;
      error.value = null;
      try {
        const response = await fetch(`${apiBaseUrl}/employees_api.php`, {
          method: "GET",
          headers: {
            "Content-Type": "application/json"
          }
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        if (result.success) {
          employees.value = result.data;
        } else {
          error.value = result.message;
        }

      } catch (err) {
        console.error("Fetch error:", err);
        error.value = "ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้: " + err.message;
      } finally {
        loading.value = false;
      }
    };

    const saveEmployee = async () => {
      saving.value = true;
      try {
        const formData = new FormData();
        const action = showAddModal.value ? 'add' : 'update';
        
        formData.append('action', action);
        formData.append('first_name', currentEmployee.value.first_name);
        formData.append('last_name', currentEmployee.value.last_name);
        formData.append('address', currentEmployee.value.address);
        formData.append('phone', currentEmployee.value.phone);
        
        if (imageFile.value) {
          formData.append('image', imageFile.value);
        }
        
        if (showEditModal.value) {
          formData.append('emp_id', currentEmployee.value.emp_id);
          formData.append('current_image', currentEmployee.value.image);
        }

        const response = await fetch(`${apiBaseUrl}/employees_api.php`, {
          method: 'POST',
          body: formData
        });

        const result = await response.json();
        
        if (result.success) {
          closeModal();
          fetchEmployees();
          alert(result.message);
        } else {
          alert('เกิดข้อผิดพลาด: ' + result.message);
        }
      } catch (err) {
        console.error('Error saving employee:', err);
        alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
      } finally {
        saving.value = false;
      }
    };

    const editEmployee = (employee) => {
      currentEmployee.value = { ...employee };
      showEditModal.value = true;
      imageFile.value = null;
    };

    const deleteEmployee = async (empId) => {
      if (!confirm("คุณแน่ใจหรือไม่ที่จะลบพนักงานนี้?")) return;

      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("emp_id", empId);

      try {
        const response = await fetch(`${apiBaseUrl}/employees_api.php`, {
          method: "POST",
          body: formData
        });
        
        const result = await response.json();
        if (result.success) {
          alert(result.message);
          employees.value = employees.value.filter(emp => emp.emp_id !== empId);
        } else {
          alert(result.message || "ไม่สามารถลบข้อมูลได้");
        }
      } catch (err) {
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

    const handleImageUpload = (event) => {
      imageFile.value = event.target.files[0];
    };

    const closeModal = () => {
      showAddModal.value = false;
      showEditModal.value = false;
      currentEmployee.value = {
        emp_id: null,
        first_name: '',
        last_name: '',
        address: '',
        phone: '',
        image: ''
      };
      imageFile.value = null;
      
      const fileInput = document.getElementById('image');
      if (fileInput) fileInput.value = '';
    };

    onMounted(fetchEmployees);

    return {
      employees,
      loading,
      error,
      saving,
      showAddModal,
      showEditModal,
      currentEmployee,
      fetchEmployees,
      saveEmployee,
      editEmployee,
      deleteEmployee,
      handleImageUpload,
      closeModal,
      getImageUrl
    };
  }
};
</script>

<style scoped>
.employee-img {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 50%;
}

.current-image-preview {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 8px;
}

.modal.show {
  display: block;
  background-color: rgba(0, 0, 0, 0.5);
}
</style>