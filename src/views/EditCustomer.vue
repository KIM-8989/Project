<template>
  <div class="container mt-4">
    <h2 class="mb-3">แก้ไขข้อมูลลูกค้า</h2>
    
    <div class="mb-3">
      <button class="btn btn-primary" @click="openAddModal">
        <i class="bi bi-plus-circle"></i> เพิ่มลูกค้าใหม่
      </button>
    </div>

    <table class="table table-bordered table-striped">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>ชื่อ</th>
          <th>นามสกุล</th>
          <th>เบอร์โทร</th>
          <th>ชื่อผู้ใช้</th>
          <th>แก้ไข/ลบ</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="customer in customers" :key="customer.customer_id">
          <td>{{ customer.customer_id }}</td>
          <td>{{ customer.firstName }}</td>
          <td>{{ customer.lastName }}</td>
          <td>{{ customer.phone }}</td>
          <td>{{ customer.username }}</td>
          <td>
            <button class="btn btn-warning btn-sm" @click="openEditModal(customer)">
              แก้ไข
            </button>
            |
            <button class="btn btn-danger btn-sm" @click="deleteCustomer(customer.customer_id)">
              ลบ
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loading" class="text-center"><p>กำลังโหลดข้อมูล...</p></div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <!-- Modal ใช้ทั้งเพิ่ม/แก้ไข -->
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditMode ? "แก้ไขข้อมูลลูกค้า" : "เพิ่มลูกค้าใหม่" }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveCustomer">
              <div class="mb-3">
                <label class="form-label">ชื่อ</label>
                <input v-model="editCustomer.firstName" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">นามสกุล</label>
                <input v-model="editCustomer.lastName" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">เบอร์โทร</label>
                <input v-model="editCustomer.phone" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">ชื่อผู้ใช้</label>
                <input v-model="editCustomer.username" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">รหัสผ่าน</label>
                <input v-model="editCustomer.password" type="password" class="form-control"
                       :required="!isEditMode"
                       placeholder="กรอกเฉพาะเมื่อเพิ่มใหม่หรือเปลี่ยนรหัสผ่าน">
              </div>
              <button type="submit" class="btn btn-success">
                {{ isEditMode ? "บันทึกการแก้ไข" : "เพิ่มลูกค้า" }}
              </button>
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
  name: "EditCustomer",
  setup() {
    const customers = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const editCustomer = ref({});
    const isEditMode = ref(false);
    let editModal = null;

    // ดึงข้อมูลลูกค้าทั้งหมด
    const fetchCustomers = async () => {
      try {
        const response = await fetch("http://localhost:8081/Project/vue_php_api/customers_api.php");
        const result = await response.json();

        if (result.success) {
          customers.value = result.data;
        } else {
          error.value = result.message;
        }
      } catch (err) {
        error.value = err.message;
      } finally {
        loading.value = false;
      }
    };

    onMounted(() => {
      fetchCustomers();
      const modalEl = document.getElementById("editModal");
      editModal = new window.bootstrap.Modal(modalEl);
    });

    // เปิด Modal เพิ่มลูกค้า
    const openAddModal = () => {
      isEditMode.value = false;
      editCustomer.value = {
        firstName: "",
        lastName: "",
        phone: "",
        username: "",
        password: ""
      };
      editModal.show();
    };

    // เปิด Modal แก้ไขลูกค้า
    const openEditModal = (customer) => {
      isEditMode.value = true;
      editCustomer.value = { ...customer, password: "" };
      editModal.show();
    };

    // บันทึกข้อมูล (เพิ่ม/แก้ไข)
    const saveCustomer = async () => {
      const url = "http://localhost:8081/Project/vue_php_api/customers_api.php";
      
      // สร้าง FormData
      const formData = new FormData();
      
      if (isEditMode.value) {
        formData.append("action", "update");
        formData.append("customer_id", editCustomer.value.customer_id);
      } else {
        formData.append("action", "add");
      }
      
      formData.append("firstName", editCustomer.value.firstName);
      formData.append("lastName", editCustomer.value.lastName);
      formData.append("phone", editCustomer.value.phone);
      formData.append("username", editCustomer.value.username);
      
      // ส่งรหัสผ่านเฉพาะเมื่อมีการกรอก
      if (editCustomer.value.password) {
        formData.append("password", editCustomer.value.password);
      }

      try {
        const response = await fetch(url, {
          method: "POST",
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          fetchCustomers();
          editModal.hide();
        } else {
          alert(result.message);
        }
      } catch (err) {
        console.error("Error:", err);
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

    // ลบลูกค้า
    const deleteCustomer = async (id) => {
      if (!confirm("คุณต้องการลบข้อมูลนี้ใช่หรือไม่?")) return;
      
      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("customer_id", id);
      
      try {
        const response = await fetch("http://localhost:8081/Project/vue_php_api/customers_api.php", {
          method: "POST",
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          alert(result.message);
          fetchCustomers();
        } else {  
          alert(result.message);
        }
      } catch (err) {
        console.error("Error:", err);
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

    return {
      customers,
      loading,
      error,
      editCustomer,
      isEditMode,
      openAddModal,
      openEditModal,
      saveCustomer,
      deleteCustomer
    };
  }
};
</script>