<template>
  <div class="container mt-4">
    <h2 class="mb-3">รายชื่อนักเรียน</h2>
    
    <div class="mb-3">
      <a class="btn btn-primary" href="/add_student" role="button">เพิ่มนักเรียน</a>
    </div>

    <!-- ตารางแสดงข้อมูลนักเรียน -->
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

    // ฟังก์ชันดึงข้อมูลนักเรียนจาก API ด้วย GET
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

    onMounted(() => {
      fetchStudents();
    });

    // ฟังก์ชั่นการลบข้อมูลนักเรียน
    const deleteStudent = async (id) => {
      if (!confirm("คุณต้องการลบข้อมูลนักเรียนนี้ใช่หรือไม่?")) return;

      try {
        console.log("Attempting to delete student ID:", id);

        // วิธีที่ 1: ใช้ DELETE method (ถ้า API รองรับ)
        let response = await fetch(`http://localhost:8081/Project/vue_php_api/student_api.php?id=${id}`, {
          method: "DELETE",
          headers: {
            "Content-Type": "application/json"
          }
        });

        // ถ้า DELETE ไม่ทำงาน ลองใช้ POST แทน
        if (!response.ok && response.status === 405) {
          console.log("DELETE method not allowed, trying POST...");
          response = await fetch("http://localhost:8081/Project/vue_php_api/student_api.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify({ 
              action: "delete",
              student_id: id 
            })
          });
        }

        // ถ้า POST ก็ไม่ทำงาน ลองใช้ GET แทน
        if (!response.ok && response.status === 405) {
          console.log("POST method not working, trying GET with query params...");
          response = await fetch(`http://localhost:8081/Project/vue_php_api/student_api.php?action=delete&id=${id}`, {
            method: "GET",
            headers: {
              "Content-Type": "application/json"
            }
          });
        }

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
        }

        const result = await response.json();
        console.log("Delete response:", result);

        if (result.success) {
          // ลบออกจาก students ทันที
          students.value = students.value.filter(s => s.student_id !== id);
          alert(result.message || "ลบข้อมูลนักเรียนสำเร็จ");
        } else {
          alert(result.message || "ไม่สามารถลบข้อมูลได้");
        }

      } catch (err) {
        console.error("Delete error:", err);
        alert("เกิดข้อผิดพลาด: " + err.message);
        
        // แสดงรายละเอียดข้อผิดพลาดเพิ่มเติม
        if (err.name === 'TypeError' && err.message.includes('fetch')) {
          alert("ไม่สามารถเชื่อมต่อกับ server ได้ กรุณาตรวจสอบ:\n" +
                "1. Server PHP ทำงานอยู่หรือไม่\n" +
                "2. URL ถูกต้องหรือไม่\n" +
                "3. CORS settings");
        }
      }
    };

    return {
      students,
      loading,
      deleteStudent,
      error
    };
  }
};
</script>