<template>
  <div class="container mt-4">
    <h2 class="mb-3">รายชื่อลูกค้า</h2>
    <table class="table table-bordered table-striped">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>ชื่อ</th>
          <th>นามสกุล</th>
          <th>เบอร์โทร</th>
          <th>ชื่อผู้ใช้</th>
          <th>ลบ</th>
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
            <button class="btn btn-danger btn-sm" @click="deleteCustomer(customer.customer_id)">ลบ</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loading" class="text-center">
      <p>กำลังโหลดข้อมูล...</p>
    </div>

    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  name: "CustomerList",
  setup() {
    const customers = ref([]);
    const loading = ref(true);
    const error = ref(null);

    const fetchCustomers = async () => {
      try {
        const response = await fetch("http://localhost:8081/Project/vue_php_api/customers_api.php", {
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
          customers.value = result.data;
        } else {
          error.value = result.message;
        }

      } catch (err) {
        console.error("Fetch error:", err);
        error.value = err.message;
      } finally {
        loading.value = false;
      }
    };

    const deleteCustomer = async (id) => {
      if (!confirm("คุณแน่ใจหรือไม่ที่จะลบลูกค้านี้?")) return;

      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("customer_id", id);

      try {
        const res = await fetch("http://localhost:8081/Project/vue_php_api/customers_api.php", {
          method: "POST",
          body: formData
        });
        const result = await res.json();
        if (result.success) {
          alert(result.message || "ลบข้อมูลสำเร็จ");
          customers.value = customers.value.filter(c => c.customer_id !== id);
        } else {
          alert(result.message || "ไม่สามารถลบข้อมูลได้");
        }
      } catch (err) {
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

    onMounted(fetchCustomers);

    return {
      customers,
      loading,
      error,
      deleteCustomer
    };
  }
};
</script>