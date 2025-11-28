<template>
  <div class="container mt-4">
    <h2 class="mb-3">แก้ไขพนักงาน</h2>
    
    <div class="mb-3">
      <a class="btn btn-primary" href="/add_employee" role="button">Add+</a>
    </div>

    <table class="table table-bordered table-striped">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>ชื่อพนักงาน</th>
          <th>แผนก</th>
          <th>ตำแหน่ง</th>
          <th>เงินเดือน</th>
          <th>รูปภาพ</th>
          <th>การจัดการ</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="employee in employees" :key="employee.employee_id">
          <td>{{ employee.employee_id || '-' }}</td>
          <td>{{ employee.employee_name || '-' }}</td>
          <td>{{ employee.department || '-' }}</td>
          <td>{{ employee.position || '-' }}</td>
          <td>{{ employee.salary ? Number(employee.salary).toLocaleString() : '-' }}</td>
          <td>
            <img 
              v-if="employee.image" 
              :src="'http://localhost:8081/Project/vue_php_api/uploads/' + employee.image" 
              width="100" 
              alt="รูปพนักงาน"
            />
            <span v-else class="text-muted">ไม่มีรูปภาพ</span>
          </td>
          <td>
            <button class="btn btn-warning btn-sm me-2" @click="openEditModal(employee)">แก้ไข</button>
            <button class="btn btn-danger btn-sm" @click="deleteEmployee(employee.employee_id)">ลบ</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loading" class="text-center"><p>กำลังโหลดข้อมูล...</p></div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>
    <div v-if="employees.length === 0 && !loading" class="alert alert-info text-center">
      ไม่พบข้อมูลพนักงาน
    </div>

    <!-- Modal แก้ไข -->
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">แก้ไขข้อมูลพนักงาน</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="updateEmployee">
              <div class="mb-3">
                <label class="form-label">ชื่อพนักงาน</label>
                <input v-model="editForm.employee_name" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">แผนก</label>
                <input v-model="editForm.department" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">ตำแหน่ง</label>
                <input v-model="editForm.position" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">เงินเดือน</label>
                <input v-model="editForm.salary" type="number" step="0.01" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">รูปภาพ</label>
                <input type="file" @change="handleFileUpload" class="form-control" accept="image/*" />
                <div v-if="editForm.image" class="mt-2">
                  <p>รูปเดิม:</p>
                  <img :src="'http://localhost:8081/Project/vue_php_api/uploads/' + editForm.image" width="100" />
                </div>
                <div v-else class="mt-2 text-muted">
                  ไม่มีรูปภาพเดิม
                </div>
              </div>
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">บันทึกการแก้ไข</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
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
import { Modal } from "bootstrap";

export default {
  name: "EditEmployeeList",
  setup() {
    const employees = ref([]);
    const loading = ref(true);
    const error = ref(null);

    const editForm = ref({
      employee_id: null,
      employee_name: "",
      department: "",
      position: "",
      salary: "",
      image: ""
    });
    const newImageFile = ref(null);
    let modalInstance = null;

    const fetchEmployees = async () => {
      try {
        loading.value = true;
        error.value = null;
        const res = await fetch("http://localhost:8081/Project/vue_php_api/Employee.php");
        
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        
        const data = await res.json();
        
        if (data.success && data.data) {
          // ทำความสะอาดข้อมูล - แทนที่ค่าว่างด้วยค่า default
          employees.value = data.data.map(emp => ({
            employee_id: emp.employee_id || 0,
            employee_name: emp.employee_name || 'ไม่มีข้อมูล',
            department: emp.department || 'ไม่มีข้อมูล',
            position: emp.position || 'ไม่มีข้อมูล',
            salary: emp.salary || 0,
            image: emp.image || ''
          }));
        } else {
          employees.value = [];
          if (data.error) {
            error.value = data.error;
          }
        }
      } catch (err) {
        console.error('Error fetching employees:', err);
        error.value = 'ไม่สามารถโหลดข้อมูลพนักงานได้: ' + err.message;
        employees.value = [];
      } finally {
        loading.value = false;
      }
    };

    const openEditModal = (employee) => {
      editForm.value = { 
        employee_id: employee.employee_id || null,
        employee_name: employee.employee_name || '',
        department: employee.department || '',
        position: employee.position || '',
        salary: employee.salary || '',
        image: employee.image || ''
      };
      newImageFile.value = null;
      
      const modalEl = document.getElementById("editModal");
      modalInstance = modalInstance || new Modal(modalEl);
      modalInstance.show();
    };

    const handleFileUpload = (event) => {
      const file = event.target.files[0];
      if (file) {
        // ตรวจสอบประเภทไฟล์
        if (!file.type.startsWith('image/')) {
          alert('กรุณาเลือกไฟล์รูปภาพเท่านั้น');
          event.target.value = '';
          return;
        }
        newImageFile.value = file;
      }
    };

    const updateEmployee = async () => {
      if (!editForm.value.employee_id) {
        alert('ไม่พบ ID พนักงาน');
        return;
      }

      const formData = new FormData();
      formData.append("action", "update");
      formData.append("employee_id", editForm.value.employee_id);
      formData.append("employee_name", editForm.value.employee_name);
      formData.append("department", editForm.value.department);
      formData.append("position", editForm.value.position);
      formData.append("salary", editForm.value.salary);
      
      if (editForm.value.image) {
        formData.append("old_image", editForm.value.image);
      }
      
      if (newImageFile.value) {
        formData.append("image", newImageFile.value);
      }

      try {
        const res = await fetch("http://localhost:8081/Project/vue_php_api/Employee.php", {
          method: "POST",
          body: formData
        });
        
        const result = await res.json();
        
        if (result.message) {
          alert(result.message);
          await fetchEmployees(); // รีเฟรชข้อมูล
          modalInstance.hide();
        } else if (result.error) {
          alert('เกิดข้อผิดพลาด: ' + result.error);
        }
      } catch (err) {
        console.error('Error updating employee:', err);
        alert('เกิดข้อผิดพลาดในการอัพเดตข้อมูล: ' + err.message);
      }
    };

    const deleteEmployee = async (id) => {
      if (!id) {
        alert('ไม่พบ ID พนักงาน');
        return;
      }

      if (!confirm("คุณแน่ใจหรือไม่ที่จะลบพนักงานนี้?")) return;

      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("employee_id", id);

      try {
        const res = await fetch("http://localhost:8081/Project/vue_php_api/Employee.php", {
          method: "POST",
          body: formData
        });
        
        const result = await res.json();
        
        if (result.message) {
          alert(result.message);
          employees.value = employees.value.filter(e => e.employee_id !== id);
        } else if (result.error) {
          alert('เกิดข้อผิดพลาด: ' + result.error);
        }
      } catch (err) {
        console.error('Error deleting employee:', err);
        alert('เกิดข้อผิดพลาดในการลบข้อมูล: ' + err.message);
      }
    };

    onMounted(() => {
      fetchEmployees();
    });

    return {
      employees,
      loading,
      error,
      editForm,
      openEditModal,
      handleFileUpload,
      updateEmployee,
      deleteEmployee
    };
  }
};
</script>