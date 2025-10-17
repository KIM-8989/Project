<template>
  <div class="container my-5">
    <h2 class="text-center mb-4">รายการสินค้า</h2>

    <!-- Loading State -->
    <div v-if="loading" class="text-center">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">กำลังโหลด...</span>
      </div>
      <p class="mt-2">กำลังโหลดข้อมูล...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="alert alert-danger text-center">
      {{ error }}
    </div>

    <!-- Products Grid -->
    <div v-if="!loading && !error" class="row">
      <div class="col-md-4 col-lg-3" v-for="product in products" :key="product.product_id">
        <div class="card shadow-sm mb-4 h-100">
          <img 
            :src="'http://localhost:8081/Project/vue_php_api/uploads/' + product.image" 
            class="card-img-top product-image" 
            :alt="product.product_name"
            @error="handleImageError"
          >
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ product.product_name }}</h5>
            <p class="card-text text-muted small flex-grow-1">
              {{ truncateText(product.description, 80) }}
            </p>
            <div class="mt-auto">
              <p class="text-primary fw-bold fs-5 mb-2">
                ฿{{ formatPrice(product.price) }}
              </p>
              <p class="text-secondary small mb-3">
                <i class="bi bi-box-seam"></i> คงเหลือ: {{ product.stock }} ชิ้น
              </p>
              <button 
                class="btn btn-primary w-100" 
                @click="viewDetails(product.product_id)"
              >
                <i class="bi bi-eye"></i> รายละเอียด
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && !error && products.length === 0" class="text-center py-5">
      <i class="bi bi-inbox fs-1 text-muted"></i>
      <p class="text-muted mt-3">ยังไม่มีสินค้าในระบบ</p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";

export default {
  name: "ShowProduct",
  setup() {
    const router = useRouter();
    const products = ref([]);
    const loading = ref(true);
    const error = ref(null);

    // ดึงข้อมูลสินค้าจาก API
    const fetchProducts = async () => {
      try {
        loading.value = true;
        error.value = null;
        
        const res = await fetch("http://localhost:8081/Project/vue_php_api/products_api.php");
        const data = await res.json();
        
        if (data.success) {
          products.value = data.data;
        } else {
          error.value = "ไม่สามารถโหลดข้อมูลสินค้าได้";
        }
      } catch (err) {
        error.value = "เกิดข้อผิดพลาด: " + err.message;
      } finally {
        loading.value = false;
      }
    };

    // จัดรูปแบบราคา
    const formatPrice = (price) => {
      return parseFloat(price).toLocaleString('th-TH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    };

    // ตัดข้อความให้สั้นลง
    const truncateText = (text, maxLength) => {
      if (!text) return '';
      if (text.length <= maxLength) return text;
      return text.substring(0, maxLength) + '...';
    };

    // จัดการเมื่อรูปภาพโหลดไม่ได้
    const handleImageError = (event) => {
      event.target.src = 'https://via.placeholder.com/300x200?text=No+Image';
    };

    // ดูรายละเอียดสินค้า
    const viewDetails = (productId) => {
      // สามารถเปลี่ยนเป็น route ที่ต้องการ เช่น /product/:id
      router.push(`/product/${productId}`);
    };

    onMounted(() => {
      fetchProducts();
    });

    return {
      products,
      loading,
      error,
      formatPrice,
      truncateText,
      handleImageError,
      viewDetails
    };
  }
};
</script>

<style scoped>
.product-image {
  height: 250px;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.card:hover .product-image {
  transform: scale(1.05);
}

.card {
  transition: box-shadow 0.3s ease, transform 0.3s ease;
  overflow: hidden;
}

.card:hover {
  box-shadow: 0 8px 16px rgba(0,0,0,0.2);
  transform: translateY(-5px);
}

.card-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
  min-height: 2.5rem;
}

.btn-primary {
  background-color: #0d6efd;
  border: none;
  transition: background-color 0.3s ease;
}

.btn-primary:hover {
  background-color: #0b5ed7;
}

.spinner-border {
  width: 3rem;
  height: 3rem;
}
</style>