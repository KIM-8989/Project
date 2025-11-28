<template>
  <div class="container mt-4">
    <h2 class="mb-4">รายชื่อพนักงาน</h2>
    
    <!-- ปุ่ม Add+ -->
    <div class="mb-3">
      <button class="btn btn-primary" @click="openAddModal">
        <i class="bi bi-plus-circle"></i> Add+
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">กำลังโหลดข้อมูล...</p>
    </div>

    <!-- Error -->
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <!-- ตาราง (ข้อ 2.1) -->
    <div v-if="!loading && !error" class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-primary">
          <tr>
            <th>รหัสพนักงาน</th>
            <th>รูปภาพ</th>
            <th>ชื่อ</th>
            <th>นามสกุล</th>
            <th>ที่อยู่</th>
            <th>เบอร์โทร</th>
            <th>การจัดการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="emp in employees" :key="emp.emp_id">
            <td>{{ String(emp.emp_id).padStart(8, '0') }}</td>
            <td>
              <img 
                v-if="emp.image" 
                :src="`http://localhost:8081/Project/vue_php_api/${emp.image}`" 
                class="rounded-circle"
                width="50" 
                height="50"
                style="object-fit: cover;"
                :alt="emp.first_name"
                @error="$event.target.src='https://via.placeholder.com/50'"
              />
              <span v-else class="text-muted">ไม่มีรูป</span>
            </td>
            <td>{{ emp.first_name }}</td>
            <td>{{ emp.last_name }}</td>
            <td>{{ emp.address || '-' }}</td>
            <td>{{ emp.phone || '-' }}</td>
            <td>
              <button class="btn btn-warning btn-sm me-1" @click="openEditModal(emp)">
                <i class="bi bi-pencil"></i> Edit
              </button>
              <button class="btn btn-danger btn-sm" @click="confirmDelete(emp.emp_id)">
                <i class="bi bi-trash"></i> Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      
      <div v-if="employees.length === 0" class="alert alert-info text-center">
        ไม่พบข้อมูลพนักงาน
      </div>
    </div>

    <!-- Modal Add (ข้อ 2.2) -->
    <div v-if="showAddModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">เพิ่มพนักงานใหม่</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeAddModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="addEmployee">
              <div class="mb-3">
                <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="newEmp.first_name" required>
              </div>
              <div class="mb-3">
                <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="newEmp.last_name" required>
              </div>
              <div class="mb-3">
                <label class="form-label">ที่อยู่</label>
                <textarea class="form-control" v-model="newEmp.address" rows="3"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">เบอร์โทร</label>
                <input type="text" class="form-control" v-model="newEmp.phone">
              </div>
              <div class="mb-3">
                <label class="form-label">รูปภาพ</label>
                <input type="file" class="form-control" @change="handleAddImage" accept="image/*">
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" @click="closeAddModal">Cancel</button>
                <button type="submit" class="btn btn-success" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                  Add
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Edit (ข้อ 2.3) -->
    <div v-if="showEditModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-warning">
            <h5 class="modal-title">แก้ไขข้อมูลพนักงาน</h5>
            <button type="button" class="btn-close" @click="closeEditModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="updateEmployee">
              <div class="mb-3">
                <label class="form-label">รหัสพนักงาน</label>
                <input type="text" class="form-control" :value="String(editEmp.emp_id).padStart(8, '0')" disabled>
              </div>
              <div class="mb-3">
                <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="editEmp.first_name" required>
              </div>
              <div class="mb-3">
                <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="editEmp.last_name" required>
              </div>
              <div class="mb-3">
                <label class="form-label">ที่อยู่</label>
                <textarea class="form-control" v-model="editEmp.address" rows="3"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">เบอร์โทร</label>
                <input type="text" class="form-control" v-model="editEmp.phone">
              </div>
              <div class="mb-3">
                <label class="form-label">รูปภาพ</label>
                <input type="file" class="form-control" @change="handleEditImage" accept="image/*">
                <div v-if="editEmp.image" class="mt-2">
                  <small class="text-muted">รูปเดิม:</small><br>
                  <img :src="`http://localhost:8081/Project/vue_php_api/${editEmp.image}`" width="80" class="mt-1 rounded">
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" @click="closeEditModal">Cancel</button>
                <button type="submit" class="btn btn-primary" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                  Update
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Delete Confirmation (ข้อ 2.4) -->
    <div v-if="showDeleteModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">ยืนยันการลบ</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeDeleteModal"></button>
          </div>
          <div class="modal-body text-center">
            <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3rem;"></i>
            <p class="mt-3">คุณต้องการลบพนักงานนี้หรือไม่?</p>
            <p class="text-muted">การดำเนินการนี้ไม่สามารถย้อนกลับได้</p>
          </div>
          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="deleteEmployee" :disabled="submitting">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
              Yes, Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'EmployeeList',
  setup() {
    const employees = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const submitting = ref(false);
    
    const showAddModal = ref(false);
    const showEditModal = ref(false);
    const showDeleteModal = ref(false);
    const deleteId = ref(null);

    const newEmp = ref({
      first_name: '',
      last_name: '',
      address: '',
      phone: '',
      image: null
    });

    const editEmp = ref({
      emp_id: null,
      first_name: '',
      last_name: '',
      address: '',
      phone: '',
      image: '',
      old_image: '',
      newImage: null
    });

    const API_URL = 'http://localhost:8081/Project/vue_php_api/Employee.php';

    // ดึงข้อมูล
    const fetchEmployees = async () => {
      try {
        loading.value = true;
        const response = await fetch(API_URL);
        const result = await response.json();
        
        if (result.success) {
          employees.value = result.data;
        } else {
          error.value = result.message;
        }
      } catch (err) {
        error.value = 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้';
        console.error(err);
      } finally {
        loading.value = false;
      }
    };

    // ADD
    const openAddModal = () => {
      newEmp.value = { first_name: '', last_name: '', address: '', phone: '', image: null };
      showAddModal.value = true;
    };

    const closeAddModal = () => {
      showAddModal.value = false;
    };

    const handleAddImage = (e) => {
      newEmp.value.image = e.target.files[0];
    };

    const addEmployee = async () => {
      submitting.value = true;
      try {
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('first_name', newEmp.value.first_name);
        formData.append('last_name', newEmp.value.last_name);
        formData.append('address', newEmp.value.address);
        formData.append('phone', newEmp.value.phone);
        if (newEmp.value.image) {
          formData.append('image', newEmp.value.image);
        }

        const response = await fetch(API_URL, {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        
        if (result.success) {
          alert(result.message);
          closeAddModal();
          fetchEmployees();
        } else {
          alert(result.message);
        }
      } catch (err) {
        alert('เกิดข้อผิดพลาด: ' + err.message);
      } finally {
        submitting.value = false;
      }
    };

    // EDIT
    const openEditModal = (emp) => {
      editEmp.value = {
        emp_id: emp.emp_id,
        first_name: emp.first_name,
        last_name: emp.last_name,
        address: emp.address,
        phone: emp.phone,
        image: emp.image,
        old_image: emp.image,
        newImage: null
      };
      showEditModal.value = true;
    };

    const closeEditModal = () => {
      showEditModal.value = false;
    };

    const handleEditImage = (e) => {
      editEmp.value.newImage = e.target.files[0];
    };

    const updateEmployee = async () => {
      submitting.value = true;
      try {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('emp_id', editEmp.value.emp_id);
        formData.append('first_name', editEmp.value.first_name);
        formData.append('last_name', editEmp.value.last_name);
        formData.append('address', editEmp.value.address);
        formData.append('phone', editEmp.value.phone);
        formData.append('old_image', editEmp.value.old_image);
        if (editEmp.value.newImage) {
          formData.append('image', editEmp.value.newImage);
        }

        const response = await fetch(API_URL, {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        
        if (result.success) {
          alert(result.message);
          closeEditModal();
          fetchEmployees();
        } else {
          alert(result.message);
        }
      } catch (err) {
        alert('เกิดข้อผิดพลาด: ' + err.message);
      } finally {
        submitting.value = false;
      }
    };

    // DELETE
    const confirmDelete = (id) => {
      deleteId.value = id;
      showDeleteModal.value = true;
    };

    const closeDeleteModal = () => {
      showDeleteModal.value = false;
      deleteId.value = null;
    };

    const deleteEmployee = async () => {
      submitting.value = true;
      try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('emp_id', deleteId.value);

        const response = await fetch(API_URL, {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        
        if (result.success) {
          alert(result.message);
          closeDeleteModal();
          fetchEmployees();
        } else {
          alert(result.message);
        }
      } catch (err) {
        alert('เกิดข้อผิดพลาด: ' + err.message);
      } finally {
        submitting.value = false;
      }
    };

    onMounted(fetchEmployees);

    return {
      employees,
      loading,
      error,
      submitting,
      showAddModal,
      showEditModal,
      showDeleteModal,
      newEmp,
      editEmp,
      openAddModal,
      closeAddModal,
      handleAddImage,
      addEmployee,
      openEditModal,
      closeEditModal,
      handleEditImage,
      updateEmployee,
      confirmDelete,
      closeDeleteModal,
      deleteEmployee
    };
  }
};
</script>

<style scoped>
.modal.show {
  animation: fadeIn 0.2s;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>