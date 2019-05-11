<template>
  <div>
    <div v-if="!isLoading">
      <v-flex class="mt-3">
        <v-layout 
          row 
          justify-center>
          <h3>邀请同事加入部门</h3>
        </v-layout>
      </v-flex>
      <v-flex class="mt-3">
        <v-select
          v-model="dept"
          :items="dept_items"
          box
          background-color="white"
          label="选择部门"
        />
      </v-flex>
      <v-flex class="mt-3">
        <v-select
          v-model="role"
          :items="role_items"
          box
          background-color="white"
          label="选择角色"
        />
      </v-flex>
      <v-flex class="mt-3">
        <v-btn 
          block 
          large
          color="primary"
          @click="inviteWechat()">邀请微信好友</v-btn>
      </v-flex>
    </div>
    <component 
      v-if="isLoading"
      :is="cView"
    />
  </div>
</template>

<script>
import { mapState } from "vuex";
import Loading from "../Commons/Loading";

export default {
	name: "InviteJoinGroup",
	components: {
		Loading
	},
	data () {
		return {
			user_id: null,
			dept: null, // 部门
			dept_id: null,
			dept_items: [], // 部门选项
			isLoading: true,
			cView: "Loading",
			role: 4, // 角色
			role_items: [
				{
					text: "系统管理员",
					value: 2
				},
				{
					text: "任务管理员",
					value: 3
				},
				{
					text: "内部成员",
					value: 4
				}
			]
		};
	},
	computed: {
		...mapState(["varMap"])
	},
	mounted () {
		this.dept_id = this.$route.query.dept_id;
		this.user_id = this.$store.state.selected_org.pivot.user_id;
		this.getDepts();
		this.wxShareConfig();
	},
	methods: {
		getDepts () {
			this.axios.get(`/api/dept/org/${this.$store.state.selected_org.id}`).then((res) => {
				this.dept_items = res.data.data;
				this.dept_items.forEach((value, index) => {
					value.text = value.name;
					value.value = value.id;
				});
				if (this.dept_id) {
					this.dept = parseInt(this.dept_id);
				}
				this.isLoading = false;
			});
		},
		inviteWechat () {
			alert("分享此页面给您的微信好友");
		},
		wxShareConfig() {
			/*global wx*/
			let url_share = "";
			url_share = `/check_invite_dept?dept_id=${this.dept_id}&inviter_id=${this.user_id}&role=${this.role}`;
			// const url = window.location.href;
			// this.pureUrl(url);

			// this.axios.get("/api/wx/js_sdk_config").then((res) => {
			// /*global wx*/
			// wx.config(res.data);
			// wx.ready(function(){
			// 	wx.checkJsApi({
			// 		jsApiList: [
			// 			"onMenuShareTimeline",
			// 			"onMenuShareAppMessage"
			// 		],
			// 		success: function(res) {
			// 		}
			// 	});
			// alert(`${host}${url_share}`);
			wx.onMenuShareAppMessage({
				title: "邀请您加入都督", // 分享标题
				desc: "微信数字化办公平台", // 分享描述
				// link: "http://frp.houlong66.cn/#/check_invite_dept",
				/*global host*/
				link: `${host}${url_share}`, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
				imgUrl: "", // 分享图标
				type: "", // 分享类型,music、video或link，不填默认为link
				dataUrl: "", // 如果type是music或video，则要提供数据链接，默认为空
				success: function () {
					// 用户点击了分享后执行的回调函数
					this.$toast("已成功发送给微信好友","success");
				}
			});
			wx.onMenuShareTimeline({
				title: "邀请您加入都督", // 分享标题
				link: `${host}${url_share}`, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
				imgUrl: "", // 分享图标
				success: function () {
					// 用户点击了分享后执行的回调函数
					this.$toast("已成功发送给微信好友","success");
				}
			});
			// });
			// }).catch((err) => {

			// });

		}
	}
};
</script>