<template>
  <div class="container mt-4 col-md-4 bg-body-secondary">
    <h2 class="text-center mb-3">ลงทะเบียนนักเรียน</h2>
    <form @submit.prevent="addStudent">
      <div class="mb-2">
        <input 
          v-model="student.first_name" 
          class="form-control" 
          placeholder="ชื่อ" 
          type="text"
          required 
        />
      </div>
      <div class="mb-2">
        <input 
          v-model="student.last_name" 
          class="form-control" 
          placeholder="นามสกุล" 
          type="text"
          required 
        />
      </div>
      <div class="mb-2">
        <input 
          v-model="student.email" 
          class="form-control" 
          placeholder="อีเมล" 
          type="email"
          required 
        />
      </div>
      <div class="mb-2">
        <input 
          v-model="student.phone" 
          class="form-control" 
          placeholder="เบอร์โทร" 
          type="tel"
          required 
        />
      </div>
      <div class="text-center mt-4">
        <button 
          type="submit" 
          class="btn btn-primary mb-4"
          :disabled="isLoading"
        >
          <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
          {{ isLoading ? 'กำลังบันทึก...' : 'บันทึก' }}
        </button> &nbsp;
        <button type="reset" class="btn btn-secondary mb-4">ยกเลิก</button>
      </div>
    </form>

    <!-- Alert Message -->
    <div v-if="message" class="alert" :class="messageClass" role="alert">
      <i :class="messageIcon" class="me-2"></i>
      {{ message }}
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue';

export default {
  name: "AddStudent",
  setup() {
    const student = reactive({
      first_name: "",
      last_name: "",
      email: "",
      phone: ""
    });

    const message = ref("");
    const messageClass = ref("");
    const messageIcon = ref("");
    const isLoading = ref(false);

    const addStudent = async () => {
      // ตรวจสอบข้อมูลก่อนส่ง
      if (!validateForm()) return;

      isLoading.value = true;
      
      try {
        const response = await fetch("http://localhost:8081/Project/vue_php_api/add_student.php", {
          method: "POST",
          headers: { 
            "Content-Type": "application/json",
            "Accept": "application/json"
          },
          body: JSON.stringify(student)
        });
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
          showMessage(data.message, "success");
          resetForm();
          
          // ลบ message หลัง 5 วินาที
          setTimeout(() => {
            clearMessage();
          }, 5000);
        } else {
          showMessage(data.message, "error");
        }

      } catch (error) {
        console.error("Add student error:", error);
        
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
          showMessage("ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้ กรุณาตรวจสอบการเชื่อมต่อ", "error");
        } else {
          showMessage("เกิดข้อผิดพลาด: " + error.message, "error");
        }
      } finally {
        isLoading.value = false;
      }
    };

    const validateForm = () => {
      // Debug: แสดงข้อมูลที่กรอก
      console.log("Validating form data:", student);
      
      // ตรวจสอบข้อมูลว่าง
      if (!student.first_name || !student.first_name.trim()) {
        showMessage("กรุณากรอกชื่อ", "error");
        return false;
      }
      
      if (!student.last_name || !student.last_name.trim()) {
        showMessage("กรุณากรอกนามสกุล", "error");
        return false;
      }
      
      if (!student.email || !student.email.trim()) {
        showMessage("กรุณากรอกอีเมล", "error");
        return false;
      }
      
      if (!student.phone || !student.phone.trim()) {
        showMessage("กรุณากรอกเบอร์โทร", "error");
        return false;
      }

      // ตรวจสอบรูปแบบอีเมล
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(student.email.trim())) {
        showMessage("รูปแบบอีเมลไม่ถูกต้อง", "error");
        return false;
      }

      // ตรวจสอบรูปแบบเบอร์โทร (ยอมรับทั้งตัวเลข 9-10 หลัก)
      const phoneRegex = /^[0-9]{9,10}$/;
      const cleanPhone = student.phone.replace(/\D/g, ''); // ลบอักขระที่ไม่ใช่ตัวเลข
      if (!phoneRegex.test(cleanPhone)) {
        showMessage("เบอร์โทรต้องเป็นตัวเลข 9-10 หลัก", "error");
        return false;
      }

      console.log("Form validation passed");
      return true;
    };

    const showMessage = (msg, type) => {
      message.value = msg;
      
      if (type === "success") {
        messageClass.value = "alert-success";
        messageIcon.value = "bi bi-check-circle-fill";
      } else {
        messageClass.value = "alert-danger";
        messageIcon.value = "bi bi-exclamation-triangle-fill";
      }
    };

    const clearMessage = () => {
      message.value = "";
      messageClass.value = "";
      messageIcon.value = "";
    };

    const resetForm = () => {
      Object.assign(student, {
        first_name: "",
        last_name: "",
        email: "",
        phone: ""
      });
      clearMessage();
    };

    return {
      student,
      message,
      messageClass,
      messageIcon,
      isLoading,
      addStudent,
      resetForm
    };
  }
}
</script>

<style scoped>
.container {
  border-radius: 10px;
  padding: 30px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.form-control {
  border-radius: 8px;
  border: 2px solid #e9ecef;
  padding: 12px 15px;
  font-size: 16px;
  transition: all 0.3s ease;
}

.form-control:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn {
  border-radius: 8px;
  padding: 12px 30px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.alert {
  border-radius: 8px;
  border: none;
  font-weight: 500;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

h2 {
  color: #495057;
  font-weight: 700;
}
</style>