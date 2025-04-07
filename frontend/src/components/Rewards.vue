<template>
  <div class="row my-5">
    <div v-for="category in categories" :key="category" class="mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 text-capitalize fw-bold">{{ category }}</h4>
        <router-link :to="`/rewards/category/${category}`" class="text-decoration-none text-primary fw-medium">
          ดูทั้งหมด <i class="bi bi-chevron-right"></i>
        </router-link>
      </div>

      <Carousel v-if="rewardsByCategory[category]?.length" :items-to-show="calculateItemsToShow()" :wrap-around="true"
        class=" border-bottom pb-4" :mouse-drag="true">
        <Slide v-for="item in rewardsByCategory[category]" :key="item.id">
          <div class="card h-100 mx-2 border-0 shadow-sm hover-shadow transition-all ">
            <div class="card-img-container">
              <img :src="item.image" class="card-img-top" alt="Reward image" loading="lazy">
            </div>
            <div class="card-body text-center">
              <h6 class="card-title fw-semibold text-truncate">{{ item.name }}</h6>
              <p class="small text-muted mb-2">ใช้แต้ม {{ item.points }}</p>
              <button class="btn btn-sm btn-outline-primary w-100" @click="showRewardDetail(item.id)">
                ดูรายละเอียด
              </button>
            </div>
          </div>
        </Slide>

        <template #addons>
          <Navigation />
        </template>
      </Carousel>
    </div>
  </div>
  <!-- Modal  -->
  <div class="modal fade" id="rewardDetailModal" tabindex="-1" aria-labelledby="rewardDetailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-dialog modal-lg" v-if="selectedReward">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="rewardDetailModalLabel">{{ selectedReward.name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-12">
                <div class="reward-image-container mb-3">
                  <img :src="selectedReward.image" class="img-fluid rounded" :alt="selectedReward.name">
                </div>
                <div class="text-center">
                  <div class="mb-3">
                    <span class="badge bg-success p-2 fs-6">ใช้แต้ม {{ selectedReward.points }}</span>
                  </div>
                  <button @click="redeemReward" class="btn btn-primary py-2 w-100"
                    :disabled="isLoading || userPoints < selectedReward.points">
                    <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"
                      aria-hidden="true"></span>
                    <font-awesome-icon :icon="['fas', 'star']" /> แลกคะแนน
                  </button>
                  <p v-if="userPoints < selectedReward.points" class="text-danger small mt-2">
                    แต้มสะสมของคุณไม่เพียงพอ
                  </p>
                </div>
              </div>
              <div class="col-md-12 mt-4">
                <h6 class="fw-bold">รายละเอียด</h6>
                <div v-html="selectedReward.description"></div>

                <div class="mt-3" v-if="selectedReward.terms">
                  <h6 class="fw-bold">เงื่อนไขการใช้งาน</h6>
                  <div v-html="selectedReward.terms"></div>
                </div>

                <div class="mt-3" v-if="selectedReward.expiry_date">
                  <h6 class="fw-bold">วันหมดอายุ</h6>
                  <p>{{ formatDate(selectedReward.expiry_date) }}</p>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import axios from 'axios'
import { Carousel, Slide, Navigation } from 'vue3-carousel'
import 'vue3-carousel/dist/carousel.css'

import { Modal } from 'bootstrap'
import { user, fetchUser, clearUser, updateProfile } from '@/stores/userStore'
import Swal from 'sweetalert2'

const categories = ref([])
const rewardsByCategory = ref({})
const windowWidth = ref(window.innerWidth)
const selectedReward = ref(null)
const isLoading = ref(false)

// Add the missing userPoints computed property
const userPoints = computed(() => user.value?.points || 0)

let detailModal = null

const calculateItemsToShow = () => {
  if (windowWidth.value < 576) return 1
  if (windowWidth.value < 768) return 2
  if (windowWidth.value < 992) return 3
  return 4
}

const handleResize = () => {
  windowWidth.value = window.innerWidth
}

const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(dateString).toLocaleDateString('th-TH', options)
}

onMounted(async () => {
  window.addEventListener('resize', handleResize)

  // เตรียม modal instance
  detailModal = new Modal(document.getElementById('rewardDetailModal'))

  try {
    // ดึงข้อมูลผู้ใช้จาก store
    await fetchUser()

    // ดึงหมวดหมู่รางวัล
    const catRes = await axios.get('/rewards/categories', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
    categories.value = catRes.data

    // ดึงรางวัลในแต่ละหมวดหมู่
    for (const cat of categories.value) {
      const res = await axios.get(`/rewards/categories/${cat}?per_page=8`, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      })
      rewardsByCategory.value[cat] = res.data.data
    }
  } catch (error) {
    console.error('Error loading rewards:', error)
  }
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
})

const showRewardDetail = async (id) => {
  try {
    isLoading.value = true

    // เรียก API เพื่อดึงข้อมูลรายละเอียดของรางวัล
    const response = await axios.get(`/rewards/${id}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })

    selectedReward.value = response.data
    detailModal.show()
  } catch (error) {
    console.error('Error fetching reward details:', error)
    Swal.fire({
      icon: 'error',
      title: 'ไม่สามารถดึงข้อมูลได้',
      text: 'ไม่สามารถดึงข้อมูลรางวัลได้ กรุณาลองใหม่อีกครั้ง',
      confirmButtonText: 'ตกลง'
    })
  } finally {
    isLoading.value = false
  }
}

const redeemReward = async () => {
  if (!selectedReward.value || !user.value || user.value.points < selectedReward.value.points) {
    return
  }

  try {
    isLoading.value = true

    // เรียก API เพื่อแลกรางวัล
    await axios.post(`/rewards/redeem`, {
      reward_id: selectedReward.value.id
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })

    // อัพเดตคะแนนของผู้ใช้ใน store
    if (user.value) {
      await fetchUser()
    }

    // ปิด modal รายละเอียด
    detailModal.hide()

    // แสดงผลสำเร็จด้วย Swal
    Swal.fire({
      icon: 'success',
      title: 'แลกรางวัลสำเร็จ!',
      html: `
        <p>คุณได้แลกรางวัล "${selectedReward.value.name}" เรียบร้อยแล้ว</p>
      `,
      confirmButtonText: 'ตกลง'
    })
  } catch (error) {
    console.error('Error redeeming reward:', error)

    // แสดงข้อความผิดพลาดด้วย Swal
    Swal.fire({
      icon: 'error',
      title: 'แลกรางวัลไม่สำเร็จ',
      text: error.response?.data?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง',
      confirmButtonText: 'ตกลง'
    })
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.card {
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  margin: 0 4px;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

.card-img-container {
  height: 160px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
}

.card-img-top {
  object-fit: contain;
  height: 100%;
  width: 100%;
  padding: 15px;
}

.carousel {
  padding: 0 8px;
}

.carousel__prev,
.carousel__next {
  background: white;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  color: #333;
  top: 40%;
}

.carousel__prev {
  left: -10px;
}

.carousel__next {
  right: -10px;
}

.carousel__prev--disabled,
.carousel__next--disabled {
  opacity: 0.5;
}

.reward-image-container {
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  border-radius: 8px;
  overflow: hidden;
}

.reward-image-container img {
  max-height: 100%;
  object-fit: contain;
}

@media (max-width: 768px) {
  .card-img-container {
    height: 120px;
  }

  .card-body {
    padding: 12px 8px !important;
  }

  .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem !important;
  }

  .reward-image-container {
    height: 160px;
  }
}

@media (max-width: 576px) {
  .card {
    margin: 0 2px;
  }

  .carousel {
    padding: 0 4px;
  }

  .card-img-container {
    height: 100px;
  }

  h4 {
    font-size: 1.25rem;
  }

  .carousel__prev,
  .carousel__next {
    width: 30px;
    height: 30px;
  }
}
</style>