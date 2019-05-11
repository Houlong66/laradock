<template>
  <div style="padding-bottom:66px;">
    <div
      v-if="!isLoading"
      column>

      <HeaderInfo
        :user-detail-info="user_info"
        :is_self="is_self"/>

      <OrgInfo
        :user-detail-info="user_info"
        :is_self="is_self"/>

      <UserInfoList
        :user-detail-info="user_info"
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
import Loading from "../components/Commons/Loading";
import UserInfoList from "../components/Mine/Popmodal/UserInfoList";
import OrgInfo from "../components/Mine/Popmodal/OrgInfo";
import HeaderInfo from "../components/Mine/Popmodal/HeaderInfo";
import { mapState } from "vuex";


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
			isEdit: false,
			is_self: 1, // 判断是否为本人
			contactItems: {}
		};
	},
	computed: {
		...mapState(["user_info"])
	},
	mounted() {
		// this.getUserInfo();
		this.contactItems["outer"] = {
			name: "外部联系人",
			number: "开发中"
		};
		this.contactItems["star"] = {
			name: "星标联系人",
			number: "开发中"
		};
		this.isLoading = false;
	},
	methods: {
		getItemClass(item) {
			let itemClass = "iconfont dudu-bumen icon-blue";
			switch(item){
			case "我的工作群组":
				itemClass = "iconfont dudu-qunzu icon-blue";
				break;
			case "外部联系人":
				itemClass = "iconfont dudu-lianxiren icon-orange";
				break;
			case "星标联系人":
				itemClass = "iconfont dudu-xingbiao icon-yellow";
				break;
			}
			return itemClass;
		},
		contact: function(val) {
			alert("开发中，敬请期待");
		}
	}
};
</script>

<style scoped>

</style>
