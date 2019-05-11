<template>
  <div>
    <v-container v-if="!isLoading">
      <v-layout column>
        <!--搜索框-->
        <div v-if="!has_code">
          <v-flex
          >
            <v-text-field
              :append-icon="search"
              v-model="search_key"
              :loading="searching"
              class="mt-3"
              flat
              placeholder="机构编码/机构名称/手机号"
              background-color="white"
              solo
              single-line
              hide-details
              @blur="scrollTo"
            />
          </v-flex>
          <v-flex
            class="search_org_tips pt-3">
            <p class="#2d1f1f--text">
              <span class="red--text">*</span>
              请联系你所在机构管理员获取机构编码
              <br>
              <span class="red--text">*</span>
              也可通过搜索已加入机构同事的手机号进行添加
            </p>
          </v-flex>
        </div>

        <div v-if="form_show">
          <!--姓名等个人信息-->
          <v-flex
            v-if="need_info"
            class="pb-3">
            <!--<v-flex class="mt-2">-->
            <!--<span class="color-red">请如实填写个人信息。</span>-->
            <!--</v-flex>-->

            <!--姓名-->
            <v-text-field
              v-model="name"
              class="mb-1"
              placeholder="填写姓名或昵称"
              hide-details
              @blur="scrollTo"/>
            <p class="caption grey--text mb-0">请填写姓名或昵称</p>
          </v-flex>

          <v-flex>
            <v-layout column>
              <v-flex class="mb-1">
                <v-select
                  v-model="org"
                  :items="org_items"
                  :readonly="org_items.length<=1"
                  placeholder="机构名称"
                  item-value="id"
                  return-object
                  hide-details
                  single-line
                  @change="selectOrg"/>
              </v-flex>
              <v-flex>
                <p class="caption grey--text mb-0">{{ org.region }}</p>
              </v-flex>
            </v-layout>
          </v-flex>

          <!-- 选择角色及提示文案  start -->
          <v-flex v-if="!in_org">
            <v-select
              v-model="role"
              :items="role_items"
              class="mb-1"
              hide-details
              single-line
              label="选择机构角色"/>
            <v-layout>
              <span
                class="caption mb-0 grey--text align-self-center"
                @click="role_info_dialog = true">
                <v-icon
                  small
                  color="blue"
                  class="iconfont dudu-yiwen"/>
                查看角色说明
              </span>
            </v-layout>
          </v-flex>

          <v-flex v-if="dept_items.length !== 1">
            <v-select
              v-model="dept"
              :items="dept_items"
              no-data-text="此机构下无部门"
              single-line
              hide-details
              class="mb-1"
              label="选择部门"/>
            <p class="caption grey--text mb-0">选择要加入的部门</p>
          </v-flex>

          <v-flex
            v-if="group"
            class="mt-1"
          >
            <v-text-field
              v-model="group.name"
              class="mb-1"
              readonly
              hide-details
              @blur="scrollTo"
            />
            <p class="caption grey--text mb-0">若申请通过，将以任务签收人身份加入此群组</p>
          </v-flex>

          <v-flex
            class="mt-5">
            <v-btn
              :disabled="!name || !search_key"
              block
              class="white"
              @click="submit()"
            >申请加入</v-btn>
          </v-flex>
        </div>
      </v-layout>
    </v-container>

    <v-dialog
      v-model="role_info_dialog"
    >
      <v-card>
        <v-card-title
          class="subheading grey lighten-2"
          primary-title
        >
          角色说明
        </v-card-title>

        <v-card-text class="px-0">
          <v-list 
            two-line
            subheader>
            <v-list-tile 
              avatar 
              class="mb-3">
              <v-list-tile-content >
                <v-list-tile-title>系统管理员</v-list-tile-title>
                <p class="caption grey--text pt-1 mb-0">{{ role_text1 }}</p>
              </v-list-tile-content>
            </v-list-tile>
            <v-list-tile 
              avatar 
              class="mb-3">
              <v-list-tile-content >
                <v-list-tile-title>任务管理员</v-list-tile-title>
                <p class="caption grey--text pt-1 mb-0">{{ role_text2 }}</p>
              </v-list-tile-content>
            </v-list-tile>
            <v-list-tile avatar>
              <v-list-tile-content >
                <v-list-tile-title>内部成员</v-list-tile-title>
                <p class="caption grey--text pt-1 mb-0">{{ role_text3 }}</p>
              </v-list-tile-content>
            </v-list-tile>
          </v-list>
        </v-card-text>

        <v-divider/>

        <v-card-actions>
          <v-spacer/>
          <v-btn
            color="primary"
            flat
            @click="role_info_dialog = false"
          >
            我知道了
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <component
      v-if="isLoading"
      :is="cView"/>
  </div>
</template>

<script>
import Loading from "../Commons/Loading";
import { mapState } from "vuex";

export default {
	name: "ApplyJoinOrg",
	components: {
		Loading
	},
	data() {
		return {
			isLoading: true,
			cView: "Loading",
			search_key: null, // 机构编码搜索词
			time_out: null,
			org: {
				name: "无"
			},
			org_items: [],
			searching: false,
			dept: null, // 部门
			dept_items: [],
			role: 4,
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
			],
			name: "",
			identity: "",
			tel: "",
			sex: null,
			tel_rules: [
				v => !!v || "请填手机号",
				v => (v&&v.length == 11) || "手机号必须为11位",
			],
			code_rules: [
				v => !!v || "请填写验证码"
			],
			btntext:"获取验证码",
			sms_code:"",
			countNum:0,
			disabled:false,
			search:"search",
			role_text:null,
			org_default_id:null,
			role_text1: "拥有除“超级管理员”外的所有权限，可审批“任务管理员”。",
			role_text2: "可创建群组，审批“内部成员”，下发任务、通知，代设日程等，拥有“内部成员”权限。",
			role_text3: "可接收任务、通知，查阅所在机构的信息，使用都督提供的应用工具。",
			role_info_dialog: false,

			// 信息展示开关
			form_show: false, // 展示表单
			need_info: false,  // 是否需要填写用户信息
			has_code: false,
			in_org: false, // 是否已加入此机构，如果是将不展示角色选框
			group: null, // 群组
		};
	},
	computed: {
		...mapState(["user_info"]),
		telLegal () {
			return this.tel? this.tel.length === 11 : false;
		}
	},
	watch: {
		role () {
			this.selectedRole(this.role);
		},

		search_key: function(newV, oldV) {
			clearTimeout(this.time_out);
			this.time_out = setTimeout(() => {
				this.searchOrg();
			}, 600);
		}
	},
	mounted() {
		if (this.$route.query.org_code) {
			this.has_code = true;
			this.search_key = this.$route.query.org_code;
		} else {
			this.isLoading = false;
		}
		this.name = this.user_info.name;

		// 如果用户非初次加入机构则不需要再填写姓名
		if(this.user_info.orgs.length === 0 || !this.name){
			this.need_info = true;
		}
	},
	methods: {

		// 切换显示介绍文案
		selectedRole(role){
			let role_text_list = {
				2:"系统管理员：拥有除“超级管理员”外的所有权限，可审批“任务管理员”。",
				3:"任务管理员：可创建群组，审批“内部成员”，下发任务、通知，代设日程等，拥有“内部成员”权限。",
				4:"内部成员：可接收任务、通知，查阅所在机构的信息，使用都督提供的应用工具。"
			};
			this.role_text = role_text_list[role];
		},

		searchOrg() {
			if (!this.search_key) {
				this.org_items = [];
				this.form_show = false;
			} else {

				this.searching = true;

				let url = `/api/org/getByCode/${this.search_key}`;

				if (this.$route.query.group_id) {
					url += `?group=${this.$route.query.group_id}`;
				}

				this.axios
					.get(url)
					.then(res => {
						this.searching = false;

						if (res.data.data == null || res.data.data.length === 0) {
							this.$toast("无搜索结果", "warning");
							this.org = {};
							this.form_show = false;
							return;
						}

						this.org_items = res.data.data;

						this.org_items.forEach((value,index) => {
							value.text = `${value.name}`;
						});
						this.org = this.org_items[0];
						this.selectOrg();
						this.isLoading = false;
					})
					.catch(err => {

					});
			}
		},

		selectOrg() {
			this.form_show = true;
			this.dept = null;

			// 如果是邀请加入群组，判断用户是否在机构内
			if (this.$route.query.group_id) {
				let flag = 0;
				let user_name = "";
				this.org.users.forEach((value, index) => {
					if (this.$store.state.user_info.id == value.id) {
						flag = 1;
					}
					if (this.$route.query.user_id == value.id) {
						user_name = value.name;
					}
				});

				if (flag == 1) {
					let content = `用户「${this.org.name}-${user_name}」邀请你加入群组「${
						this.$route.query.group_name
					}」，请及时处理`;
					this.$router.push({
						path: "/check_invite_group",
						query: {
							content: content,
							user_id: this.$route.query.user_id,
							group_id: this.$route.query.group_id
						}
					});
				}

				this.group = {
					id: this.$route.query.group_id,
					name: this.$route.query.group_name
				};
			}


			let arr = [];

			this.org.depts.forEach((value, index) => {
				// 筛选出机构本部作为默认选项
				if (value.name === this.org.name) {
					this.org_default_id = value.id;
				}
				value.text = value.name;
				value.value = value.id;
				arr.push(value);
			});

			this.dept_items = arr ;

			// 判断用户是否已加入此机构
			this.in_org = false;
			this.user_info.orgs.forEach((v, i) => {
				if(v.id === this.org.id){
					this.in_org = true;
					this.role = v.pivot.role_id;
				}
			});
		},

		submit() {
			// 部门判断
			if(this.dept == null){
				// 无部门机构
				if(this.dept_items.length === 1){
					// 用户未加入此部门
					if(!this.in_org){
						// 默认部门id
						this.dept = this.org_default_id;
					}else{
						this.$toast("您已加入此机构，请勿重复申请", "warning");
						return;
					}
				}else{
					this.$toast("请选择要加入的部门", "warning");
					return;
				}
			}

			// 角色判断
			if(!this.in_org){
				if(!this.role){
					this.$toast("请选择机构角色", "warning");
					return;
				}
			}

			let temp_data = {
				dept_id: this.dept,
				new_rules : true
			};

			if (this.name) {
				temp_data.name = this.name;
			}
			if (this.group) {
				temp_data.group_id = this.group.id;
			}
			this.axios
				.post(
					`/api/my/apply/org/${this.org.id}/role/${this.role}/joinOrg`,
					temp_data
				)
				.then(res => {
					if (res.data.errcode != 0) {
						this.$toast(res.data.errmsg, "warning");
					} else {
						this.$toast("已提交申请！", "success");
						if (this.$store.state.user_info.is_followed == 0) {
							this.$router.push("/qrcode_to_like");
						} else {
							this.$router.push("/works/0");
						}
					}
				})
				.catch(Err => {});
		}
	}
};
</script>

<style scoped>
.color-red {
color: red;
}
.inline-block {
display: inline-block;
}
.search_org_tips {
display: block;
font-size: .8rem;
color: #aaa;
}
.group-name{
height: 29px;
border-bottom:1px solid rgba(0,0,0,.42);
margin-bottom: 21px;
}

</style>