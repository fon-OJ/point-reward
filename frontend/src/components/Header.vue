<template>
  <Loader v-if="!user" />
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3">
    <div class="container-fluid">
      <a class="navbar-brand" href="/home">
        <img src="/assets/icon.png" alt="Logo" width="40" height="40"
          class="d-inline-block align-text-top bg-white rounded-circle me-2">
        Point Reward
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText"
        aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarText">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link" href="/rewards">Rewards</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/topup">เติมคะแนน</a>
          </li>
        </ul>

        <ul class="navbar-nav d-flex align-items-center gap-3" v-if="user">
          <li class="nav-item text-white">
            <span class="fw-bold">
              <font-awesome-icon class="me-2 text-warning" :icon="['fas', 'star']" />
              แต้มของฉัน: {{ user.points }}
            </span>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              {{ user?.name || 'Guest' }}
            </a>

            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/profile">แก้ไขโปรไฟล์</a></li>
              <li>
                <hr class="dropdown-divider" />
              </li>
              <li><a class="dropdown-item" href="#" @click.prevent="logout">ออกจากระบบ</a></li>
            </ul>
          </li>
        </ul>

      </div>
    </div>
  </nav>
</template>

<script setup>
import { user, fetchUser } from '@/stores/userStore.js'
import Swal from 'sweetalert2'
import { onMounted } from 'vue'
import axios from 'axios'
import Loader from '@/components/Loader.vue'

const logout = async () => {
  try {
    await axios.post('/logout', {}, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
  } catch (err) {
    console.error('Logout error:', err)
    Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถออกจากระบบได้', 'error')
    return
  }

  localStorage.removeItem('token')
  Swal.fire('ออกจากระบบแล้ว', '', 'success').then(() => {
    window.location.href = '/login'
  })
}

onMounted(async () => {
  if (localStorage.getItem('token') && !user.value) {
    fetchUser()
  }
})
</script>

<style scoped>
.navbar {
  position: sticky;
  top: 0;
  z-index: 1000;
}
</style>
