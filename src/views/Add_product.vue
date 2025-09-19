<template>
  <div class="container">
    <h2>เพิ่มสินค้า</h2>
    
    <form @submit.prevent="addProduct">
      <div class="form-group">
        <input 
          v-model="product.product_name" 
          type="text"
          placeholder="ชื่อสินค้า" 
          required 
        />
      </div>
      
      <div class="form-group">
        <input 
          v-model="product.description" 
          type="text"
          placeholder="รายละเอียด" 
          required 
        />
      </div>
      
      <div class="form-group">
        <input 
          v-model="product.price" 
          type="number"
          step="0.01"
          placeholder="ราคา" 
          required 
        />
      </div>
      
      <div class="form-group">
        <input 
          v-model="product.stock" 
          type="number"
          placeholder="จำนวน" 
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
      product: {
        product_name: "",
        description: "",
        price: "",
        stock: "",
        image: null,
      },
      message: "",
      isSuccess: false,
    };
  },
  methods: {
    onFileChange(event) {
      this.product.image = event.target.files[0];
    },
    
    resetForm() {
      this.product = {
        product_name: "",
        description: "",
        price: "",
        stock: "",
        image: null,
      };
      this.message = "";
      this.isSuccess = false;
      this.$refs.fileInput.value = "";
    },

    async addProduct() {
      try {
        const formData = new FormData();
        formData.append("product_name", this.product.product_name);
        formData.append("description", this.product.description);
        formData.append("price", this.product.price);
        formData.append("stock", this.product.stock);
        formData.append("image", this.product.image);

        console.log("กำลังส่งข้อมูลไป API...");

        const response = await fetch("http://localhost:8081/Project/vue_php_api/add_product.php", {
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