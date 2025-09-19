<template>
  <div class="container mt-4">
    <h2 class="mb-3">รายชื่อลูกค้า</h2>
    
    <div class="mb-3">
      <a class="btn btn-primary" href="/add_products" role="button">Add+</a>
    </div>

    <table class="table table-bordered table-striped">
      <thead class="table-primary">
        <tr>
          <th>รหัสสินค้า</th>
          <th>ชื่อสินค้า</th>
          <th>รายละเอียด</th>
          <th>ราคา</th>
          <th>รูปสินค้า</th>
          <th>จำนวน</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="products in products" :key="products.products_id">
          <td>{{ products.product_id }}</td>
          <td>{{ products.product_name }}</td>
          <td>{{ products.description }}</td>
          <td>{{ products.price }}</td>
          <td>
            <img :src="'http://localhost:8081/Project/vue_php_api/uploads/' + products.image" alt="Product Image" style="width: 100px;">
          </td>
          <td>{{ products.stock }}</td>
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
  name: "productsList",
  setup() {
    const products = ref([]);
    const loading = ref(true);
    const error = ref(null);

    // ฟังก์ชันดึงข้อมูลจาก API ด้วย GET
    const fetchproducts = async () => {
      try {
        const response = await fetch("http://localhost:8081/Project/vue_php_api/products_api.php", {
          method: "GET",
          headers: {
            "Content-Type": "application/json"
          }
        });

        if (!response.ok) {
          throw new Error("ไม่สามารถดึงข้อมูลได้");
        }

        const result = await response.json();
        if (result.success) {
          products.value = result.data;
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
      fetchproducts();
    });

    return {
      products,
      loading,
      error
    };
  }
};
</script>