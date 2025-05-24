<template>
  <div>
    <h2 class="text-xl font-bold mb-4">
      {{ mode === 'update' ? 'Sửa thương hiệu' : 'Thêm thương hiệu' }}
    </h2>

    <!-- Tên -->
    <div class="mb-4">
      <label class="block mb-1 font-medium">Tên thương hiệu</label>
      <input v-model="form.name" class="w-full border px-4 py-2 rounded" />
    </div>

    <!-- Mô tả -->
    <div class="mb-4">
      <label class="block mb-1 font-medium">Mô tả</label>
      <textarea v-model="form.description" class="w-full border px-4 py-2 rounded"></textarea>
    </div>

    <!-- Logo -->
    <div class="mb-4">
      <label class="block mb-1 font-medium">Logo</label>
      <input type="file" @change="handleFile" />

      <!-- Preview -->
      <div v-if="form.logo_url" class="mb-2 mt-2 relative w-[120px]">
        <img :src="form.logo_url" alt="Preview" class="rounded shadow w-[120px] h-[120px] object-cover border" />
        <button @click="removeLogo" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700 cursor-pointer">✕</button>
      </div>
    </div>

    <!-- Submit -->
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
      form: {
        name: '',
        description: '',
        logo: null,
        logo_url: '',
      },
      remove_logo: false
    }
  },
  mounted() {
    if (this.mode === 'update') this.fetch()
  },
  methods: {
    async fetch() {
      const res = await window.axios.get(`/api/warehouse/brand/detail/${this.id}`)
      this.form.name = res.data.name
      this.form.description = res.data.description
      this.form.logo_url =  res.data.logo_url
    },
    handleFile(e) {
      const file = e.target.files[0]
      this.form.logo = file

      // Preview
      if (file) {
        this.form.logo_url = URL.createObjectURL(file)
        this.remove_logo = false
      }
    },
    removeLogo() {
      this.form.logo = null
      this.form.logo_url = ''
      this.remove_logo = true
    },
    async submit() {
      const formData = new FormData()
      for (const key in this.form) {
        if (this.form[key] !== null) {
          formData.append(key, this.form[key])
        }
      }

      if (this.remove_logo) {
        formData.append('remove_logo', '1')
      }

      const url = this.mode === 'create'
        ? '/api/warehouse/brand/create'
        : `/api/warehouse/brand/update/${this.id}`

      await window.axios.post(url, formData)
      this.$router.push('/warehouse/brand')
    }
  }
}
</script>
