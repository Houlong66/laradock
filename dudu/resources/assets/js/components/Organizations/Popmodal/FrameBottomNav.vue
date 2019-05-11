<template>
  <div>
    <v-bottom-nav
      :active.sync="bottomNav"
      :value="true"
      color="red"
      shift
      fixed
    >
      <v-btn 
        dark 
        @click="RouterTo(0)">
        <span>添加部门单位</span>
      </v-btn>

      <v-btn 
        dark 
        @click="RouterTo(1)">
        <span>批量操作成员</span>
      </v-btn>

    </v-bottom-nav>
  </div>
</template>


<script>
export default {
	name: "FrameBottomNav",
	components: {

	},
	props: {

	},
	data: function() {
		return {
			RouterTo: function(val) {
				// let url = "/organizations/BatchOperation";
				if (val == 0) {
					// 
				}
			}
		};
	},
	methods: {

	}
};
</script>

<style scoped>

</style>