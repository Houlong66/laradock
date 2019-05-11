<template>
  <div class="mt-5 pt-5">
    <v-progress-circular
      :size="50"
      :width="5"
      class="loading-layout"
      color="red"
      indeterminate
    />
    <p 
      class="pt-3 grey--text text--darken-1" 
      style="text-align:center;">Loading</p>
  </div>
</template>

<script>
export default {
	name: "Loading"
};
</script>

<style scoped>
  .loading-layout{
    display:block;
    margin:0 auto;
  }
</style>