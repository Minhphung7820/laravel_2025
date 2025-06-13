<template>
  <CategoryForm mode="edit" :id="Number($route.params.id)" />
</template>

<script>
import CategoryForm from './Form.vue'

export default {
  components: { CategoryForm }
}
</script>
