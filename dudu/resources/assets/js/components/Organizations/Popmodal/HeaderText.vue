<template>
  <div>
    <v-subheader
      center
      style="background:#f5f5f5;">
      <!--标题文案-->
      <span
        class="text-truncate"
      >
        {{ header_text }}
      </span>
      <v-spacer/>
      <!--群组相关功能按钮组-->
      <v-btn-toggle
        v-if="group_btn_show"
        class="btn_font">

        <!--创建群组-->
        <v-btn
          v-if="entry_type === 'group_list' && create_group_control.permission"
          ref="mask"
          flat
          color="red"
          @click="createGroupPop">
          <v-icon
            small
            class="iconfont dudu-chuangjianv pr-1"
          />
          创建
        </v-btn>

        <!-- 主动申请加入群组 -->
        <v-btn
          v-if="entry_type === 'group_list' && selected_org.groups.length !== 0"
          ref="masks"
          flat
          color="red"
          @click="applyJoinGroupPop">
          <v-icon
            small
            class="iconfont dudu-jiarubanji pr-1"
          />
          加入
        </v-btn>


        <!--邀请用户加入群组-->
        <v-btn
          v-if="entry_type === 'group_user_list'"
          flat
          color="red"
          @click="inviteJoinGroup()">
          <v-icon
            small
            class="iconfont dudu-yaoqing pr-1"/>
          邀请
        </v-btn>

        <!-- 退出群组按钮 -->
        <v-btn
          v-if="entry_type === 'group_user_list' && role_id !== 5"
          flat
          color="red"
          @click="dialog_exit_group = !dialog_exit_group"
        >
          <v-icon
            small
            class="iconfont dudu-outgroup pr-1"/>
          退出
        </v-btn>
        <!-- 转让群主按钮 -->
        <!--todo-->
      </v-btn-toggle>


    </v-subheader>

    <!--弹框-->
    <!-- 创建群组弹框-->
    <v-bottom-sheet
      v-model="dialog_create_group"
    >
      <v-card>
        <v-card-text>
          <v-layout
            column
            align-center>
            <v-radio-group
              v-model="group_type"
              row>
              <v-radio
                :value="0"
                label="工作群组"/>
              <v-radio
                :value="1"
                label="日程群组"/>
            </v-radio-group>
            <v-text-field
              v-model="group_name"
              style="width: 100%"
              label="群组名称"
              @blur="scrollTo"
            />
            <v-flex>
              <v-btn
                flat
                color="primary"
                @click="createGroupPopClose()">取消
              </v-btn>
              <v-btn
                :disabled="!group_name"
                flat
                color="primary"
                @click="createGroupSubmit()">确定
              </v-btn>
            </v-flex>
          </v-layout>
        </v-card-text>
      </v-card>
    </v-bottom-sheet>


    <!--选择加入的群组-->
    <Dialogs
      v-model="dialog_apply_join_group"
      :title="title"
      :show.sync="dialog_apply_join_group"
    >
      <div
        slot="dialogs-title"
        class="dialogs-title pa-2 mt-4">请先选择要加入的群组</div>
      <v-list
        slot="dialogs-content"
        three-line
        subheader>
        <v-container fluid>

          <!--选择列表框-->
          <treeselect
            :disable-branch-nodes="true"
            v-model="add_groups_id"
            :options="groups_list"
            :max-height="150"
            :always-open="true"
            :default-expand-level="0"
            :z-index="0"
            placeholder="请先选择要加入的群组"
          />

        </v-container>

        <!--角色选择框-->
        <div v-show="seleted_role">
          <div
            slot="dialogs-title"
            class="dialogs-title pa-2 mt-1">请选择群组身份</div>
          <!-- 选择角色 选择列表框-->

          <v-select
            :items="role_list"
            v-model="add_role_id"
            height="25"
            class="big_select px-3 mb-2"
            hide-details
            single-line
            label="请选择群组角色"/>
          <GroupRoleIntroduction
            :dialog_type="role_type"
            class="px-3 mb-3"
          />
        </div>
        <!--申请按钮-->
        <v-layout
          style="width:100%; position:fixed; bottom:0; left:0;"
          justin-center>
          <v-btn
            :disabled="!add_groups_id || !add_role_id"
            block
            class="mb-1"
            @click="applyJoinGroupSubmit"
          >申请加入</v-btn>
        </v-layout>
      </v-list>
    </Dialogs>

    <!--退出群组-->
    <v-dialog
      v-model="dialog_exit_group"
      max-width="290"
    >
      <v-card>
        <v-card-title class="headline">退出群组</v-card-title>
        <v-card-text>
          您确定要退出 {{ group_name }} 吗？
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            color="green darken-1"
            flat="flat"
            @click="dialog_exit_group = false"
          >
            取消
          </v-btn>
          <v-btn
            color="green darken-1"
            flat="flat"
            @click="exitGroupConfirm()"
          >
            确定
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!-- 转让群主弹框 -->

    <!--todo-->

    <!--    &lt;!&ndash;删除成员弹框&ndash;&gt;-->
    <!--    <v-layout-->
    <!--      row-->
    <!--      justify-center>-->
    <!--      <v-dialog-->
    <!--        v-model="dialog_kick_out"-->
    <!--        class="dialogs"-->
    <!--        fullscreen-->
    <!--        hide-overlay-->
    <!--        transition="dialog-bottom-transition">-->
    <!--        <v-card>-->
    <!--          <v-toolbar-->
    <!--            dark-->
    <!--            color="primary">-->
    <!--            <v-btn-->
    <!--              icon-->
    <!--              dark-->
    <!--              @click="dialog_kick_out = false">-->
    <!--              <v-icon-->
    <!--                small-->
    <!--                class="iconfont dudu-guanbi1"/>-->
    <!--            </v-btn>-->
    <!--            <v-toolbar-title class="subheading">删除机构下成员</v-toolbar-title>-->
    <!--            <v-spacer/>-->
    <!--          </v-toolbar>-->
    <!--          <v-list-->
    <!--            three-line-->
    <!--            subheader>-->
    <!--            <v-subheader>请选择要删除的成员</v-subheader>-->
    <!--            <v-container-->
    <!--              class="py-0"-->
    <!--              fluid>-->
    <!--              &lt;!&ndash;选择列表框&ndash;&gt;-->
    <!--              <treeselect-->
    <!--                :disable-branch-nodes="true"-->
    <!--                v-model="deltel_user_id"-->
    <!--                :options="options"-->
    <!--                :max-height="250"-->
    <!--                :always-open="true"-->
    <!--                :default-expand-level="0"-->
    <!--                :z-index="0"-->
    <!--                placeholder="请选择要删除的成员"-->
    <!--              />-->
    <!--            </v-container>-->
    <!--          </v-list>-->
    <!--        </v-card>-->

    <!--        <v-layout-->
    <!--          justin-center-->
    <!--          style="width:100%; position:fixed; bottom:0; left:0;">-->
    <!--          <v-btn-->
    <!--            block-->
    <!--            class="mb-1"-->
    <!--            @click="deletedialogs"-->
    <!--          >确认删除-->
    <!--          </v-btn>-->
    <!--        </v-layout>-->
    <!--      </v-dialog>-->
    <!--    </v-layout>-->

    <!--    &lt;!&ndash;删除确认弹框&ndash;&gt;-->
    <!--    <dialogs-->
    <!--      v-model="dialogs"-->
    <!--      :title="title"-->
    <!--      :text="text"-->
    <!--      :dialog.sync="dialogs"-->
    <!--      :fn="deltel_user"-->
    <!--    />-->

    <!--新手引导-->
    <message-guide
      :iswhu="guide_show"
      :text="text"
      :css="css"
      :fn="clickfun"
    />
  </div>
</template>


<script>
import {mapMutations, mapState, mapGetters, mapActions} from "vuex";
import MessageGuide from "../../Messages/MessageGuide";
import Dialogs from "../../Commons/Dialogs";
// import the component
import Treeselect from "@riophae/vue-treeselect";
// import the styles
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import GroupRoleIntroduction from "./GroupRoleIntroduction";

export default {
	name: "HeaderText",
	inject: ["reload"],
	components: {
		Treeselect,
		MessageGuide,
		Dialogs,
		GroupRoleIntroduction
	},
	props: {
		header_text: {
			type: String,
			default: () => ""
		},
		entry_type: {
			type: String,
			default: () => ""
		}
	},
	data() {
		return {
			// 当前用户相关数据
			org_id: null,
			group_id: null, // 表示获取当前群组的id值
			group_name: null,
			group_type: 0,
			group_type_id: null, // 表示获取当前组的类别id值
			group_btn_show: false,
			group: null,
			// 权限控制
			create_group_control: { // 创建群组功能
				arr: [1, 2, 3],
				permission: false
			},
			kick_out_control: { // 踢人出机构功能
				arr: [1, 2],
				permission: false
			},
			role_id: null, // 表示获取当前用户的角色id值
			// 弹框开关
			dialog_create_group: false, // 创建群组
			dialog_apply_join_group: false, // 申请加入群组
			dialog_change_group_owner: false, // 群主转让
			dialog_exit_group: false, // 退出群组
			dialog_kick_out: false, // 踢出机构

			items: null,
			role_items: [],
			change_users: null,
			change_roles: null,
			current_group_role_items: [], // 测试用下拉群组成员
			targets: null, // 邀请对象
			targets_items: [1, 23, 3], // 候选邀请对象
			options: [],
			containSons: false,
			filter_array: [],
			dialogs: false,
			title: "",
			delte_btn: false,
			add_groups_id: null,
			add_role_id: null,
			role_type: 2,
			groups_list: [],
			role_list: [],
			introduce: [],
			add_groups: false, // 判断是否显示添加群组按钮
			seleted_role: false,
			// 新手指引
			dom_ready: false,
			guide_show: false,
			text: null,
			css: [{}, {}, {}],
			clickfun: null,
			restext: null
		};
	},
	computed: {
		...mapState(["selected_org", "user_info"]),
		...mapGetters(["guide_info"]),
	},
	watch: {
		add_groups_id() {
			this.getGroupRole();
		},
		dom_ready() {
			this.initGuide();
		}
	},
	updated() {
		this.$nextTick(() => {
			this.dom_ready = true;
		});
	},
	mounted() {
		// 初始化数据
		this.initData();

		//获取机构下面所有的群组
		this.getOrgGroups();

		// 获取可邀请加入群组的用户
		this.getOrgUser();
	},

	methods: {
		...mapMutations(["setUserInfo", "haveOrgAccess"]),
		...mapActions(["initUser"]),
		// 初始化数据
		initData() {
			// 机构id
			this.org_id = this.selected_org ? this.selected_org.id : null;
			// 群组id
			if (this.entry_type === "group_user_list") {
				this.getGroupInfo();
			} else {
				this.group_btn_show = true;
			}
			// 权限管理相关
			this.haveOrgAccess(this.create_group_control);
			this.haveOrgAccess(this.kick_out_control);
		},
		// 判断是否进行新手引导
		initGuide() {
			let el = null;
			let arrow = null;
			switch (this.guide_info) {
			// 引导创建部门
			case "insert_groups":
				// 引导文案
				this.text = "请创建工作群组，后续可给群成员发放任务和通知";
				// 点击事件
				this.clickfun = this.createGroupPop;
				// 定制蒙版样式
				el = this.$refs.mask.$el.getBoundingClientRect();
				arrow = {
					top: el.top + 10 + "px",
					left: el.left - 210 + "px",
				};
				this.css = this.getGuideCss(el, arrow);
				this.restext = "create_group";
				// 开启蒙版
				this.guide_show = true;
				break;
			case "add_groups" :
				// 引导文案
				this.text = "请先加入工作群组，方便后续开展工作";
				// 点击事件
				this.clickfun = this.applyJoinGroupPop;
				// 定制蒙版样式
				el = this.$refs.masks.$el.getBoundingClientRect();
				arrow = {
					top: el.top + 10 + "px",
					left: el.left - 210 + "px",
				};
				this.css = this.getGuideCss(el, arrow);
				this.restext = "create_group";
				// 开启蒙版
				this.guide_show = true;
				break;
			}
		},

		// 机构相关功能 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
		// 获取机构下面所有的群组
		getOrgGroups() {
			this.axios.get(`/api/group/getallgroups/${this.$store.state.selected_org.id}`).then((res) => {
				let data = res.data.data;
				for (let index in res.data.data) {
					var temp_data = {
						id: index,
						label: index,
						children: []
					};
					data[index].forEach((item, i) => {
						item.label = item.name;
						temp_data.children.push(item);
					});
					this.groups_list.push(temp_data);
				}
			}).catch((err) => {
			});
		},

		//获取此机构下所有用户
		getOrgUser() {
			this.axios.get(`/api/user/org/${this.$store.state.selected_org.id}/flag/${this.containSons}`).then((res) => {
				let data = res.data.data;
				for (let index in res.data.data) {
					var temp_data = {
						id: index,
						label: index,
						children: []
					};
					var temp_array = [];
					// 判断该用户是否已存在或者是否已接收到了任务或通知
					data[index].forEach((value, i) => {
						if (value.pivot.role_id != 1) {
							if (this.filter_array.indexOf(value.id) === -1) {
								value.label = value.name;
								temp_array.push(value);
								this.filter_array.push(value.id);
							}
						}
					});

					if (temp_array.length !== 0) {
						if (temp_array[0].id !== this.user_info.id) {
							temp_data.children = temp_array;
							this.options.push(temp_data);
						}
					}
				}


			}).catch((err) => {

			});
		},
		// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

		// 群组相关功能 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
		// 获取群组数据
		getGroupInfo() {
			this.group_id = this.$route.query.group_id;
			// 判断用户是否是该群组成员,如果是获取更多信息
			if (this.user_info.smart_groups[this.group_id]) {
				this.group = this.user_info.smart_groups[this.group_id];
				this.role_id = this.user_info.smart_groups[this.group_id].pivot.role_id;
				this.group_type_id = this.user_info.smart_groups[this.group_id].type;
				this.group_btn_show = true;
			}
		},
		// 获取角色列表
		getGroupRole() {
			let groups_id = this.add_groups_id;
			this.axios.get(`/api/group/info/${groups_id}`).then((res) => {
				let group = res.data.data;
				group.type == 1 ? this.role_type = 3 : this.role_type = 2;
				this.axios.get("/api/role/get?type=" + this.role_type).then((res) => {
					this.role_list = res.data.data;
					if (this.role_list[0]["name"] == "群组创建人") this.role_list.shift();
					// 构造角色选项列表
					this.role_list.forEach((value, index) => {
						if (value.type == 3) {
							this.defaults = "日程共享人";
							// 角色描述文案
							let desp = "";
							switch (value.name) {
							case "日程共享人":
								desp = "日程共享人";
								this.add_role_id = value.id;
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
						// 角色描述文案
						let desp = "";
						switch (value.name) {
						case "任务签收人":
							desp = "任务签收人";
							this.add_role_id = value.id;
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
					this.seleted_role = true;
				});
			});
		},
		// 创建群组弹框
		createGroupPop() {
			this.dialog_create_group = true;
		},
		// 创建属于自己的群组
		createGroupSubmit() {
			this.axios.post("/api/group/store", {
				org_id: this.org_id,
				name: this.group_name,
				type: this.group_type
			}).then((res) => {
				if (res.data.errcode == 0) {

					if (this.restext != null) {
						this.initUser().then(() => {
							this.$toast("您已完成新手指引，接下来可以邀请其他用户加入机构或群组，进行都督微办公！", "success");
							this.$router.push({path: "/organizations"});
							this.reload();

						});
						return;
					}
					this.initUser().then(() => {
						this.$toast("创建群组成功！", "success");
						this.dialog_create_group = false;
						this.group_name = "";
						this.reload();
					});

				}
			}).catch((Err) => {
				// console.log(Err);
			});
		},
		// 创建群组弹窗关闭
		createGroupPopClose() {
			this.dialog_create_group = false;
			this.group_name = "";
			this.group_type = 0;
		},
		// 申请加入群组弹窗
		applyJoinGroupPop() {
			this.dialog_apply_join_group = !this.dialog_apply_join_group;
			this.title = "请选择要加入的群组!";
		},
		// 申请加入群组
		applyJoinGroupSubmit() {
			let guide_msg = this.guide_info;
			if (guide_msg === "add_groups") {
				let storage = window.localStorage;
				if (!storage.addGroups) {
					storage.setItem("addGroups", 1);
				}
			}

			let postData = {
				org_id: this.$store.state.selected_org.id,
				role_id: this.add_role_id
			};

			this.axios.post(`/api/group/addgroup/${this.add_groups_id}`, postData).then((res) => {
				if (res.data.errcode === 0) {
					this.initUser().then(() => {
						this.$toast("已向群主发送申请！请等待群主同意!", "success");
						this.reload();
					});

				} else {
					this.$toast(res.data.errmsg, "error");
				}

			}).catch((err) => {

			});
		},
		// 邀请用户加入群组
		inviteJoinGroup() {
			this.$router.push({path: `/invite_join_group/${this.group_id}?org_id=${this.group.org_id}&group_name=${this.header_text}`});
		},

		// 确认退出当前的群组
		exitGroupConfirm() {
			// Vuex的state状态属性
			var n_user_info = this.$store.state.user_info;
			var old_groups = n_user_info.groups;
			var new_groups = [];

			if (this.group_id) {
				this.axios.post("/api/group/leave_group", {
					group_id: this.group_id
				}).then((res) => {
					if (res.data.errcode === 0) {
						this.$toast("退出当前群组成功!", "success");
						// 循环遍历群组,将新的群组存入新数组
						setTimeout(() => {
							old_groups.forEach((val, index) => {
								if (val.id !== this.group_id) {
									new_groups.push(val);
								}
							});
							n_user_info.groups = new_groups;
							this.initUser().then(() => {
								// 将当前用户组id置为空
								this.group_id = null;
								this.reload();
								this.$router.go(-1);
							});
						}, 1000);
					} else {
						this.$toast(res.data.errmsg, "warning");
						this.dialog_exit_group = false;
					}
				}).catch((err) => {
				});
			}
		},
		// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	},
};
</script>

<style scoped>
  .select_style {
    /* border: 1px #ccc solid; */
    padding: 10px;
  }

  .border {
    width: 50%;
    padding: 0;
    margin: 0;
    float: left;
    box-sizing: border-box;
  }

  .dialogs {
    overflow: hidden;
  }

  .dialogs-title{
    border-left:red 3px solid;
    border-top:#eee 1px dashed;
    border-bottom:#eee 1px dashed;
  }

  .introduce {
    width: 93%;
    box-shadow: -2px 4px 8px 0px #b3a0a0;
    padding: 9px;
    margin-bottom: 7px;
    font-size: .9rem;
  }

  .btn_font {
    font-size: .9rem;
  }
</style>