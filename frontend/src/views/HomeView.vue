<template>
  <Header />

  <div class="container">
    <div class="row mt-4">
      <div class="col-12 d-flex justify-content-center align-items-center flex-column">
        <div class="card w-75 shadow-sm">
          <div class="card-body ">
            <div class="row">
              <div class="col-12">
                <h5>คะแนนสะสมของคุณ</h5>
              </div>
              <div class="col-8">
                <h3 class="text-primary">{{ user?.points ?? 'xx' }}</h3>
              </div>
              <div class="col-4 text-end">
                <a href="#" class="btn btn-sm btn-outline-secondary">
                  ดูรายละเอียดคะแนน
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <Rewards />
  </div>

  <Footer />
</template>

<script setup>
import { onMounted } from 'vue'
import Swal from 'sweetalert2'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { user, fetchUser, clearUser } from '@/stores/userStore'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'

import Rewards from '@/components/Rewards.vue'
const router = useRouter()

onMounted(async () => {
  if (!user.value || !user.value.email) {
    try {
      await fetchUser()
    } catch (err) {
      Swal.fire('หมดเวลาเข้าสู่ระบบ', 'กรุณาเข้าสู่ระบบใหม่', 'error')
      clearUser()
      localStorage.removeItem('token')
      router.push('/login')
    }
  }
})
</script>
