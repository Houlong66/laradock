<template>
  <div>
    <v-container>
      <v-layout 
        row 
        wrap>
        <v-flex xs1>
          <v-avatar>
            <img 
              :src="userDetailInfo.avatar" 
              alt="avatar">
          </v-avatar>
        </v-flex>
        <v-flex xs11>
          <v-container>
            <span>{{ userDetailInfo.name }}</span>
            <div
              v-for="(item,index) in userDetailInfo.orgs"
              :key="index">
              <v-list-tile 
                :key="index">
                <v-list-tile-content>
                  <v-list-tile-title>
                    {{ item.org_name }}>{{ item.dept_name }} {{ item.identify }}
                  </v-list-tile-title>
                </v-list-tile-content>
              </v-list-tile>
            </div>
          </v-container>
        </v-flex>
      </v-layout>
    </v-container>
    <v-layout class="center">
      <v-flex xs12>
        <img
          :src="userDetailInfo.weixinCard"
          aspect-ratio="1">
      </v-flex>
      <v-flex xs12>
        <span>微信扫一扫，交换名片</span>
      </v-flex>
    </v-layout>
    <v-btn
      block>
      分享名片
    </v-btn>
  </div>
</template>

<script>
export default {
	name: "UserCard",
	components: {
	
	},
	props: {

	
	},
	data() {
		return {
			userDetailInfo: {}
		};
	},
	mounted() {
		this.userDetailInfo = this.$store.state.user_info;
	},
	methods: {

	}
};
</script>

<style scoped>
.center {
  text-align: center;
}
</style>