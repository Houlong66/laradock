<template>
  <div
    class="admintop"
  >
    <v-card-title class="headline admintoptitle font-weight-regular blue-grey white--text">都督后台管理</v-card-title>

    <v-btn
      class="red"
      color="info"
      @click="hadelRoute('/admin')">查看已有文章</v-btn>
    <v-btn
      color="info"
      @click="hadelRoute('/addlist')"
    >添加文章</v-btn>
  </div>
</template>

<script>
export default {
	name: "AdminTopNva",
	data(){
		return{

		};
	},
	methods:{
		hadelRoute(type){
			this.$router.push({path:type});
		}
	}
};
</script>
<style scoped>
  .admintop  .red .theme--light.v-btn:not(.v-btn--icon):not(.v-btn--flat){
    background-color: red !important;
  }
  .admintoptitle{
    font-size: 12px;

    margin-bottom: 14px;
  }

</style>
