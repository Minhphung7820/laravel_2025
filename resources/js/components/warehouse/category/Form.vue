<template>
  <div>
    <h2 class="text-xl font-semibold mb-4">📂 Thêm / Sửa Danh Mục</h2>

    <div class="mb-4">
      <label class="block font-medium mb-1">📁 Danh mục</label>
      <input
        v-model="form.title"
        placeholder="Tên danh mục"
        class="w-full border px-4 py-2 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
      />
    </div>

    <div v-for="(variant, vIndex) in form.variants" :key="vIndex" class="border p-4 rounded mb-4 bg-gray-50">
      <div class="flex justify-between items-center mb-2">
        <h3 class="font-semibold">🔸 Biến thể {{ vIndex + 1 }}</h3>
        <button @click="removeVariant(vIndex)" class="text-red-500 hover:underline text-sm">
          Xoá biến thể
        </button>
      </div>

      <input
        v-model="variant.title"
        placeholder="Tên biến thể"
        class="w-full border px-4 py-2 rounded shadow-sm mb-3"
      />

      <div v-for="(attr, aIndex) in variant.attributes" :key="aIndex" class="flex items-center gap-2 mb-2">
        <input
          v-model="attr.title"
          placeholder="Tên thuộc tính"
          class="flex-1 border px-3 py-2 rounded"
        />
        <button @click="removeAttribute(vIndex, aIndex)" class="text-red-400 hover:text-red-600 text-sm">❌</button>
      </div>

      <button @click="addAttribute(vIndex)" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm">
        + Thêm thuộc tính
      </button>
    </div>

    <div class="mb-4">
      <button @click="addVariant" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
        + Thêm biến thể
      </button>
    </div>

    <button @click="submit" class="bg-blue-700 text-white px-6 py-2 rounded hover:bg-blue-800 font-semibold">
      💾 Lưu
    </button>
  </div>
</template>

<script>
export default {
  props: {
    mode: {
      type: String,
      default: 'create',
    },
    id: {
      type: [String, Number],
      default: null,
    },
  },
  data() {
    return {
      form: {
        title: '',
        variants: [],
      },
    };
  },
  created() {
    if (this.mode === 'edit' && this.id) {
      this.getDetail()
    }
  },
  methods: {
    async getDetail() {
      try {
        const res = await window.axios.get(`/api/warehouse/category/detail/${this.id}`)
        this.form = res.data
      } catch (e) {
        console.error('Lỗi khi load dữ liệu:', e)
      }
    },
    async handleSubmit() {
      try {
        const url = this.mode === 'edit'
          ? `/api/warehouse/category/update/${this.id}`
          : `/api/warehouse/category/create`
        await window.axios.post(url, this.form)
        this.$router.push('/warehouse/category')
      } catch (e) {
        console.error('Lỗi khi lưu:', e)
      }
    },
    submit() {
      this.handleSubmit()
    },
    addVariant() {
      this.form.variants.push({ title: '', attributes: [] });
    },
    removeVariant(index) {
      this.form.variants.splice(index, 1);
    },
    addAttribute(vIndex) {
      this.form.variants[vIndex].attributes.push({ title: '' });
    },
    removeAttribute(vIndex, aIndex) {
      this.form.variants[vIndex].attributes.splice(aIndex, 1);
    },
  },
};
</script>
