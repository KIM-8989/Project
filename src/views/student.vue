<template>
  <div class="container mt-4">
    <h2 class="mb-3">รายชื่อนักเรียน</h2>
    <table class="table table-bordered table-striped">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>ชื่อ</th>
          <th>นามสกุล</th>
          <th>อีเมลล์</th>
          <th>โทรศัพท์</th>
          <th>ลบ</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="student in students" :key="student.student_id">
          <td>{{ student.student_id }}</td>
          <td>{{ student.first_name }}</td>
          <td>{{ student.last_name }}</td>
          <td>{{ student.email }}</td>
          <td>{{ student.phone }}</td>
          <td>  
            <button class="btn btn-danger btn-sm" @click="deleteStudent(student.student_id)">ลบ</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Loading -->
    <div v-if="loading" class="text-center">
      <p>กำลังโหลดข้อมูล...</p>
    </div>

    <!-- Error -->
    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  name: "StudentList",
  setup() {
    const students = ref([]);
    const loading = ref(true);
    const error = ref(null);

    const fetchStudents = async () => {
      try {
        const response = await fetch("http://localhost:8081/Project/vue_php_api/student_api.php", {
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
          students.value = result.data;
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

    const deleteStudent = async (id) => {
      if (!confirm("คุณต้องการลบข้อมูลนักเรียนนี้ใช่หรือไม่?")) return;

      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("student_id", id);

      try {
        const res = await fetch("http://localhost:8081/Project/vue_php_api/student_api.php", {
          method: "POST",
          body: formData
        });
        const result = await res.json();
        if (result.success) {
          alert(result.message || "ลบข้อมูลนักเรียนสำเร็จ");
          students.value = students.value.filter(s => s.student_id !== id);
        } else {
          alert(result.message || "ไม่สามารถลบข้อมูลได้");
        }
      } catch (err) {
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

    onMounted(() => {
      fetchStudents();
    });

    return {
      students,
      loading,
      deleteStudent,
      error
    };
  }
};
</script>