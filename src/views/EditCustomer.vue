<template>
  <div class="container mt-4">
    <h2 class="mb-3">แก้ไขข้อมูลลูกค้า</h2>

    <div v-if="loading" class="text-center">
      <p>กำลังโหลดข้อมูล...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <div v-else class="row">
      <!-- รายชื่อลูกค้า -->
      <div class="col-md-4">
        <h5>เลือกลูกค้าที่ต้องการแก้ไข</h5>
        <div class="list-group">
          <button
            v-for="customer in customers"
            :key="customer.customer_id"
            class="list-group-item list-group-item-action"
            :class="{ active: selectedCustomer?.customer_id === customer.customer_id }"
            @click="selectCustomer(customer)"
          >
            {{ customer.customer_id }}. {{ customer.firstName }} {{ customer.lastName }}
          </button>
        </div>
      </div>

      <!-- ฟอร์มแก้ไข -->
      <div class="col-md-8">
        <div v-if="selectedCustomer" class="card">
          <div class="card-body">
            <h5 class="card-title">แก้ไขข้อมูล</h5>
            <form @submit.prevent="updateCustomer">
              <div class="mb-3">
                <label class="form-label">ชื่อ</label>
                <input v-model="editForm.firstName" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">นามสกุล</label>
                <input v-model="editForm.lastName" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">เบอร์โทร</label>
                <input v-model="editForm.phone" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">ชื่อผู้ใช้</label>
                <input v-model="editForm.username" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">รหัสผ่านใหม่ (ถ้าต้องการเปลี่ยน)</label>
                <input v-model="editForm.password" type="password" class="form-control" placeholder="เว้นว่างไว้ถ้าไม่ต้องการเปลี่ยน" />
              </div>
              <button type="submit" class="btn btn-success">บันทึกการแก้ไข</button>
              <button type="button" class="btn btn-secondary ms-2" @click="clearSelection">ยกเลิก</button>
            </form>
          </div>
        </div>
        <div v-else class="alert alert-info">
          กรุณาเลือกลูกค้าจากรายการด้านซ้าย
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
    const selectedCustomer = ref(null);
    const loading = ref(true);
    const error = ref(null);

    const editForm = ref({
      customer_id: null,
      firstName: "",
      lastName: "",
      phone: "",
      username: "",
      password: ""
    });

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

    const selectCustomer = (customer) => {
      selectedCustomer.value = customer;
      editForm.value = {
        customer_id: customer.customer_id,
        firstName: customer.firstName,
        lastName: customer.lastName,
        phone: customer.phone,
        username: customer.username,
        password: ""
      };
    };

    const clearSelection = () => {
      selectedCustomer.value = null;
      editForm.value = {
        customer_id: null,
        firstName: "",
        lastName: "",
        phone: "",
        username: "",
        password: ""
      };
    };

    const updateCustomer = async () => {
      const formData = new FormData();
      formData.append("action", "update");
      formData.append("customer_id", editForm.value.customer_id);
      formData.append("firstName", editForm.value.firstName);
      formData.append("lastName", editForm.value.lastName);
      formData.append("phone", editForm.value.phone);
      formData.append("username", editForm.value.username);
      if (editForm.value.password) {
        formData.append("password", editForm.value.password);
      }

      try {
        const res = await fetch("http://localhost:8081/Project/vue_php_api/customers_api.php", {
          method: "POST",
          body: formData
        });
        const result = await res.json();
        if (result.success) {
          alert(result.message || "แก้ไขข้อมูลสำเร็จ");
          await fetchCustomers();
          clearSelection();
        } else {
          alert(result.message || "ไม่สามารถแก้ไขข้อมูลได้");
        }
      } catch (err) {
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

    onMounted(fetchCustomers);

    return {
      customers,
      selectedCustomer,
      loading,
      error,
      editForm,
      selectCustomer,
      clearSelection,
      updateCustomer
    };
  }
};
</script>