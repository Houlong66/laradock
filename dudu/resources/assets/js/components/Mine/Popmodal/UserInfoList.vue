<template>
  <div class="mt-2">
    <v-subheader 
      center 
      style="background:#f5f5f5;">
      个人信息
    </v-subheader>

    <UserInfoItem
      :edit="edit"
      :is_self="is_self"
      :name="name"
      :value="userDetailInfo.name?userDetailInfo.name:'未填'"/>
    <UserInfoItem
      :edit="edit"
      :is_self="is_self"
      :name="identity"
      :value="userDetailInfo.identity?userDetailInfo.identity:'未填'"/>
    <UserInfoItem
      :edit="edit"
      :is_self="is_self"
      :name="mobileNumber"
      :value="userDetailInfo.tel?userDetailInfo.tel:'未填'"/>
    <UserInfoItem
      :edit="edit"
      :is_self="is_self"
      :name="email"
      :value="userDetailInfo.email?userDetailInfo.email:'未填'"/>
    <UserInfoItem
      :edit="edit"
      :is_self="is_self"
      :name="address"
      :value="userDetailInfo.address?userDetailInfo.address:'未填'"/>
    <UserInfoItem
      :edit="edit"
      :is_self="is_self"
      :name="fixedContactNumber"
      :value="userDetailInfo.fixed_tel?userDetailInfo.fixed_tel:'未填'"/>
    <UserInfoItem
      :edit="edit"
      :is_self="is_self"
      :name="qq"
      :value="userDetailInfo.qq?userDetailInfo.qq:'未填'"/>
    <UserInfoItem
      :edit="edit"
      :is_self="is_self"
      :name="weixin"
      :value="userDetailInfo.wechat?userDetailInfo.wechat:'未填'"/>
  </div>
</template>


<script>
import UserInfoItem from "../Popmodal/UserInfoItem";
export default {
	name: "UserInfoList",
	components: {
		UserInfoItem
	},
	props: {
		userDetailInfo: {
			type: Object,
			default: ()=>{}
		},
		edit: {
			type: Boolean,
			default: ()=>false
		},
		is_self: {
			type: Number,
			default: ()=>null
		}
	},
	data() {
		return {
			mobileNumber: "手机号",
			email: "Email",
			address: "通讯地址",
			fixedContactNumber: "固定电话",
			qq: "QQ",
			weixin: "微信号",
			identity: "单位职务",
			name: "姓名"
		};
	},
	methods: {
	}
};
</script>

<style scoped>

</style>