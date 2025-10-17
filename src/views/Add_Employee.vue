<template>
  <div class="container">
    <h2>เพิ่มพนักงาน</h2>
    
    <form @submit.prevent="addEmployee">
      <div class="form-group">
        <input 
          v-model="employee.employee_name" 
          type="text"
          placeholder="ชื่อพนักงาน" 
          required 
        />
      </div>
      
      <div class="form-group">
        <input 
          v-model="employee.department" 
          type="text"
          placeholder="แผนก" 
          required 
        />
      </div>
      
      <div class="form-group">
        <input 
          v-model="employee.position" 
          type="text"
          placeholder="ตำแหน่ง" 
          required 
        />
      </div>
      
      <div class="form-group">
        <input 
          v-model="employee.salary" 
          type="number"
          step="0.01"
          placeholder="เงินเดือน" 
          required 
        />
      </div>
      
      <div class="form-group">
        <input 
          type="file" 
          @change="onFileChange" 
          ref="fileInput"
          accept="image/*"
          required 
        />
      </div>
      
      <div class="form-buttons">
        <button type="submit">บันทึก</button>
        <button type="button" @click="resetForm">ยกเลิก</button>
      </div>
    </form>

    <div v-if="message" class="message" :class="{ success: isSuccess, error: !isSuccess }">
      {{ message }}
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      employee: {
        employee_name: "",
        department: "",
        position: "",
        salary: "",
        image: null,
      },
      message: "",
      isSuccess: false,
    };
  },
  methods: {
    onFileChange(event) {
      this.employee.image = event.target.files[0];
    },
    
    resetForm() {
      this.employee = {
        employee_name: "",
        department: "",
        position: "",
        salary: "",
        image: null,
      };
      this.message = "";
      this.isSuccess = false;
      this.$refs.fileInput.value = "";
    },

    async addEmployee() {
      try {
        const formData = new FormData();
        formData.append("employee_name", this.employee.employee_name);
        formData.append("department", this.employee.department);
        formData.append("position", this.employee.position);
        formData.append("salary", this.employee.salary);
        formData.append("image", this.employee.image);

        console.log("กำลังส่งข้อมูลไป API...");

        const response = await fetch("http://localhost:8081/Project/vue_php_api/add_employee.php", {
          method: "POST",
          body: formData,
        });

        console.log("Response status:", response.status);
        console.log("Response headers:", response.headers.get('content-type'));

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log("Response data:", data);
        
        this.message = data.message;
        this.isSuccess = data.success;

        if (data.success) {
          this.resetForm();
        }

      } catch (error) {
        console.error('Detailed Error:', error);
        this.message = "เกิดข้อผิดพลาดในการเชื่อมต่อ: " + error.message;
        this.isSuccess = false;
      }
    },
  },
};
</script>

<style>
.container {
  max-width: 500px;
  margin: 50px auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.form-buttons {
  text-align: center;
  margin-top: 20px;
}

.form-buttons button {
  margin: 0 10px;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.form-buttons button[type="submit"] {
  background-color: #007bff;
  color: white;
}

.form-buttons button[type="button"] {
  background-color: #6c757d;
  color: white;
}

.message {
  margin-top: 15px;
  padding: 10px;
  border-radius: 4px;
  text-align: center;
}

.message.success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.message.error {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}
</style>