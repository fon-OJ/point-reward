<template>
  <section class="vh-100 ">
    <div class="container py-5 h-50 my-auto">
      <div class="row d-flex align-items-center justify-content-center h-100">
        <div class="col-md-8 col-lg-7 col-xl-6">
          <img src="/assets/icon.png" class="img-fluid" alt="cover image">
        </div>
        <div class="col-md-7 col-lg-5 col-xl-5 offset-xl-1">
          <form @submit.prevent="handleLogin" id="login-form">
            <!-- Email input -->
            <div class="form-outline my-4 ">
              <label class="form-label" for="email">Email address</label>
              <input type="email" id="email" v-model="email" class="form-control" autocomplete="email" required>
            </div>

            <!-- Password input -->
            <div class="form-outline mb-3">
              <label class="form-label" for="password">Password</label>
              <input type="password" v-model="password" id="password" class="form-control"
                autocomplete="current-password" required>

            </div>

            <div class="d-flex justify-content-end mb-2">
              <a href="#!">Forgot password?</a>
            </div>

            <!-- Submit button -->
            <div class="text-center">
              <button type="submit" class="btn btn-primary btn-lg w-100">Sign in</button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'

const email = ref('')
const password = ref('')
const router = useRouter()

const handleLogin = async () => {
  try {
    const res = await axios.post('/login', {
      email: email.value,
      password: password.value,
    }, {
      headers: {
        'Content-Type': 'application/json'
      }
    })

    Swal.fire({
      icon: 'success',
      title: 'เข้าสู่ระบบสำเร็จ',
      text: `ยินดีต้อนรับ ${res.data.user.name}`,
      timer: 2000,
      showConfirmButton: false
    })

    localStorage.setItem('token', res.data.access_token)
    router.push('/home')
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'เกิดข้อผิดพลาด',
      text: 'เข้าสู่ระบบไม่สำเร็จ'
    })
  }
}
</script>