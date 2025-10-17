<template>
  <div class="container mt-4">
    <h2 class="mb-3">รายการพนักงาน</h2>
    
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
          <td>{{ employee.employee_id }}</td>
          <td>{{ employee.employee_name }}</td>
          <td>{{ employee.department }}</td>
          <td>{{ employee.position }}</td>
          <td>{{ Number(employee.salary).toLocaleString() }}</td>
          <td>
            <img :src="'http://localhost:8081/Project/vue_php_api/uploads/' + employee.image" width="100" />
          </td>
          <td>
            <button class="btn btn-danger btn-sm" @click="deleteEmployee(employee.employee_id)">ลบ</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loading" class="text-center"><p>กำลังโหลดข้อมูล...</p></div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>
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

    const fetchEmployees = async () => {
      try {
        const res = await fetch("http://localhost:8081/Project/vue_php_api/Employee.php");
        const data = await res.json();
        employees.value = data.success ? data.data : [];
      } catch (err) {
        error.value = err.message;
      } finally {
        loading.value = false;
      }
    };

    const deleteEmployee = async (id) => {
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
          alert(result.error);
        }
      } catch (err) {
        alert(err.message);
      }
    };

    onMounted(fetchEmployees);

    return {
      employees,
      loading,
      error,
      deleteEmployee
    };
  }
};
</script>
