<template>
  <div class="container mt-4">
    <h2 class="mb-3">แก้ไขข้อมูลนักเรียน</h2>

    <div v-if="loading" class="text-center">
      <p>กำลังโหลดข้อมูล...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <div v-else class="row">
      <!-- รายชื่อนักเรียน -->
      <div class="col-md-4">
        <h5>เลือกนักเรียนที่ต้องการแก้ไข</h5>
        <div class="list-group">
          <button
            v-for="student in students"
            :key="student.student_id"
            class="list-group-item list-group-item-action"
            :class="{ active: selectedStudent?.student_id === student.student_id }"
            @click="selectStudent(student)"
          >
            {{ student.student_id }}. {{ student.first_name }} {{ student.last_name }}
          </button>
        </div>
      </div>

      <!-- ฟอร์มแก้ไข -->
      <div class="col-md-8">
        <div v-if="selectedStudent" class="card">
          <div class="card-body">
            <h5 class="card-title">แก้ไขข้อมูล</h5>
            <form @submit.prevent="updateStudent">
              <div class="mb-3">
                <label class="form-label">ชื่อ</label>
                <input v-model="editForm.first_name" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">นามสกุล</label>
                <input v-model="editForm.last_name" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">อีเมล</label>
                <input v-model="editForm.email" type="email" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">โทรศัพท์</label>
                <input v-model="editForm.phone" type="text" class="form-control" required />
              </div>
              <button type="submit" class="btn btn-success">บันทึกการแก้ไข</button>
              <button type="button" class="btn btn-secondary ms-2" @click="clearSelection">ยกเลิก</button>
            </form>
          </div>
        </div>
        <div v-else class="alert alert-info">
          กรุณาเลือกนักเรียนจากรายการด้านซ้าย
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  name: "EditStudent",
  setup() {
    const students = ref([]);
    const selectedStudent = ref(null);
    const loading = ref(true);
    const error = ref(null);

    const editForm = ref({
      student_id: null,
      first_name: "",
      last_name: "",
      email: "",
      phone: ""
    });

    const fetchStudents = async () => {
      try {
        const response = await fetch("http://localhost:8081/Project/vue_php_api/student_api.php");
        const result = await response.json();
        if (result.success) {
          students.value = result.data;
        } else {
          error.value = result.message;
        }
      } catch (err) {
        error.value = err.message;
      } finally {
        loading.value = false;
      }
    };

    const selectStudent = (student) => {
      selectedStudent.value = student;
      editForm.value = {
        student_id: student.student_id,
        first_name: student.first_name,
        last_name: student.last_name,
        email: student.email,
        phone: student.phone
      };
    };

    const clearSelection = () => {
      selectedStudent.value = null;
      editForm.value = {
        student_id: null,
        first_name: "",
        last_name: "",
        email: "",
        phone: ""
      };
    };

    const updateStudent = async () => {
      const formData = new FormData();
      formData.append("action", "update");
      formData.append("student_id", editForm.value.student_id);
      formData.append("first_name", editForm.value.first_name);
      formData.append("last_name", editForm.value.last_name);
      formData.append("email", editForm.value.email);
      formData.append("phone", editForm.value.phone);

      try {
        const res = await fetch("http://localhost:8081/Project/vue_php_api/student_api.php", {
          method: "POST",
          body: formData
        });
        const result = await res.json();
        if (result.success) {
          alert(result.message || "แก้ไขข้อมูลสำเร็จ");
          await fetchStudents();
          clearSelection();
        } else {
          alert(result.message || "ไม่สามารถแก้ไขข้อมูลได้");
        }
      } catch (err) {
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

    onMounted(fetchStudents);

    return {
      students,
      selectedStudent,
      loading,
      error,
      editForm,
      selectStudent,
      clearSelection,
      updateStudent
    };
  }
};
</script>