<template>
  <div>
    <v-container class="pa-0">
      <v-flex class="py-4 px-3 mb-4 top-title">
        <p
          class="subheading font-weight-bold mb-0"
          style="text-align: center">加入群组申请</p>
      </v-flex>

      <v-flex class="mt-4 mx-4">
        <p class="content">{{ msg_content }}</p>
      </v-flex>

      <div class="px-4">
        <v-layout
          v-if="access"
          align-center>
          <v-flex v-if="agreed_btn">
            <v-btn
              class="white"
              block
              @click="Agreed">同意
            </v-btn>
          </v-flex>
          <v-flex v-if="agreed_btn">
            <v-btn
              class="white"
              block
              @click="Refused">拒绝
            </v-btn>
          </v-flex>
        </v-layout>

        <v-flex
          v-if="!agreed_btn"
        >
          <v-btn
            disabled
            block>已处理
          </v-btn>
        </v-flex>
      </div>
    </v-container>
  </div>
</template>

<script>
import {mapState, mapActions} from "vuex";

export default {
	name: "CheckJoinGroup",
	inject: ["reload"],
	data() {
		return {
			msg_id: null,
			msg_title: null,
			msg_content: null,
			msg: null,
			agreed_btn: true,
			access: true,
		};
	},
	computed: {
		...mapState(["user_info", "selected_org"])
	},
	mounted() {
		this.initData();
	},
	methods: {
		...mapActions(["initUser"]),
		initData() {
			// 获取消息id
			this.msg_id = this.$route.params.fo_msg_id !== undefined ? this.$route.params.fo_msg_id : this.$route.query.fo_msg_id;
			// 获取消息内容
			if (this.$route.params.fo_content !== undefined) {
				this.msg_title = this.$route.params.fo_title;
				this.msg_content = this.$route.params.fo_content;
				this.checkUserInGroups();
			} else {
				this.axios.get(`/api/message/msg/${this.msg_id}`).then((res) => {
					if (res.data.errcode === 0) {
						this.msg_title = res.data.data.title;
						this.msg_content = res.data.data.content;
						this.msg = res.data.data;
						this.checkUserInGroups();
					}
				}).catch((err) => {

				});
			}
			this.setMessageStatus(this.$route.params.fo_msg_id);
		},
		// 检查用户是否存在群组中
		checkUserInGroups() {
			if (this.msg_content.split("已拒绝").length > 1) {
				this.agreed_btn = false;
			} else if (this.$route.params.fo_params || this.msg) {
				let group_id = this.msg ? JSON.parse(this.msg.params).group_id : parseInt(this.$route.params.fo_params.group_id);
				let send_id = this.msg ? this.msg.send_id : this.$route.params.fo_params.user_id;
				// 检查用户是否已经存在了群组中
				let apply_group = this.user_info.smart_groups[group_id];
				// 判断当前用户在群中，且为群主
				if (apply_group && apply_group.pivot.role_id === 5) {
					apply_group.users.forEach((item, index) => {
						// 判断申请人是否已在群中
						if (item.id === send_id) {
							this.agreed_btn = false;
							return;
						}
					});
				} else {
					this.access = false;
					this.$toast("您无权操作", "warning");
				}
			}
		},

		// 拒绝申請加入群组
		Refused() {
			let data = this.msg ? JSON.parse(this.msg.params) : this.$route.params.fo_params;
			data.msg_id = this.msg_id;
			let postData = data;
			this.axios.post("/api/group/refusedjoin", postData).then((res) => {
				if (res.data.errcode === 1) {
					this.$toast(res.data.errmsg, "error");
					this.$router.push({path: "/messages"});
					return;
				}

				this.$toast("已拒绝用户加入群组申请", "success");
				this.$router.push({path: "/messages"});
				this.reload();
			}).catch((err) => {
			});
		},
		// 同意申請加入群组
		Agreed() {
			let data = this.msg ? JSON.parse(this.msg.params) : this.$route.params.fo_params;
			data.msg_id = this.msg_id;
			let postData = data;
			this.axios.post("/api/group/agreedjoin", postData).then((res) => {

				if (res.data.errcode === 1) {
					this.$toast(res.data.errmsg, "error");
					return false;
				}

				this.initUser().then(() => {
					this.$toast("已同意用户加入", "success");
					this.$router.push({path: "/messages"});
					this.reload();
				});
			}).catch((err) => {
			});
		},
	}
};
</script>

<style scoped>

  .content {
    font-size: 1.1rem;
    text-align: center;
  }

  .joingrourp-title {
    padding: 10px;
    text-align: center;
  }
</style>