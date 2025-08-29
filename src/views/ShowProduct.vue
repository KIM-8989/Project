<template>
  <div class="container my-5">
    <h2 class="text-center mb-4">รายการสินค้า</h2>
    <div class="row">
      <div class="col-md-4" v-for="product in products" :key="product.id">
        <div class="card shadow-sm mb-4">
          <img :src="product.image" class="card-img-top" :alt="product.name">
          <div class="card-body text-center">
            <h5 class="card-title">{{ product.name }}</h5>
            <p class="card-text">{{ product.price }} บาท</p>
            <button class="btn btn-primary">รายละเอียด</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ShowProduct",
  data() {
    return {
      products: [
        { id: 1, name: "TOKYO MARUI NOVAKS V10 ULTRA ", price: 5800, image: "https://image.makewebcdn.com/makeweb/m_1920x0/pBDC0uceT/Pistols/234.jpg?v=202405291424" },
        { id: 2, name: "Tokyo Marui Hi-Capa 5.1 Gold Match", price: 6500, image: "https://image.makewebcdn.com/makeweb/m_1920x0/pBDC0uceT/Pistols/BBGUNZONE_Tokyo_Marui_Hi_Capa_5_1Gold_Match__1_.jpg?v=202405291424" },
        { id: 3, name: "Double Bell 774C Glock 18C full auto", price: 2500, image: "https://image.makewebcdn.com/makeweb/r_409x409/pBDC0uceT/Pistols/_%E0%B8%81%E0%B8%B2%E0%B9%81%E0%B8%9F_%E0%B9%80%E0%B8%A1%E0%B8%99%E0%B8%B9%E0%B9%80%E0%B8%84%E0%B8%A3%E0%B8%B7%E0%B9%88%E0%B8%AD%E0%B8%87%E0%B8%94%E0%B8%B7%E0%B9%88%E0%B8%A1_%E0%B9%82%E0%B8%9B%E0%B8%A3%E0%B9%82%E0%B8%A1%E0%B8%8A%E0%B8%B1%E0%B9%88%E0%B8%99_%E0%B8%A5%E0%B8%94%E0%B8%A3%E0%B8%B2%E0%B8%84%E0%B8%B2_Instagram_%E0%B9%82%E0%B8%9E%E0%B8%AA%E0%B8%95%E0%B9%8C__1__7.jpg?v=202405291424" },
         { id: 4, name: "Double Bell 723Y M1911A1 silver", price: 2600, image: "https://image.makewebcdn.com/makeweb/m_1920x0/pBDC0uceT/Pistols/_%E0%B8%81%E0%B8%B2%E0%B9%81%E0%B8%9F_%E0%B9%80%E0%B8%A1%E0%B8%99%E0%B8%B9%E0%B9%80%E0%B8%84%E0%B8%A3%E0%B8%B7%E0%B9%88%E0%B8%AD%E0%B8%87%E0%B8%94%E0%B8%B7%E0%B9%88%E0%B8%A1_%E0%B9%82%E0%B8%9B%E0%B8%A3%E0%B9%82%E0%B8%A1%E0%B8%8A%E0%B8%B1%E0%B9%88%E0%B8%99_%E0%B8%A5%E0%B8%94%E0%B8%A3%E0%B8%B2%E0%B8%84%E0%B8%B2_Instagram_%E0%B9%82%E0%B8%9E%E0%B8%AA%E0%B8%95%E0%B9%8C_7.jpg?v=202405291424" },
        { id: 5, name: "Double Bell 793Y V10 Ultracompact .45", price: 2950, image: "https://image.makewebcdn.com/makeweb/m_1920x0/pBDC0uceT/Pistols/_%E0%B8%81%E0%B8%B2%E0%B9%81%E0%B8%9F_%E0%B9%80%E0%B8%A1%E0%B8%99%E0%B8%B9%E0%B9%80%E0%B8%84%E0%B8%A3%E0%B8%B7%E0%B9%88%E0%B8%AD%E0%B8%87%E0%B8%94%E0%B8%B7%E0%B9%88%E0%B8%A1_%E0%B9%82%E0%B8%9B%E0%B8%A3%E0%B9%82%E0%B8%A1%E0%B8%8A%E0%B8%B1%E0%B9%88%E0%B8%99_%E0%B8%A5%E0%B8%94%E0%B8%A3%E0%B8%B2%E0%B8%84%E0%B8%B2_Instagram_%E0%B9%82%E0%B8%9E%E0%B8%AA%E0%B8%95%E0%B9%8C__1__8.jpg?v=202405291424" },
        { id: 6, name: "Army Armament R609 sv (2025)", price: 3900, image: "https://image.makewebcdn.com/makeweb/m_1920x0/pBDC0uceT/Pistols/26.jpg?v=202405291424" }
      ]
    }
  }
}
</script>
