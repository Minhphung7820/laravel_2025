<template>
  <div>
    <h2 class="text-xl font-bold mb-4">
      {{ mode === 'update' ? 'Sửa kho' : 'Thêm kho' }}
    </h2>

    <div class="mb-4">
      <label class="block mb-1 font-medium">Tên kho</label>
      <input v-model="form.name" class="w-full border px-4 py-2 rounded" />
    </div>

    <button @click="submit"
      class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-semibold">
      💾 Lưu
    </button>
  </div>
</template>

<script>
export default {
  props: {
    mode: { type: String, default: 'create' },
    id: Number
  },
  data() {
    return {
      form: { name: '' }
    }
  },
  mounted() {
    if (this.mode === 'update') this.fetch()
  },
  methods: {
    async fetch() {
      const res = await window.axios.get(`/api/warehouse/stock/detail/${this.id}`)
      this.form = res.data
    },
    async submit() {
      const url = this.mode === 'create'
        ? '/api/warehouse/stock/create'
        : `/api/warehouse/stock/update/${this.id}`

      await window.axios.post(url, this.form)
      this.$router.push('/warehouse/stock')
    }
  }
}
</script>
