<template>
  <div class="loader-overlay" v-if="visible">
    <div class="loader"></div>
  </div>
</template>

<style scoped>
.loader-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.2);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.loader {
  width: 45px;
  aspect-ratio: 1;
  --c: no-repeat linear-gradient(#000 0 0);
  background:
    var(--c) 0% 50%,
    var(--c) 50% 50%,
    var(--c) 100% 50%;
  background-size: 20% 100%;
  animation: l1 1s infinite linear;
}

@keyframes l1 {
  0% {
    background-size: 20% 100%, 20% 100%, 20% 100%
  }

  33% {
    background-size: 20% 10%, 20% 100%, 20% 100%
  }

  50% {
    background-size: 20% 100%, 20% 10%, 20% 100%
  }

  66% {
    background-size: 20% 100%, 20% 100%, 20% 10%
  }

  100% {
    background-size: 20% 100%, 20% 100%, 20% 100%
  }
}
</style>
<script setup>
defineProps({
  visible: Boolean
})
</script>