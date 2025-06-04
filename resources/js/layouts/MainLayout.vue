<template>
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside
      :class="[
        'bg-[#1e293b] text-white transition-all duration-100 shadow-lg z-20 flex-shrink-0 overflow-y-auto flex flex-col items-center pt-4',
        showSidebar ? 'w-64 px-4' : 'w-16'
      ]"
    >
      <div v-if="showSidebar && activeModule?.name" class="font-bold text-lg mb-4 text-left w-full">
        {{ $t(activeModule.name) }}
      </div>
      <ul class="w-full" v-if="activeModule?.epics?.length">
        <li v-for="item in activeModule.epics" :key="item.name" class="mb-2">
          <router-link
            :to="item.path"
            class="group flex items-center rounded hover:bg-blue-600 transition relative w-full"
            :class="[
              showSidebar ? 'px-3 py-2 justify-start' : 'w-10 h-10 mx-auto justify-center',
              $route.path.startsWith(item.path) ? 'bg-blue-700' : ''
            ]"
          >
            <template v-if="showSidebar && item?.name">
              {{ $t(item.name) }}
            </template>
            <template v-else>
              <div class="w-full flex justify-center items-center relative group">
                <!-- Mũi tên > -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <!-- Tooltip -->
                <span
                  class="absolute left-full ml-2 bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-50"
                >
                  {{ item?.name ? $t(item.name) : '' }}
                </span>
              </div>
            </template>
          </router-link>
        </li>
      </ul>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Nav -->
      <header class="bg-[#0f172a] text-white flex items-center justify-between px-6 py-3 shadow z-10">
        <div class="flex items-center gap-4">
          <!-- Nút toggle 3 gạch -->
          <button @click="toggleSidebar" class="text-white hover:text-blue-400 focus:outline-none" title="Thu gọn/mở sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <!-- Modules -->
          <nav class="flex gap-3">
            <button
              v-for="module in modules"
              :key="module.name"
              @click="selectModule(module)"
              class="px-3 py-1 rounded hover:bg-blue-600"
              :class="{ 'bg-blue-700': activeModule.name === module.name }"
            >
              {{ module?.name ? $t(module.name) : '' }}
            </button>
          </nav>
        </div>

        <!-- Language + Logout -->
        <div class="flex items-center gap-4">
          <div class="relative">
            <select
              v-model="selectedLang"
              @change="changeLanguage"
              class="appearance-none bg-[#1e293b] text-white border border-white rounded px-2 py-1 pr-8"
            >
              <option value="vi">🇻🇳 Tiếng Việt</option>
              <option value="en">🇺🇸 English</option>
              <option value="ja">🇯🇵 日本語</option>
              <option value="ko">🇰🇷 한국어</option>
              <option value="zh">🇨🇳 中文（简体）</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-white">
              ▼
            </div>
          </div>

          <button v-if="isLoggedIn" @click="handleLogout" class="text-white hover:text-red-400 font-bold">
            {{ $t('app.logout') }}
          </button>
        </div>
      </header>

      <!-- Content -->
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
      selectedLang: localStorage.getItem('lang') || 'vi',
      showSidebar: true,
      modules: [
        {
          name: 'menu.home',
          path: '/',
          epics: []
        },
        {
          name: 'menu.sale',
          epics: [
            { name: 'menu.customer', path: '/sale/customer' },
            { name: 'menu.quote', path: '/quotes' },
            { name: 'menu.order', path: '/orders' },
            { name: 'menu.return', path: '/returns' }
          ]
        },
        {
          name: 'menu.purchase',
          epics: [
            { name: 'menu.supplier', path: '/purchase/supplier' },
            { name: 'menu.purchase_request', path: '/quotes' },
            { name: 'menu.purchase_order', path: '/orders' },
            { name: 'menu.purchase_return', path: '/returns' }
          ]
        },
        {
          name: 'menu.warehouse',
          epics: [
            { name: 'menu.product', path: '/warehouse/product' },
            { name: 'menu.category', path: '/warehouse/category' },
            { name: 'menu.brand', path: '/warehouse/brand' },
            { name: 'menu.unit', path: '/warehouse/unit' },
            { name: 'menu.stock', path: '/warehouse/stock' }
          ]
        },
        {
          name: 'menu.support',
          epics: [
            { name: 'menu.complaint', path: '/support/complaints' },
            { name: 'menu.feedback', path: '/support/feedbacks' }
          ]
        },
        {
          name: 'menu.marketing',
          epics: [
            { name: 'menu.campaign', path: '/marketing/campaigns' },
            { name: 'menu.promotion', path: '/marketing/promotions' }
          ]
        }
      ],
      activeModule: {
        name: '',
        epics: []
      }
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
    changeLanguage() {
      localStorage.setItem('lang', this.selectedLang)
      this.$i18n.locale = this.selectedLang
      window.axios.defaults.headers.common['Accept-Language'] = this.selectedLang
      location.reload()
    },
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
      this.activeModule = found || { name: '', epics: [] }
    }
  }
}
</script>

<style scoped>
</style>
