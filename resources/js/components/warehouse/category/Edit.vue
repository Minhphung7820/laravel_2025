<template>
  <CategoryForm mode="edit" :id="$route.params.id" />
</template>

<script>
import CategoryForm from './Form.vue'

export default {
  components: { CategoryForm }
}
</script>
