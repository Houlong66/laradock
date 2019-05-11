<template>

  <!--  群组成员查看文件  -->
  <div>
    <div
      v-if="!isLoading ">
      <header-text
        :header_text="header_text"
        entry_type="group_user_list"/>

      <v-card
        flat
        class="u-list pb-0"
      >
        <user-item
          :user_list="group_users"
          list_type="group"
          @showRole="showRole"/>
      </v-card>
    </div>

    <v-bottom-sheet
      v-model="role_dialog"
      :persistent="true"
    >
      <v-card>
        <v-card-text>
          <v-layout
            column
            align-center>
            <v-select
              :items="role_items"
              v-model="change_roles"
              style="width: 100%"
              placeholder="请选择角色"/>

            <v-flex>
              <v-btn
                flat
                color="primary"
                @click="role_dialog=false">取消
              </v-btn>
              <v-btn
                :disabled="!change_roles"
                flat
                color="primary"
                @click="confirmRole()">确定
              </v-btn>
            </v-flex>
          </v-layout>
        </v-card-text>
      </v-card>
    </v-bottom-sheet>

    <component
      v-if="isLoading"
      :is="cView"
    />

  </div>
</template>


<script>
import Loading from "../Commons/Loading";
import UserItem from "../Organizations/Popmodal/UserItem";
import HeaderText from "../Organizations/Popmodal/HeaderText";
import {mapState} from "vuex";

export default {
	name: "GroupUserList",
	inject: ["reload"],
	components: {
		Loading,
		UserItem,
		HeaderText,
	},
	data: function () {
		return {
			isLoading: true,
			cView: "Loading",
			header_text: "",
			group_users: null,
			role_dialog: false,
			role_items: [],
			change_roles: null,
			target: null,
			group_type: "",
		};
	},
	computed: {
		...mapState(["user_info"])
	},
	mounted() {
		// 初始化数据
		this.initData();
	},

	methods: {
		// 获取群组用户数据
		initData() {
			let p1 = this.axios.get(`/api/user/group/${this.$route.query.group_id}`).then((res) => {
				this.group_users = res.data.data.users;
				this.header_text = res.data.data.name;
				let role_type = res.data.data.type === 1 ? 3 : 2;
				this.getRoles(role_type);
			});

			Promise.all([p1]).then(() => {
				this.isLoading = false;
			});
		},
		// 获取角色
		getRoles(roles_type) {
			this.axios.get("/api/role/get?type=" + roles_type).then((res) => {
				this.role_items = res.data.data;
				if (this.role_items[0]["name"] === "群组创建人") this.role_items.shift();

				// 构造角色选项列表
				this.role_items.forEach((value, index) => {
					let desp = "";

					if (value.type === 3) {
						switch (value.name) {
						case "日程共享人":
							desp = "共享人（可发起共享日程）";
							break;
						case "日程跟踪人":
							desp = "跟踪人（负责跟踪发起日程共享）";
							break;
						default:
						}
					} else {
						switch (value.name) {
						case "任务签收人":
							desp = "签收人（仅负责签收和上报任务）";
							break;
						case "部门/单位领导":
							desp = "督办人（签收人单位负责督办的人）";
							break;
						case "任务发放人":
							desp = "发放人（可向其他组员发放任务）";
							break;
						default:
						}
					}

					value.text = desp;
					value.value = value.id;
				});
			});
		},

		//接收子组件传值
		showRole: function (...data) {
			this.role_dialog = true;
			this.target = data[0];
			this.change_roles = this.target.pivot.role_id;
		},

		confirmRole: function () {
			let temp_data = {
				targets: this.target.id,
				roles: this.change_roles,
				role_type: "2",
				item: this.$route.query.group_id
			};
			this.axios.post("/api/role/batch_change", temp_data).then((res) => {
				if (res.data.errcode === 0) {
					this.reload();
					this.$toast("修改成功", "success");
				} else {
					this.$toast(res.data.errmsg, "warning");
				}
			});
		}
	},
};
</script>

<style scoped>
  .u-list {
    padding-bottom: 40px;
  }
</style>