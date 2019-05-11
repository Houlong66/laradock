<template>
  <div>
    <v-container class="pa-0">
      <v-layout
        column
        class="pa-0">
        <v-flex class="py-4 px-3 mb-2 top-title">
          <p
            class="subheading font-weight-bold mb-0"
            style="text-align: center">邀请您加入群组</p>
        </v-flex>
        <!--<v-flex class="mt-3">-->
        <!--<h2>邀请加入群组消息</h2>-->
        <!--</v-flex>-->

        <v-flex class="mt-4 mx-4">
          <p>{{ content }}</p>
          <p class="grey--text">选择群组角色，点击同意即可加入</p>
        </v-flex>

        <!--<div-->
        <!--v-for="(item,index) of introduce"-->
        <!--v-show="introduce != []"-->
        <!--:key="index"-->
        <!--class="introduce">-->
        <!--{{ item }}-->
        <!--</div>-->

        <!--selects 选择列-->
        <v-flex
          v-if="agreed_btn"
          class="mb-2 px-4"
        >
          <!--<v-flex xs12>-->
          <!--<v-subheader> 请选择你在群组中的角色(默认为{{ defaults }})</v-subheader>-->
          <!--</v-flex>-->
          <v-select
            :items="role_list"
            v-model="change_roles"
            class="mb-1"
            hide-details
            single-line
            label="请选择群组角色"/>
          <GroupRoleIntroduction
            :dialog_type="role_type"
          />
        </v-flex>


        <v-flex 
          class="px-4" 
          style="width: 100%">
          <v-btn
            v-if="agreed_btn"
            block
            class="white mt-3"
            @click="agree()">同意
          </v-btn>

          <v-btn
            v-else
            block
            disabled
            class="white mt-3"
          >已同意
          </v-btn>
        </v-flex>
      </v-layout>
    </v-container>
  </div>
</template>

<script>
import {mapState, mapActions} from "vuex";
import GroupRoleIntroduction from "./Popmodal/GroupRoleIntroduction";

export default {
	name: "CheckInviteGroup",
	inject: ["reload"],
	components: {
		GroupRoleIntroduction,
	},
	data() {
		return {
			msg_id: null,
			content: null,
			role_list: [],
			change_roles: "",
			defaults: "",
			introduce: [],
			agreed_btn: true,
			w_role_info_dialog: false,
			s_role_info_dialog: false,
			role_type: 2, // 0工作群, 1日程群
			group_id: null,
		};
	},
	computed: {
		...mapState(["user_info", "selected_org"]),
	},
	mounted() {
		this.initData();
	},
	methods: {
		...mapActions(["initUser"]),
		// 初始化
		initData() {
			// 获取消息id
			this.msg_id = this.$route.params.fo_msg_id !== undefined ? this.$route.params.fo_msg_id : this.$route.query.fo_msg_id;
			// 获取消息内容
			if (this.$route.params.fo_content !== undefined) {
				this.content = this.$route.params.fo_content;
				this.checkUserInGroups();
			} else {
				this.axios.get(`/api/message/msg/${this.msg_id}`).then((res) => {
					if (res.data.errcode === 0) {
						this.content = res.data.data.content;
						this.msg = res.data.data;
						this.checkUserInGroups();
					}
				}).catch((err) => {

				});
			}
			// 设置消息为已读
			this.setMessageStatus(this.$route.params.fo_msg_id);
		},
		// 检查用户是否存在群组中
		checkUserInGroups() {
			if (this.$route.params.fo_params || this.msg) {
				this.group_id = this.msg ? JSON.parse(this.msg.params).group_id : parseInt(this.$route.params.fo_params.group_id);
				let apply_group = this.user_info.smart_groups[this.group_id];
				// 判断当前用户在群中
				if (apply_group) {
					this.agreed_btn = false;
				}else{
					// 获取群组权限
					this.getGroupRoles();
				}
			}
		},
		// 获取角色列表
		getGroupRoles() {
			this.axios.get(`/api/group/info/${this.group_id}`).then((res) => {
				let group = res.data.data;
				group.type == 1 ? this.role_type = 3 : this.role_type = 2;
				this.axios.get("/api/role/get?type=" + this.role_type).then((res) => {
					this.role_list = res.data.data;

					if (this.role_list[0]["name"] == "群组创建人") this.role_list.shift();

					// 构造角色选项列表
					this.role_list.forEach((value, index) => {

						if (value.type == 3) {
							this.defaults = "日程共享人";
							// this.change_roles = this.role_list[0].id;
							this.change_roles = 9;
							// 角色描述文案
							let desp = "";
							switch (value.name) {
							case "日程共享人":
								desp = "日程共享人";
								break;
							case "日程查看人":
								desp = "日程查看人";
								break;
							default:
							}
							value.text = desp;
							value.value = value.id;
							return;
						}
						this.defaults = "签收人";
						// this.change_roles = this.role_list[0].id;
						this.change_roles = 7;
						// 角色描述文案
						let desp = "";
						switch (value.name) {
						case "任务签收人":
							desp = "任务签收人";
							break;
						case "部门/单位领导":
							desp = "部门/单位领导";
							break;
						case "任务发放人":
							desp = "任务发放人";
							break;
						default:
						}
						value.text = desp;
						value.value = value.id;
					});
				});
			});
		},
		// 同意加入
		agree() {
			let temp_data = {
				user_id: this.msg ? JSON.parse(this.msg.params).user_id : this.$route.params.fo_params.user_id,
				group_id: this.group_id,
			};
			this.axios.post("/api/my/approval/grant/join_group?org_id=" + this.change_roles, temp_data).then((res) => {
				if (res.data.errcode === 0) {
					this.$toast("已同意！", "success");
					// 更新 vuex
					let vue = this;
					this.initUser().then(() => {
						vue.$router.push({path: "works/0"});
						this.reload();
					});

				} else {
					this.$toast(res.data.errmsg, "warning");
				}
			}).catch((Err) => {

			});
		},


	}
}
;
</script>

<style scoped>
  .introduce {
    width: 93%;
    box-shadow: -2px 4px 8px 0px #b3a0a0;
    padding: 9px;
    margin-bottom: 7px;
    font-size: .9rem;
  }
</style>