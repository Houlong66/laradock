<template>
  <div style="background:#fff;">
    <v-layout class="mx-3 pt-3 pb-3">
      <v-flex xs2>
        <!--头像-->
        <v-avatar>

          <img
            v-if="userDetailInfo.avatar"
            :src="userDetailInfo.avatar"
            alt="avatar">

          <img
            v-else
            src="../../../../img/user_info.png"
            alt="avatar">

        </v-avatar>
      </v-flex>

      <!--性别-->
      <v-flex 
        xs4
        class="align-self-center">
        <v-icon
          :class="getUserSexClass(userDetailInfo.sex)" 
          small/>
        <span>{{ userDetailInfo.name }}</span>
      </v-flex>

      <!--<v-flex -->
      <!--v-if="is_self==1"-->
      <!--xs6-->
      <!--class="align-self-center">-->
      <!--<div style="text-align:right;">-->
      <!--<v-icon-->
      <!--class="iconfont dudu-jiaruqun2 ml-2 mr-1"-->
      <!--color="grey"-->
      <!--@click="dev()"-->
      <!--/>-->
      <!--</div>-->
      <!--</v-flex>-->
      <!--<v-flex -->
      <!--v-if="is_self==0"-->
      <!--xs6-->
      <!--class="align-self-center">-->
      <!--<div style="text-align:right;">-->
      <!--<v-icon -->
      <!--class="iconfont dudu-shenqing ml-2 mr-1"-->
      <!--color="grey"-->
      <!--/>-->
      <!--<v-icon -->
      <!--class="iconfont dudu-xingbiao ml-2 mr-1"-->
      <!--color="grey"-->
      <!--/>-->
      <!--<v-icon -->
      <!--class="iconfont dudu-yaoqinghaoyou ml-2 mr-1"-->
      <!--color="grey"-->
      <!--/>-->
      <!--</div>-->
      <!--</v-flex>-->

    </v-layout>

  </div>
</template>


<script>
export default {
	name: "HeaderInfo",
	components: {
	},
	props: {
		userDetailInfo: {
			type: Object,
			default: ()=>{}
		},
		is_self: {
			type: Number,
			default: ()=>null
		}
	},
	data() {
		return {
			user_img:null
		};
	},
	mounted() {
	},
	methods: {

		dev () {
			alert("开发中，敬请期待");
		},
		edit: function() {
			let url = "/mine/editInfo";
			this.$router.push({path: url});
		},
		setUserInfo: function() {
			let url = "/mine/setInfo";
			this.$router.push({path: url});
		},
		register: function() {
			let url = "/apply_join_org";
			this.$router.push({path: url});
		},
		getUserCard: function() {
			let url = "/mine/userCard";
			this.$router.push({path: url});
		},
		getUserSexClass(sex) {
			if(sex == 1){
				return "iconfont dudu-nan icon-blue";
			}else if(sex == 2){
				return "iconfont dudu-nv icon-pink";
			}
		}
	},
};
</script>

<style scoped>
</style>