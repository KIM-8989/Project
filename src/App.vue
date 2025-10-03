<template>
<nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
  <div class="container-fluid">
    <router-link class="navbar-brand" to="/">Project</router-link>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <router-link class="nav-link active" aria-current="page" to="/">Home</router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link" to="/showproduct">Show Product</router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link" to="/product">Product</router-link>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Customers
          </a>
          <ul class="dropdown-menu">
            <li><router-link class="dropdown-item" to="/Customers">Customer</router-link></li>
            <li><router-link class="dropdown-item" to="/edit_customers">Edit Customer</router-link></li>
            <li><hr class="dropdown-divider"></li>
            <li><router-link class="dropdown-item" to="/add_customers">Add Customer</router-link></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          Student
        </a>
        <ul class="dropdown-menu">
        <li><router-link class="dropdown-item" to="/Student">Student</router-link></li>
        <li><router-link class="dropdown-item" to="/edit_student">Edit Student</router-link></li>
        <li><hr class="dropdown-divider"></li>
        <li><router-link class="dropdown-item" to="/add_student">Add Student</router-link></li>
        </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Login
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">login</a></li>
            <li><a class="dropdown-item" href="#">logout</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><router-link class="dropdown-item" to="/add_customers">register</router-link></li>
          </ul>
        </li>
        <li class="nav-item">
          <router-link class="nav-link" to="/about">About</router-link>
        </li>
      </ul>
      <form class="d-flex" role="search" @submit.prevent>
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search"/>
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
    </div>
  </div>
</nav>

  <router-view/>
</template>