import { createRouter, createWebHistory } from "vue-router";
import LoginView from "@/views/LoginView.vue";
import ProfileView from "@/views/ProfileView.vue";
import HomeView from "@/views/HomeView.vue";
import RewardsView from "@/views/RewardsView.vue";
import RewardsHistoryView from "@/views/RewardsHistoryView.vue";
import TopupView from "@/views/TopupView.vue";
import RewardsCategoryView from "@/views/RewardsCategoryView.vue";
const routes = [
  { path: "/", redirect: "/login" },
  { path: "/login", component: LoginView },
  { path: "/home", component: HomeView },
  { path: "/profile", component: ProfileView },
  { path: "/rewards", component: RewardsView },
  { path: "/rewards/category/:category", component: RewardsCategoryView },
  { path: "/rewardsHistory", component: RewardsHistoryView },
  { path: "/topup", component: TopupView },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
