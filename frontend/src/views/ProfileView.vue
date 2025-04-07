<template>
  <Header />

  <section class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h3 class="mb-4 text-center">แก้ไขโปรไฟล์</h3>

            <div v-if="user">
              <form @submit.prevent="saveProfile">
                <div class="mb-3">
                  <label for="name" class="form-label">ชื่อ - นามสกุล</label>
                  <input type="text" id="name" class="form-control" v-model="user.name" required />
                </div>

                <div class="mb-3">
                  <label for="email" class="form-label">อีเมล</label>
                  <input type="email" id="email" class="form-control" v-model="user.email" required />
                </div>

                <div class="mb-3">
                  <label class="form-label">แต้มสะสม</label>
                  <input type="number" class="form-control" :value="user.points" disabled />
                </div>

                <div class="d-grid gap-2 mt-4">
                  <button class="btn btn-success" type="submit">
                    <font-awesome-icon class="me-2" :icon="['fas', 'floppy-disk']" /> บันทึก
                  </button>
                </div>
              </form>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>

  <Footer />
</template>

<script setup>
import { onMounted } from 'vue'
import Swal from 'sweetalert2'
import { useRouter } from 'vue-router'
import axios from 'axios'

import { user, fetchUser, clearUser, updateProfile } from '@/stores/userStore'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'

const router = useRouter()

const saveProfile = async () => {
  try {
    const response = await updateProfile(user.value)

    if (response.require_relogin) {
      Swal.fire({
        icon: 'info',
        title: 'เปลี่ยนอีเมลสำเร็จ',
        text: 'กรุณาเข้าสู่ระบบใหม่',
        confirmButtonText: 'ตกลง'
      }).then(() => {
        localStorage.removeItem('token')
        clearUser()
        router.push('/login')
      })
    } else {
      Swal.fire({
        icon: 'success',
        title: 'บันทึกสำเร็จ',
        text: response.message,
      })
    }
  } catch (err) {
    Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถบันทึกข้อมูลได้', 'error')
  }
}

onMounted(() => {
  fetchUser()
})
</script>