<template>
    <div class="p-4 bg-white rounded-xl shadow-md">
      <Form mode="create" :type="'single'" />
    </div>
</template>

<script>
import Form from './Form.vue'

export default {
  components: { Form },
}
</script>
