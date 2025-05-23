<template>
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside
      :class="[
        'bg-[#1e293b] text-white transition-all duration-100 shadow-lg z-20 flex-shrink-0 overflow-y-auto flex flex-col items-center pt-4',
        showSidebar ? 'w-64 px-4' : 'w-16'
      ]"
    >
      <div v-if="showSidebar" class="font-bold text-lg mb-4 text-left w-full">
        {{ activeModule.name }}
      </div>
      <ul class="w-full">
        <li
          v-for="item in activeModule.epics"
          :key="item.name"
          class="mb-2"
        >
        <router-link
          :to="item.path"
          class="group flex items-center rounded hover:bg-blue-600 transition relative w-full"
          :class="[
            showSidebar ? 'px-3 py-2 justify-start' : 'w-10 h-10 mx-auto justify-center',
            $route.path.startsWith(item.path) ? 'bg-blue-700' : ''
          ]"
        >
          <template v-if="showSidebar">
            {{ item.name }}
          </template>
          <template v-else>
            <div class="w-full flex justify-center items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
            <span
              class="absolute left-full ml-2 bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-50"
            >
              {{ item.name }}
            </span>
          </template>
        </router-link>
        </li>
      </ul>
    </aside>

    <!-- Main content -->
    <div
      class="flex-1 flex flex-col overflow-hidden transition-all duration-300"
      :class="showSidebar ? 'ml-0' : 'ml-0'"
    >
      <!-- Top Nav -->
      <header class="bg-[#0f172a] text-white flex items-center justify-between px-6 py-3 shadow z-10">
        <div class="flex items-center gap-4">
          <button
            @click="toggleSidebar"
            class="text-white hover:text-blue-400 focus:outline-none"
            title="Thu gọn sidebar"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <nav class="flex gap-3">
            <button
              v-for="module in modules"
              :key="module.name"
              @click="selectModule(module)"
              class="px-3 py-1 rounded hover:bg-blue-600"
              :class="{ 'bg-blue-700': activeModule.name === module.name }"
            >
              {{ module.name }}
            </button>
          </nav>
        </div>

        <button
          v-if="isLoggedIn"
          @click="handleLogout"
          class="text-white hover:text-red-400 font-bold"
        >
          Đăng xuất
        </button>
      </header>

      <!-- Router View -->
      <main class="p-6 overflow-y-auto flex-1 bg-gray-50 transition-all duration-300">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  data() {
    return {
      showSidebar: true,
      modules: [
        {
          name: 'Trang chủ',
          path: '/',
          epics: [
          ]
        },
        {
          name: 'Bán hàng',
          epics: [
            { name: 'Quản lý khách hàng', path: '/sale/customer' },
            { name: 'Báo giá', path: '/quotes' },
            { name: 'Đơn bán hàng', path: '/orders' },
            { name: 'Trả hàng bán', path: '/returns' }
          ]
        },
        {
          name: 'Mua hàng',
          epics: [
            { name: 'Quản lý nhà cung cấp', path: '/sale/customer' },
            { name: 'Yêu cầu mua hàng', path: '/quotes' },
            { name: 'Đơn mua hàng', path: '/orders' },
            { name: 'Trả hàng mua', path: '/returns' }
          ]
        },
        {
          name: 'Kho',
          epics: [
            { name: 'Sản phẩm', path: '/warehouse/product' },
            { name: 'Danh mục', path: '/warehouse/category' },
            { name: 'Nhãn hiệu', path: '/warehouse/brand' },
            { name: 'Đơn vị', path: '/warehouse/unit' },
            { name: 'Kho', path: '/warehouse/stock' }
          ]
        },
        {
          name: 'CSKH',
          epics: [
            { name: 'Khiếu nại', path: '/support/complaints' },
            { name: 'Phản hồi', path: '/support/feedbacks' }
          ]
        },
        {
          name: 'Marketing',
          epics: [
            { name: 'Chiến dịch', path: '/marketing/campaigns' },
            { name: 'Khuyến mãi', path: '/marketing/promotions' }
          ]
        }
      ],
      activeModule: {}
    }
  },
  computed: {
    ...mapGetters('auth', ['isAuthenticated']),
    isLoggedIn() {
      return this.isAuthenticated
    }
  },
  mounted() {
    this.updateActiveModule()
    this.showSidebar = this.$route.path !== '/'
  },
  watch: {
    '$route.path'() {
      this.updateActiveModule()
      this.showSidebar = this.$route.path !== '/'
    }
  },
  methods: {
    ...mapActions('auth', ['logout']),
    async handleLogout() {
      await this.logout()
      this.$router.push('/login')
    },
    toggleSidebar() {
      this.showSidebar = !this.showSidebar
    },
    selectModule(module) {
      this.activeModule = module
      this.showSidebar = true
      if (module.epics?.[0]?.path) {
        this.$router.push(module.epics[0].path)
      } else if (module.path) {
        this.$router.push(module.path)
      }
    },
    updateActiveModule() {
      const currentPath = this.$route.path
      const found = this.modules.find(mod => {
        return mod.epics?.some(epic => currentPath.startsWith(epic.path)) || mod.path === currentPath
      })
      if (found) {
        this.activeModule = found
      }
    }
  }
}
</script>

<style scoped>
</style>
