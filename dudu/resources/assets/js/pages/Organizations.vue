<template>
  <div>
    <v-container
      v-if="!isLoading"
      class="px-0 pb-0"
      style="margin-bottom:66px;background-color: #f5f5f5;">

      <!--搜索区-->
      <v-text-field
        v-model="search_value"
        class="mx-3 pt-3"
        flat
        placeholder="搜索（支持姓名和手机号）"
        background-color="white"
        append-icon="iconfont dudu-sousuo"
        solo
        single-line
        @blur="scrollTo"
      />

      <!--机构信息控制区域-->
      <v-layout class="mx-4 mb-3">
        <v-flex
          class="align-self-center "

          xs5>
          <h3 class="text-truncate">{{ org_name }}</h3>
        </v-flex>

        <v-flex
          class="align-self-center"
          xs7>
          <v-layout justify-end>
            <v-flex
              v-if="selected_org && user_info.orgs.length > 1"
              class="align-self-center"
            >
              <!--切换机构按钮-->
              <div
                class="grey--text"
                style="text-align: right;"
                @click="switchOrganization()">
                <v-icon
                  small
                  class="iconfont dudu-qiehuan1"
                />
                切换机构
              </div>
            </v-flex>
            <v-flex
              v-if="selected_org && accessControl.permission"
              class="align-self-center"
            >
              <!--机构管理-->
              <div
                ref="mask"
                class="grey--text"
                style="text-align: right;"
                @click="organizationFrame()">
                <v-icon
                  small
                  class="iconfont dudu-chilun"
                />
                配置管理
              </div>
            </v-flex>
            <!--<v-flex class="align-self-center ml-1">-->
            <!--&lt;!&ndash;退出机构按钮&ndash;&gt;-->
            <!--<div-->
            <!--v-if="selected_org && user_info.orgs.length > 0"-->
            <!--class="grey&#45;&#45;text"-->
            <!--style="text-align: right;"-->
            <!--@click="deleteDialog()">-->
            <!--<v-icon-->
            <!--small-->
            <!--class="iconfont dudu-outgroup"-->
            <!--/>-->
            <!--退出-->
            <!--</div>-->
            <!--</v-flex>-->
          </v-layout>
        </v-flex>

        <!--<v-flex-->
        <!--xs5-->
        <!--class="org-icon-box">-->
        <!--&lt;!&ndash;切换机构按钮&ndash;&gt;-->
        <!--<v-icon-->
        <!--v-if="selected_org && user_info.orgs.length > 1"-->
        <!--class="iconfont dudu-qiehuan1"-->
        <!--color="grey"-->
        <!--@click="switchOrganization()"-->
        <!--/>-->
        <!--&lt;!&ndash;设置按钮&ndash;&gt;-->
        <!--<v-icon-->
        <!--v-if="selected_org && accessControl.permission"-->
        <!--ref="mask"-->
        <!--class="iconfont dudu-chilun ml-2"-->
        <!--color="grey"-->
        <!--@click="organizationFrame()"-->
        <!--/>-->
        <!--&lt;!&ndash;退出机构按钮&ndash;&gt;-->
        <!--<v-icon-->
        <!--v-if="selected_org && user_info.orgs.length > 0"-->
        <!--class="iconfont dudu-outgroup ml-2"-->
        <!--color="grey"-->
        <!--@click="deleteDialog()"-->
        <!--/>-->
        <!--</v-flex>-->
      </v-layout>

      <!--组织架构-->
      <v-list
        v-if="selected_org && selected_org.depts.length !== 1"
        class="pa-0 mb-2">
        <v-list-tile
          class="pt-1 pb-1"
          style="border-bottom:solid 1px #eee;"
          @click="organizationviewgroupusers()">
          <v-layout class="ml-2 mr-1">
            <v-flex
              xs8
              class="align-self-center"
              style="font-size:14px;">
              <span>
                <v-icon
                  size="20"
                  class="item-icon iconfont dudu-jiagouguanli icon-green mr-2"/>组织架构</span>
            </v-flex>
            <v-flex
              xs4
              class="list-box align-self-center">
              <v-icon class="iconfont dudu-arrow"/>
            </v-flex>
          </v-layout>
        </v-list-tile>
      </v-list>

      <!--部门/单位/群组-->
      <v-list
        v-if="selected_org"
        class="pa-0 mb-2">
        <v-list-tile
          v-for="(item, index) in orgItems"
          :key="index"
          :ref="index"
          class="pt-1 pb-1"
          style="border-bottom:solid 1px #eee;"
          @click="contact(item)">
          <v-layout class="ml-2 mr-1">
            <v-flex
              xs8
              class="align-self-center"
              style="font-size:14px;">
              <span>

                <v-icon
                  :class="getItemClass(item.name)"
                  size="20"
                  class="item-icon mr-2"/>
                {{ item.name }}

              </span>
              <span class="grey--text lighten-3"> ({{ item.number }})</span>
            </v-flex>
            <v-flex
              xs4
              class="list-box align-self-center"
              style="font-size:14px;">
              <v-icon class="iconfont dudu-arrow grey--text lighten-1"/>
            </v-flex>
          </v-layout>
        </v-list-tile>
      </v-list>

      <!--邀请加入-->
      <v-list
        v-if="selected_org"
        class="pa-0 mb-2">
        <v-list-tile
          ref="masks"
          class="pt-1 pb-1"
          style="border-bottom:solid 1px #eee;"
          @click="colleagueInvitation()">
          <v-layout
            class="ml-2 mr-1">
            <v-flex
              xs8
              class="align-self-center"
              style="font-size:14px;">
              <span><v-icon
                size="20"
                class="item-icon iconfont dudu-yaoqing icon-green mr-2"/>邀请同事加入</span>
            </v-flex>
            <v-flex
              xs4
              class="list-box align-self-center">
              <v-icon class="iconfont dudu-arrow"/>
            </v-flex>
          </v-layout>
        </v-list-tile>
      </v-list>

      <v-list class="pa-0 mb-2">
        <!--注册顶级机构-->
        <v-list-tile
          class="pt-1 pb-1 "
          style="border-bottom:solid 1px #eee;"
          @click="organizationInvitation">
          <v-layout class="ml-2 mr-1">
            <v-flex
              xs8
              class="align-self-center"
              style="font-size:14px;">
              <span><v-icon
                size="20"
                class="item-icon iconfont dudu-shenqing mr-2 icon-green"/>创建机构</span>
            </v-flex>
            <v-flex
              xs4
              class="list-box align-self-center">
              <v-icon class="iconfont dudu-arrow"/>
            </v-flex>
          </v-layout>
        </v-list-tile>

        <!--加入其它机构-->
        <v-list-tile
          class="pt-1 pb-1"
          style="border-bottom:solid 1px #eee;"
          @click="organizationapplyjoimorg()">
          <v-layout class="ml-2 mr-1">
            <v-flex
              xs8
              class="align-self-center"
              style="font-size:14px;">
              <span><v-icon
                size="20"
                class="item-icon iconfont dudu-jiarubanji mr-2 icon-green"/>加入机构</span>
            </v-flex>
            <v-flex
              xs4
              class="list-box align-self-center">
              <v-icon class="iconfont dudu-arrow"/>
            </v-flex>
          </v-layout>
        </v-list-tile>

        <!--退出机构-->
        <v-list-tile
          v-if="selected_org"
          class="pt-1 pb-1"
          style="border-bottom:solid 1px #eee;"
          @click="deleteDialog()">
          <v-layout class="ml-2 mr-1">
            <v-flex
              xs8
              class="align-self-center grey--text"
              style="font-size:14px;">
              <v-icon
                size="20"
                class="item-icon iconfont dudu-outgroup mr-2 icon-grey"/>
              退出当前机构
            </v-flex>
            <!--<v-flex-->
            <!--xs4-->
            <!--class="list-box align-self-center">-->
            <!--<v-icon class="iconfont dudu-arrow"/>-->
            <!--</v-flex>-->
          </v-layout>
        </v-list-tile>
      </v-list>
    </v-container>

    <!--加载中..样式-->
    <component
      v-if="isLoading"
      :is="cView"
    />

    <!--  新手引导   -->
    <message-guide
      :iswhu="guide_show"
      :fn="clickfun"
      :text="text"
      :css="css"
    />
    <!--跳过按钮-->
    <div
      v-if="guide_show && miss_btn "
      class="miss-btn"
      @click="setStatus"
    >
      <button>跳过</button>
    </div>

    <!--删除确认弹窗-->
    <dialogs
      :title="title"
      :text="text"
      :dialog.sync="dialogs"
      :fn="exitOrg"
    />

    <!--切换机构盒子-->
    <v-bottom-sheet
      v-model="org_dialog"
    >
      <v-card>
        <v-card-text>
          <v-layout
            column
            align-center>
            <v-select
              v-model="org_changed"
              :items="org_items"
              background-color="white"
              label="切换机构"
            />
            <v-flex>
              <v-btn
                flat
                color="primary"
                @click="org_dialog=false">取消
              </v-btn>
              <v-btn
                :disabled="!org_changed"
                flat
                color="primary"
                @click="changeOrg()">确定
              </v-btn>
            </v-flex>
          </v-layout>
        </v-card-text>
      </v-card>
    </v-bottom-sheet>
  </div>
</template>

<script>
import Loading from "../components/Commons/Loading";
import MessageGuide from "../components/Messages/MessageGuide";
import {mapState, mapGetters, mapMutations, mapActions} from "vuex";
import Dialogs from "../components/Commons/Dialogs";
import Contacts from "../components/Organizations/DeptUserList";

export default {
	name: "Organizations",
	inject: ["reload"],
	components: {
		Contacts,
		Loading,
		MessageGuide,
		Dialogs
	},
	data() {
		return {
			isLoading: true,
			open_id: null,
			search_value: "",
			org_name: "暂无机构",
			orgItems: {},
			navTitle: null,
			cView: "Loading",
			time_out: null,
			org_dialog: false,
			org_changed: null,
			org_items: [],
			// 是否有权限点击设置按钮
			accessControl: {
				arr: [1, 2],
				permission: false
			},
			title: "",
			dialogs: false,
			// 新手指引
			dom_ready: false,
			guide_show: false,
			miss_btn: false,
			text: null,
			css: [{}, {}, {}],
			clickfun: null,

		};
	},
	computed: {
		...mapState(["selected_org", "user_info"]),
		...mapGetters(["guide_info"])
	},
	watch: {
		search_value: function (newV, oldV) {
			clearTimeout(this.time_out);
			this.time_out = setTimeout(() => {
				this.setSearchValue(newV);
				this.$router.push({path: "/organizations/search"});
			}, 500);
		},
		dom_ready() {
			// 判断 dom 元素是否完全渲染完毕
			// 判断新手引导功能是否开启
			this.initGuide();
		}
	},
	mounted() {

		// 初始化机构数据
		this.initData();
		// 初始化用户的机构权限
		this.haveOrgAccess(this.accessControl);
		// 存在storage的时候
	},
	updated() {
		this.$nextTick(() => {
			this.dom_ready = true;
		});
	},
	methods: {
		...mapActions(["initUser"]),
		...mapMutations(["haveOrgAccess", "setSearchValue", "setTempGuideInfo"]),
		// 跳过按钮
		setStatus() {
			this.setTempGuideInfo(false);
			this.miss_btn = false;
			this.guide_show = false;
			let storage = window.localStorage;
			if (!storage.isOneGuide) {
				storage.setItem("isOneGuide", true);
			}
		},
		// 初始化数据
		initData: function () {
			this.cView = "Loading";

			if (!this.selected_org) {
				// 未加入机构
				this.orgItems["group"] = {
					name: "我的群组",
					number: 0
				};

			} else {
				// 有加入机构的
				this.org_name = this.selected_org.name;
				this.selected_org.depts.forEach((value, index) => {
					if (this.user_info.smart_depts[value.id]) {
						this.orgItems[`dept${value.id}`] = {
							name: value.name,
							number: value.users.length,
							id: value.id,
							type: "dept",
						};
					}
				});
				let in_group_count = 0;
				this.selected_org.groups.forEach((v, i) => {
					if (this.user_info.smart_groups[v.id]) {
						in_group_count++;
					}
				});
				this.orgItems["group"] = {
					name: "我的群组",
					number: in_group_count,
					type: "group"
				};
			}

			this.isLoading = false;
		},
		//用户退出确认弹窗
		deleteDialog() {
			this.title = "退出机构确认";
			this.text = `您确定要退出${this.org_name}吗？`;
			this.dialogs = !this.dialogs;
		},
		// 用户退出机构
		exitOrg() {
			this.dialogs = false;
			this.isLoading = !this.isLoading;
			this.axios.post(`/api/org/exitOrg/${this.user_info.id}/org_id/${this.selected_org.id}`).then((res) => {
				let data = res.data.errcode;
				if (data === 0) {
					this.$toast("退出成功！", "success");
					this.initUser().then(() => {
						this.isLoading = !this.isLoading;
						this.$router.push({path: "works/0"});
						this.reload();
					});
					return;
				}

				this.$toast(res.data.errmsg, "error");
				this.isLoading = !this.isLoading;
			}).catch((err) => {

			});
		},

		// 判断是否进行新手引导
		initGuide() {
			let el = null;
			let arrow = null;
			switch (this.guide_info) {
			// 引导创建部门
			case "create_dept":
				// 引导文案
				this.text = "创建机构成功，接下来请按提示操作";
				// 点击事件
				this.clickfun = this.organizationFrame;
				// 定制蒙版样式
				el = this.$refs.mask.getBoundingClientRect();
				arrow = {
					top: el.top + "px",
					left: el.left - 220 + "px"
				};
				this.css = this.getGuideCss(el, arrow);
				// 开启蒙版
				this.guide_show = true;
				break;
				// 引导创建群组

			case "invite_users": {
				// 判断存在 storage 的情况
				const storage = window.localStorage;
				if (storage.isOneGuide) {
					this.guide_show = false;
					return;
				}
				// 引导文案
				this.text = "邀请同事加入机构";
				// 点击事件
				this.clickfun = this.colleagueInvitation;
				// 定制蒙版样式
				el = this.$refs.masks.$el.getBoundingClientRect();
				arrow = {
					top: el.top - 200 + "px",
					left: el.width / 2 + "px",
					transform: "rotate(90deg)",
				};
				this.css = this.getGuideCss(el, arrow, true);
				// 开启蒙版
				this.guide_show = true;
				break;
			}

			// 任务管理员及以上身份引导创建群组
			case "insert_groups" :
				// 引导文案
				this.text = "加入机构成功，请按指示创建工作群组，跳过可使用请示功能";
				// 点击事件
				this.clickfun = this.ofContact;
				// 定制蒙版样式
				el = this.$refs.group[0].$el.getBoundingClientRect();

				arrow = {
					top: el.top - 200 + "px",
					left: el.width / 10 + "px",
					transform: "rotate(90deg)",
				};
				this.css = this.getGuideCss(el, arrow, true);
				// 开启蒙版
				this.guide_show = true;
				this.miss_btn = true;

				break;
				// 普通用户引导加入群组
			case "add_groups" :
				// 引导文案
				this.text = "加入机构成功，请按指示加入工作群组，跳过可使用请示功能";
				// 点击事件
				this.clickfun = this.ofContact;
				// 定制蒙版样式
				el = this.$refs.group[0].$el.getBoundingClientRect();

				arrow = {
					top: el.top - 200 + "px",
					left: el.width / 10 + "px",
					transform: "rotate(90deg)",
				};
				this.css = this.getGuideCss(el, arrow, true);
				// 开启蒙版
				this.guide_show = true;
				this.miss_btn = true;

				break;
			}

		},
		ofContact() {
			let data = {name: "我的群组", type: "group"};
			this.contact(data);
		},
		// 跳转链接构建
		contact: function (val) {
			// 跳转后展示对象的 id
			let id = val.id;
			// 跳转页面链接构建
			let url = val.type === "dept" ? `/organizations/dept_user_list?dept_id=${id}` : "/organizations/group_list";
			// 跳转
			this.$router.push({path: url});
		},
		// 邀请用户加入
		colleagueInvitation: function () {
			let storage = window.localStorage;
			if (!storage.isOneGuide) {
				storage.setItem("isOneGuide", true);
			}

			let url = `/invite_join_org/${this.$store.state.selected_org.code}`;
			this.$router.push({path: url});
		},
		// 加入其他机构
		organizationInvitation: function () {
			let url = "/organizations/joinOrg";
			this.$router.push({path: url});
		},

		organizationapplyjoimorg() {
			let url = "/apply_join_org";
			this.$router.push({path: url});
		},

		organizationviewgroupusers() {
			let url = "/dept_list";
			this.$router.push({path: url});
		},

		// 切换机构按钮
		switchOrganization: function () {
			this.axios.get("/api/my/org").then((res) => {
				this.org_items = res.data.data;
				this.org_items.forEach((value, index) => {
					value.text = value.name;
					value.value = value.id;
				});
				this.org_dialog = true;
			});
		},
		//设置按钮
		organizationFrame: function (test) {
			if (test != null) {
				let url = "/organizations/frameWork";
				return this.$router.push({path: url, query: {in: test}});
			}
			let url = "/organizations/frameWork";
			this.$router.push({path: url});
		},
		getItemClass(item) {
			let itemClass = "iconfont dudu-bumen icon-blue";
			switch (item) {
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
		// 切换机构
		changeOrg(org_changed) {
			if (org_changed == undefined) {
				// 判断切换机构是否为当前机构
				if (this.org_changed === this.selected_org.id) {
					this.$toast("不能选择当前机构", "warning");
					return;
				}

				this.axios.get(`/api/my/org/${this.selected_org.id}/change_default?change_org=${this.org_changed}`).then((res) => {
					if (res.data.errcode === 0) {

						this.initUser().then(() => {
							this.$toast("切换成功");
							this.$router.go(0);
						});

					}
				});
			} else {
				this.axios.get(`/api/my/org/${this.selected_org.id}/change_default?change_org=${org_changed}`).then((res) => {
					if (res.data.errcode === 0) {
						this.initUser().then(() => {
							this.$toast("切换成功");
						});
					}
				});
			}
		}
	},
};

</script>

<style scoped>
.dudu-jiagouguanli:before {
color: red;
}

.org-icon-box {
text-align: right;
}

.list-box {
text-align: right;
}
/*跳过按钮*/
.miss-btn{
position: absolute;
z-index: 102;
width: 100px;
color: #ffffff;
height: 38px;
text-align: center;
line-height: 24px;
border: 1px solid;
padding: 5px 15px 5px 15px;
bottom:30%;
left: 0;
right: 0;
margin: auto;
}
  .item-icon{
    position: relative;
    top:2px;
  }
</style>