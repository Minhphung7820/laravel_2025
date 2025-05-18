<template>
  <div class="about">
    <h1>Về chúng tôi</h1>
    <p>
      Đây là trang giới thiệu về nhóm phát triển ứng dụng SPA sử dụng Vue 3 và Laravel 12.
      Mục tiêu là tạo ra trải nghiệm mượt mà, hiện đại và thân thiện với người dùng.
    </p>
    <button
      class="btn-rgb"
      :class="{ effect: isHovered }"
      @mouseenter="triggerAnimation"
    >
      Hover thử nè 😈
    </button>
  </div>
</template>

<script>
export default {
  name: 'About',
  data() {
    return {
      isHovered: false
    };
  },
  methods: {
    triggerAnimation() {
      this.isHovered = true;
      setTimeout(() => {
        this.isHovered = false;
      }, 1000); // giữ 1 giây để hoàn tất animation
    }
  }
};
</script>

<style scoped>
.btn-rgb {
  padding: 16px 32px;
  border: none;
  border-radius: 12px;
  font-size: 18px;
  font-weight: bold;
  color: white;
  background-color: #111;
  cursor: pointer;
  transition: transform 0.6s ease;
  animation: idleGlow 5s linear infinite;
}

/* Kích hoạt khi hover (bằng class 'effect') */
.btn-rgb.effect {
  animation: rotateAndRGB 1s linear forwards, rgbGlow 1s linear;
}

/* Xoay 360 độ và giữ màu cuối */
@keyframes rotateAndRGB {
  from {
    transform: rotate(0deg);
    background-color: #111;
  }
  to {
    transform: rotate(360deg);
    background-color: #0ff;
  }
}

/* Hiệu ứng RGB loop */
@keyframes rgbGlow {
  0%   { background-color: red; }
  25%  { background-color: orange; }
  50%  { background-color: lime; }
  75%  { background-color: cyan; }
  100% { background-color: violet; }
}

/* Hiệu ứng glow nhẹ khi không hover */
@keyframes idleGlow {
  0%, 100% { box-shadow: 0 0 8px #0ff; }
  50%      { box-shadow: 0 0 20px #0ff; }
}

.about {
  padding: 20px;
  background: #e3f2fd;
  border-radius: 10px;
  border: 1px solid #90caf9;
  color: #0d47a1;
}

h1 {
  margin-bottom: 10px;
  font-size: 24px;
}
</style>
