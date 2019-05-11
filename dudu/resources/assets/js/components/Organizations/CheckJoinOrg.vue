<template>
  <div>
    <v-container class="pa-0">
      <v-flex class="py-4 px-3 mb-4 top-title">
        <p
          class="subheading font-weight-bold mb-0"
          style="text-align: center">加入机构申请</p>
      </v-flex>

      <div class="px-4">
        <v-flex class="mt-4 mx-4">
          <p>{{ msg_content }}</p>
        </v-flex>

        <v-layout
          v-if="access"
          align-center>
          <v-flex v-if="agree_btn">
            <v-btn
              class="white"
              block
              @click="agree">同意
            </v-btn>
          </v-flex>
          <v-flex v-if="agree_btn">
            <v-btn
              class="white"
              block
              @click="Refused">拒绝
            </v-btn>
          </v-flex>
        </v-layout>

        <v-flex
          v-if="!agree_btn"
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
	name: "CheckJoinOrg",
	inject: ["reload"],
	data() {
		return {
			msg_id: null,
			msg_content: "",
			msg: null,
			agree_btn: true,
			access: true,
		};
	},
	computed: {
		...mapState(["selected_org", "user_info"])
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
				this.msg_content = this.$route.params.fo_content;
				this.checkInOrg();
				this.checkDelaWithMsg();
			} else {
				this.axios.get(`/api/message/msg/${this.msg_id}`).then((res) => {
					if (res.data.errcode === 0) {
						this.msg_content = res.data.data.content;
						this.msg = res.data.data;
						this.checkDelaWithMsg();
						this.checkInOrg();
					}
				}).catch((err) => {

				});
			}
			this.setMessageStatus(this.$route.params.fo_msg_id);
		},

		// 检查是否已经同意或者拒绝了用户加入机构？
		checkDelaWithMsg(){
			let msg_text  = this.msg_content;
			if(msg_text.indexOf("拒绝") !== -1 || msg_text.indexOf("同意") !== -1 ){
				this.agree_btn = !this.agree_btn;
			}
		},

		// 检查是否已经加入了当前机构
		checkInOrg() {
			if (this.$route.params.fo_params || this.msg) {
				// 不额外发送请求的方式判断申请用户是否已加入机构
				let apply_user_id = this.msg ? this.msg.send_id : this.$route.params.fo_params.user_id;
				let apply_org_id = this.msg ? JSON.parse(this.msg.params).org_id : this.$route.params.fo_params.org_id;
				let apply_org = this.user_info.smart_orgs[apply_org_id];

				// 用户是否在机构中
				if (apply_org && [1, 2].indexOf(apply_org.pivot.role_id) !== -1) {
					// 判断当前用户是否为超管或系统管理员
					apply_org.users.forEach((v, i) => {
						// 判断用户是否在群组中
						if (v.id === apply_user_id) {
							this.agree_btn = false;
							return;
						}
					});
				} else {
					this.access = false;
					this.$toast("您非机构超管或系统管理员员无权操作", "warning");
				}
			}
		},

		// 同意加入机构
		agree() {

			this.axios.post(`/api/my/approval/message/${this.msg_id}/grant/joinOrg`).then((res) => {

				if (res.data.errcode == 0) {
					this.initUser().then(() => {
						this.$toast("已同意！", "success");
						this.$router.push("/messages");
						this.reload();
					});

				} else {
					this.$toast(res.data.errmsg, "warning");
				}
			}).catch((Err) => {

			});
		},

		// 拒绝用户申请加入机构
		Refused(){
			let data = {
				msg_id : this.msg_id,
				org_name : this.selected_org.name
			};
			this.axios.post("/api/org/refused",data).then((res) => {
				if(res.data.errcode === 0){
					this.$toast("已拒绝！", "success");
					this.$router.push("/messages");
					this.reload();
					return ;
				}
				this.$toast(res.data.errmsg, "warning");
			}).catch((err) => {

			});

		}
	}
};
</script>

<style scoped>

</style>