<template>
  <div>
    <v-radio 
      label="微信用户" 
      value="value1"/>
    <v-radio 
      label="系统用户" 
      value="value2"/>
    <span>请将此页面发送给微信用户</span>
    <v-btn
      block>微信转发他人审批</v-btn>
  </div>
</template>


<script>
export default {
	name: "OrgMigrate",
	components: {

	},
	data() {
		return {
			value1: 1,
			value2: 2
		};
	}
};
</script>

<style scoped>

</style>