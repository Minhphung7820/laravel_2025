<template>
  <div v-if="loading" class="fixed inset-0 bg-white bg-opacity-60 z-50 flex items-center justify-center">
    <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
    </svg>
  </div>
  <div class="w-full p-8 bg-white rounded-xl shadow-md">
    <h2 class="text-2xl font-bold mb-6">
      {{ mode === 'update' ? $t('stock.edit_title') : $t('stock.add_title') }}
    </h2>

    <!-- Tên kho -->
    <div class="mb-4">
      <label class="block mb-1 font-medium">
        {{ $t('stock.name') }} <span class="text-red-500">*</span>
      </label>
      <input
        v-model="form.name"
        :placeholder="$t('stock.name')"
        class="w-1/2 border px-4 py-2 rounded"
        :class="{ 'border-red-500': !form.name.trim() }"
      />
      <p v-if="!form.name.trim()" class="text-red-500 text-xs mt-1">
        {{ $t('stock.required') }}
      </p>
    </div>

    <!-- Submit -->
    <button
      @click="submit"
      class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-semibold"
    >
      💾 {{ $t('stock.save') }}
    </button>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  props: {
    mode: { type: String, default: 'create' },
    id: Number
  },
  data() {
    return {
      form: { name: '' },
      loading : true
    }
  },
  computed: {
    ...mapGetters('cache', ['getAllCacheKeys'])
  },
  async mounted() {
    if (this.mode === 'update') await this.fetch()
    this.loading = false
  },
  methods: {
    ...mapActions('cache', ['clearCacheKey']),
    resetCacheableListByKey() {
      const allKeys = this.getAllCacheKeys('stock')
      const listKeys = allKeys.filter(key =>
        key.includes('page')
      )
      listKeys.forEach(key => {
        this.clearCacheKey({ module: 'stock', key })
      })
    },
    async fetch() {
      const res = await window.axios.get(`/api/warehouse/stock/detail/${this.id}`)
      this.form = res.data.data
    },
    async submit() {
     this.loading = true
     try {
      if (!this.form.name.trim()) return

      const url = this.mode === 'create'
        ? '/api/warehouse/stock/create'
        : `/api/warehouse/stock/update/${this.id}`

      await window.axios.post(url, this.form)
      this.resetCacheableListByKey()
      this.$router.push('/warehouse/stock')
     } catch (error) {

     } finally {
      this.loading = false
     }
    }
  }
}
</script>
