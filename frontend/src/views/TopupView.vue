<template>
  <Header />

  <div class="container">
    <div class="col-12">
      <h4 class="text-center mt-4">เติมคะแนน</h4>

      <div class="card mx-auto mt-4" style="max-width: 500px;">
        <div class="card-body">
          <form @submit.prevent="submitTopup">
            <div class="mb-3">
              <label for="topup_code" class="form-label">Top Code</label>
              <input type="text" class="form-control" id="topup_code" v-model="topup_code" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary w-100" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              {{ loading ? 'กำลังดำเนินการ...' : 'เติมคะแนน' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import axios from 'axios'
import { user, fetchUser, clearUser } from '@/stores/userStore'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'

const router = useRouter()
const topup_code = ref('')
const paymentMethod = ref('')
const loading = ref(false)

const submitTopup = async () => {
  loading.value = true

  try {
    const { data } = await axios.post('/topup', {
      topup_code: topup_code.value
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })

    await Swal.fire({
      title: 'สำเร็จ!',
      text: data.message || 'เติมคะแนนสำเร็จ',
      icon: 'success'
    })

    // Refresh user data
    await fetchUser()

    // Reset form
    topup_code.value = ''

  } catch (err) {
    console.error('Topup error:', err)
    const errorMessage = err?.response?.data?.error || 'เกิดข้อผิดพลาดในการเติมคะแนน'
    Swal.fire('ข้อผิดพลาด', errorMessage, 'warning')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.card {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>