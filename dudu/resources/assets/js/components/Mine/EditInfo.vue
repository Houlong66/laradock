<template>
  <div>
    <UserInfoItem
      :edit="edit"
      :name="name"
      :value="userDetailInfo.name"
      :is_open="userDetailInfo.isPublic.name"/>
    <UserInfoItem
      :edit="edit"
      :name="avatar"
      :value="userDetailInfo.avatar"
      :is_open="userDetailInfo.isPublic.avatar"/>
    <UserInfoItem
      :edit="edit"
      :name="sex"
      :value="userDetailInfo.sex"
      :is_open="userDetailInfo.isPublic.sex"/>
    <UserInfoItem
      :edit="edit"
      :name="mobileNumber"
      :value="userDetailInfo.mobileNumber"
      :is_open="userDetailInfo.isPublic.mobileNumber"/>
    <UserInfoItem
      :edit="edit"
      :name="email"
      :value="userDetailInfo.email"
      :is_open="userDetailInfo.isPublic.email"/>
    <UserInfoItem
      :edit="edit"
      :name="address"
      :value="userDetailInfo.address"
      :is_open="userDetailInfo.isPublic.address"/>
    <UserInfoItem
      :edit="edit"
      :name="fixedContactNumber"
      :value="userDetailInfo.fixedContactNumber"
      :is_open="userDetailInfo.isPublic.fixedContactNumber"/>
    <UserInfoItem
      :edit="edit"
      :name="qq"
      :value="userDetailInfo.qq"
      :is_open="userDetailInfo.isPublic.qq"/>
    <UserInfoItem
      :edit="edit"
      :name="weixin"
      :value="userDetailInfo.weixin"
      :is_open="userDetailInfo.isPublic.weixin"/>
    <UserInfoItem
      :edit="edit"
      :name="unitPosition"
      :value="userDetailInfo.unitPosition"
      :is_open="userDetailInfo.isPublic.unitPosition"/>
    <v-subheader center>
      所在机构
    </v-subheader>
    <v-list two-line>
      <div 
        v-for="(item,index) in userDetailInfo.orgs"
        :key="index">
        <OrgInfoItems
          :org="item.org_name"
          :dept="item.dept_name"
          :org_id="item.org_id"/>
      </div>
      <v-divider/>
      <v-divider/>
    </v-list>
    <v-btn
      block
      @click="finishEdit()"
    >完成编辑</v-btn>
  </div>
</template>


<script>
import UserInfoItem from "../Mine/Popmodal/UserInfoItem";
import OrgInfoItems from "../Mine/Popmodal/OrgInfoItems";
import { mapState, mapMutations } from "vuex";

export default {
	name: "EditInfo",
	components: {
		UserInfoItem,
		OrgInfoItems
	},
	props: {

	},
	data() {
		return {
			edit: true,
			userDetailInfo: this.$store.state.user_info,
			name: "名字",
			avatar: "头像",
			sex: "性别",
			mobileNumber: "手机号",
			email: "Email",
			address: "通讯地址",
			fixedContactNumber: "固定电话",
			qq: "QQ",
			weixin: "微信号",
			unitPosition :"单位职务",
		};
	},
	computed: {
		...mapState(["varMap"]),
	},
	mounted() {
		this.userDetailInfo = this.$store.state.user_info;
	},
	methods: {
		...mapMutations(["setUserInfo"]),
		finishEdit: function() {
			let url = "/mine";
			this.setUserInfo(this.userDetailInfo);
			this.axios.post("/api/mine/finishEdit",this.userDetailInfo).then((res)=>{
				this.$router.push({path: url});
			}).catch((err)=>{
				// console.log(err);
			});
		}
	}
};
</script>

<style scoped>

</style>