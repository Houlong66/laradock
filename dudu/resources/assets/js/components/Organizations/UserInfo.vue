<template>
  <div style="padding-bottom:66px;">
    <div 
      v-if="!isLoading" 
      column>
      <HeaderInfo 
        :user-detail-info="userDetailInfo" 
        :is_self="is_self"/>
      <OrgInfo
        :user-detail-info="userDetailInfo"
        :is_self="is_self"/>
      <UserInfoList
        :user-detail-info="userDetailInfo"
        :edit="isEdit"
        :is_self="is_self"/>
    </div>

    <component 
      v-if="isLoading"
      :is="cView"
    />
  </div>
</template>

<script>
import Loading from "../Commons/Loading";
import UserInfoList from "../Mine/Popmodal/UserInfoList";
import OrgInfo from "../Mine/Popmodal/OrgInfo";
import HeaderInfo from "../Mine/Popmodal/HeaderInfo";


export default {
	name: "Mine",
	components: {
		Loading,
		UserInfoList,
		OrgInfo,
		HeaderInfo
	},
	data() {
		return {
			isLoading: true,
			cView: "Loading",
			userDetailInfo: {},
			isEdit: false,
			is_self: 0 // 判断是否为本人
		};
	},
	mounted() {
		this.getUserInfo();
	},
	methods: {
		getUserInfo() {
			this.axios.get(`/api/user/${this.$route.query.id}`).then((res) => {
				this.userDetailInfo = res.data.data;
				this.isLoading = false;
			});				
		}
	}
};
</script>

<style scoped>

</style>