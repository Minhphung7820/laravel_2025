<template>
  <div v-if="loading" class="fixed inset-0 bg-white bg-opacity-60 z-50 flex items-center justify-center">
    <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
    </svg>
  </div>
  <div class="w-full p-6 bg-white rounded-xl shadow-md">
    <h1 class="text-2xl font-bold">
      {{ mode === 'update' ? $t('brand.edit_title') : $t('brand.add_title') }}
    </h1>

    <!-- Tên -->
    <div class="mb-4">
      <label class="block mb-1 font-medium">{{ $t('brand.name') }} <span class="text-red-500">*</span></label>
      <input
        v-model="form.name"
        :placeholder="$t('brand.name')"
        class="w-1/2 border px-4 py-2 rounded"
        :class="{ 'border-red-500': !form.name.trim() }"
      />
      <p v-if="!form.name.trim()" class="text-red-500 text-xs mt-1">{{ $t('brand.required') }}</p>
    </div>

    <!-- Mô tả -->
    <div class="mb-4">
      <label class="block mb-1 font-medium">{{ $t('brand.description') }}</label>
      <textarea v-model="form.description" class="w-1/2 border px-4 py-2 rounded"></textarea>
    </div>

    <!-- Logo -->
    <div class="mb-4">
      <label class="block mb-1 font-medium">{{ $t('brand.logo') }}</label>

      <div class="w-32 h-32 border-2 border-dashed border-gray-400 rounded relative flex items-center justify-center overflow-hidden cursor-pointer hover:border-blue-500"
           @click="$refs.logoInput.click()">
        <input
          ref="logoInput"
          type="file"
          accept="image/*"
          class="hidden"
          @change="handleFile"
        />
        <img v-if="form.logo_url" :src="form.logo_url" class="object-cover w-full h-full" />
        <div
          v-else
          class="w-full h-full flex flex-col items-center justify-center text-gray-400 text-center"
        >
          <PhotoIcon class="w-10 h-10 text-gray-400" />
          <span class="mt-1 text-sm">{{ $t('brand.select_file') }}</span>
        </div>

        <button v-if="form.logo_url"
                @click.stop="removeLogo"
                class="absolute top-1 right-1 bg-red-500 text-white w-6 h-6 rounded-full flex items-center justify-center shadow hover:bg-red-600">
          <XMarkIcon class="w-4 h-4 text-white-500" />
        </button>
      </div>
    </div>

    <!-- Submit -->
    <button
      @click="submit"
      class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-semibold"
    >
      💾 {{ $t('brand.save') }}
    </button>
  </div>
</template>

<script>
import { PhotoIcon, XMarkIcon } from '@heroicons/vue/24/solid'
import { mapGetters, mapActions } from 'vuex'

export default {
  components: { PhotoIcon, XMarkIcon },
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
      remove_logo: false,
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
      const allKeys = this.getAllCacheKeys('brand')
      const listKeys = allKeys.filter(key =>
        key.includes('page')
      )
      listKeys.forEach(key => {
        this.clearCacheKey({ module: 'brand', key })
      })
    },
    async fetch() {
      try {
        const res = await window.axios.get(`/api/warehouse/brand/detail/${this.id}`)
        this.form.name = res.data.data.name
        this.form.description = res.data.data.description
        this.form.logo_url = res.data.data.logo_url
      } catch (error) {
        console.log(error)
      }
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
      this.loading = true
      try {
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
        this.resetCacheableListByKey()
        this.$router.push('/warehouse/brand')
      } catch (error) {

      } finally {
        this.loading = false
      }
    }
  }
}
</script>
