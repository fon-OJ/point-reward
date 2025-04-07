<template>
  <div class="d-flex flex-column min-vh-100">
    <Header />
    <section class="flex-fill bg-light py-5">
      <div class="container">
        <div>
          <a href="/rewards" class="text-decoration-none text-primary"><font-awesome-icon
              :icon="['fas', 'arrow-left']" /> กลับ</a>
        </div>
        <h2 class="text-center mb-4">ประเภท Reward {{ categoryName.toUpperCase() }} </h2>

        <div class="row">
          <div class="col-12 col-sm-6 col-md-3 mb-4" v-for="reward in rewards" :key="reward.id">
            <div class="card h-100 shadow-sm">
              <img :src="reward.image" class="card-img-top p-3" alt="Reward Image"
                style="height: 150px; object-fit: contain;" />
              <div class="card-body d-flex flex-column">
                <h6 class="card-title">{{ reward.name }}</h6>
                <p class="card-text small text-muted">
                  ใช้แต้ม {{ reward.points }} แต้ม
                </p>
                <button class="btn btn-outline-secondary btn-sm mb-2" @click="showRewardDetail(reward)">
                  ดูรายละเอียด
                </button>
                <button class="btn btn-primary btn-sm mt-auto" @click="redeem(reward.id)"
                  :disabled="(user?.points ?? 0) < reward.points">
                  แลกของรางวัล
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <nav v-if="lastPage > 1" class="mt-4 d-flex justify-content-center">
          <ul class="pagination">
            <li class="page-item" :class="{ disabled: currentPage === 1 }" @click="changePage(currentPage - 1)">
              <a class="page-link">Previous</a>
            </li>
            <li class="page-item" v-for="page in lastPage" :key="page" :class="{ active: currentPage === page }"
              @click="changePage(page)">
              <a class="page-link">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === lastPage }" @click="changePage(currentPage + 1)">
              <a class="page-link">Next</a>
            </li>
          </ul>
        </nav>
      </div>

      <!-- Reward Detail Modal -->
      <div class="modal fade" id="rewardDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ selectedReward.name }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <img :src="selectedReward.image" class="img-fluid mb-3" alt="Reward Image" />
              <p><strong>ใช้แต้ม:</strong> {{ selectedReward.points }} แต้ม</p>
              <p v-if="selectedReward.expiry_date"><strong>หมดอายุ:</strong> {{ formatDate(selectedReward.expiry_date)
              }}</p>
              <p v-if="selectedReward.details"><strong>รายละเอียด:</strong> {{ selectedReward.details }}</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
              <button type="button" class="btn btn-primary" :disabled="(user?.points ?? 0) < selectedReward.points"
                @click="redeem(selectedReward.id)" data-bs-dismiss="modal">
                แลกของรางวัล
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>
    <Footer />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import Swal from 'sweetalert2'
import axios from 'axios'
import { Modal } from 'bootstrap'
import { user, fetchUser, clearUser } from '@/stores/userStore'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'
const route = useRoute()
const rewards = ref([])
const categoryName = ref('')
const currentPage = ref(1)
const lastPage = ref(1)
const loading = ref(false)
const searchTerm = ref('')
const sortBy = ref('newest')
const filterPoints = ref('all')
const searchTimeout = ref(null)

const selectedReward = ref({})
let rewardModal = null

// Calculate visible pagination range
const paginationRange = computed(() => {
  const range = []
  const maxVisible = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(lastPage.value, start + maxVisible - 1)

  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }

  for (let i = start; i <= end; i++) {
    range.push(i)
  }
  return range
})



const showRewardDetail = (reward) => {
  selectedReward.value = reward
  if (!rewardModal) {
    const el = document.getElementById('rewardDetailModal')
    if (el) rewardModal = new Modal(el)
  }
  rewardModal?.show()
}

const fetchRewards = async (page = 1) => {
  loading.value = true
  const category = route.params.category
  categoryName.value = category

  try {
    // Build query parameters
    const params = new URLSearchParams({
      page: page,
      per_page: 12,
      sort: sortBy.value
    })

    if (searchTerm.value) {
      params.append('search', searchTerm.value)
    }

    if (filterPoints.value === 'available') {
      params.append('available_only', true)
    }

    const res = await axios.get(`/rewards/categories/${category}?${params.toString()}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })

    rewards.value = res.data.data
    currentPage.value = res.data.current_page
    lastPage.value = res.data.last_page
  } catch (err) {
    console.error('Error fetching rewards:', err)
    Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถโหลดของรางวัลได้', 'error')
  } finally {
    loading.value = false
  }
}

const searchRewards = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  searchTimeout.value = setTimeout(() => {
    fetchRewards(1)
  }, 300)
}

const changePage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    fetchRewards(page)
  }
}

const redeem = async (rewardId) => {
  const result = await Swal.fire({
    title: 'ยืนยันการแลกของรางวัล',
    text: 'คุณต้องการแลกของรางวัลนี้หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'ยืนยัน',
    cancelButtonText: 'ยกเลิก'
  })

  if (!result.isConfirmed) return

  try {
    const { data } = await axios.post('/rewards/redeem', {
      reward_id: rewardId
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })

    if (data.user) {
      user.value.points = data.user.points
    }

    await Swal.fire('สำเร็จ', data.message || 'แลกของรางวัลสำเร็จ', 'success')
    fetchRewards(currentPage.value)

  } catch (err) {
    console.error('Redeem error:', err)
    const msg = err.response?.data?.error || 'ไม่สามารถแลกของรางวัลได้'
    Swal.fire('เกิดข้อผิดพลาด', msg, 'error')
  }
}

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(async () => {
  await fetchUser()
  fetchRewards()
})
</script>