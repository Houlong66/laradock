<template>
  <div class="admin-bottom">
    <v-footer
      dark
      height="auto"
    >
      <v-card
        class="flex"
        flat
        tile
      >
        <v-card-actions class="grey darken-3 justify-center">
          &copy;2019 — <strong>广州汇考教育科技有限公司</strong>
        </v-card-actions>
      </v-card>
    </v-footer>
  </div>
</template>

<script>
export default {
	name: "AdminBottimNva",
	data (){
		return{
			icons: [
				"fab fa-facebook",
				"fab fa-twitter",
				"fab fa-google-plus",
				"fab fa-linkedin",
				"fab fa-instagram"
			]
		};
	}
};
</script>

<style scoped>
  .admin-bottom{
    position: fixed;
    width: 100%;
    bottom: 0;
  }

</style>
