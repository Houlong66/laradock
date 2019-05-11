<template>
  <div>
    <div
      v-if="!isLoading">
      <v-layout>
        <v-flex
          class="align-self-center"
          offset-xs1>
          <span>请选择成员</span>
        </v-flex>
        <v-flex style="text-align:right;">
          <v-btn
            v-if="dept_items.length !== 0"
            ref="masks"
            flat
            color="primary"
            style="padding: 0"
            @click="showBatchDept()">批量修改部门</v-btn>
          <v-btn
            flat
            color="primary"
            style="padding: 0"
            @click="showBatchRole()">批量修改角色</v-btn>
        </v-flex>
      </v-layout>

      <v-layout
        column
      >
        <v-flex
          v-if="search_input.length > 0"
        >
          <v-layout
            class="pa-3"
            style="position: relative"
            column
          >
            <v-flex>
              <input
                v-model="search"
                class="search-input"
                type="text"
                placeholder="输入手机号或姓名查找用户"
              >
            </v-flex>
            <v-flex
              v-if="boxselect"
              class="search_box"
            >
              <ul
                v-if="states .length > 0"
                class="pl-0"
              >
                <li
                  v-for="(item,i) in states"
                  :key="i"
                  class="pa-2 select-box"
                  @click="selects(item.name)"
                >
                  {{ item.name }}
                </li>
              </ul>
              <div v-else>
                <p 
                  class="pa-2"
                  style="color: #8c8c8c">查无此人</p>
              </div>
            </v-flex>

          </v-layout>

        </v-flex>
        <v-flex>

          <v-data-table
            :items="show_userlist"
            v-model="selected"
            class="elevation-1"
            item-key="id"
            select-all
            hide-actions
          >
            <template
              slot="headers"
              slot-scope="props">
              <tr style="height: 50px">

                <th>
                  <v-layout>
                    <v-flex
                      xs2>
                      <v-checkbox
                        ref="mask"
                        :input-value="props.all"
                        :indeterminate="props.indeterminate"
                        class="mt-1"
                        primary
                        hide-details
                        @click.native="toggleAll"
                      />

                    </v-flex>

                    <v-flex
                      style="display: flex;flex-direction: row-reverse;"
                      xs10
                    >
                      <!--机构部门相关功能按钮组-->
                      <v-btn-toggle>
                        <!--删除成员-->
                        <v-btn
                          flat
                          color="blue"
                          @click="dialog_kick_out = true"
                        >
                          <v-icon
                            small
                            class="iconfont dudu-yichuhuiyuan pr-1"
                          />
                          移除机构成员
                        </v-btn>
                      </v-btn-toggle>
                    </v-flex>


                  </v-layout>


                </th>


              </tr>

            </template>

            <template
              slot="items"
              slot-scope="props">
              <v-layout class="item py-2">
                <v-flex
                  xs1
                  class="align-self-center">
                  <td style="display: block;">
                    <v-checkbox
                      v-model="props.selected"
                      primary
                      hide-details
                    />
                  </td>
                </v-flex>

                <v-flex
                  xs11
                  class="align-self-center">
                  <td style="display: block;">
                    <v-layout>
                      <v-flex
                        xs2
                        class="align-self-center"
                        style="text-align: center;">
                        <v-avatar :size="30">
                          <img
                            :src="props.item.avatar">
                        </v-avatar>
                      </v-flex>
                      <v-flex
                        xs2
                        class="align-self-center"
                        style="text-align: center;">
                        <span>{{ props.item.name }}</span>
                      </v-flex>
                      <v-flex
                        xs5
                        class="align-self-center text-truncate px-2"
                        style="text-align: center;color: #999">
                        <span
                          v-for="dept in selected_depts[props.item.id]"
                          :key="dept"
                        >{{ dept }}<br></span>
                      </v-flex>
                      <v-flex
                        xs3
                        class="align-self-center"
                        style="text-align: center; color: #999">
                        <span>{{ selected_roles[props.item.id] }}</span>
                      </v-flex>
                    </v-layout>
                  </td>
                </v-flex>
              </v-layout>
            </template>
          </v-data-table>

        </v-flex>
      </v-layout>
      <v-bottom-sheet
        v-model="dept_dialog"
        :persistent="true"
      >
        <v-card>
          <v-card-text>
            <v-layout
              column
              align-center>
              <v-select
                :items="dept_items"
                v-model="change_depts"
                :small-chips="true"
                :dense="true"
                style="width: 70%"
                multiple
                placeholder="请选择部门"/>
              <v-layout
                justify-space-between
                style="width: 115%">
                <v-flex xs3>
                  <v-btn
                    flat
                    color="rgba(0,0,0,.87)"
                    @click="cancelDept()">取消</v-btn>
                </v-flex>
                <v-flex xs3>
                  <v-btn
                    :disabled="change_depts.length==0"
                    flat
                    color="primary"
                    @click="confirmDept()">确定</v-btn>
                </v-flex>
              </v-layout>
            </v-layout>
          </v-card-text>
        </v-card>
      </v-bottom-sheet>
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
                style="width: 70%"
                placeholder="请选择角色"/>
              <v-flex>
                <v-btn
                  flat
                  color="rgba(0,0,0,.87)"
                  @click="cancelRole()">取消</v-btn>
                <v-btn
                  :disabled="!change_roles"
                  flat
                  color="primary"
                  @click="confirmRole()">确定</v-btn>
              </v-flex>
            </v-layout>
          </v-card-text>
        </v-card>
      </v-bottom-sheet>
    </div>



    <!--删除成员弹框-->
    <v-layout
      row
      justify-center>
      <v-dialog
        v-model="dialog_kick_out"
        class="dialogs"
        fullscreen
        hide-overlay
        transition="dialog-bottom-transition">
        <v-card>
          <v-toolbar
            dark
            color="primary">
            <v-btn
              icon
              dark
              @click="dialog_kick_out = false">
              <v-icon
                small
                class="iconfont dudu-guanbi1"/>
            </v-btn>
            <v-toolbar-title class="subheading">删除机构下成员</v-toolbar-title>
            <v-spacer/>
          </v-toolbar>
          <v-list
            three-line
            subheader>
            <v-subheader>请选择要删除的成员</v-subheader>
            <v-container
              class="py-0"
              fluid>
              <!--选择列表框-->
              <treeselect
                :disable-branch-nodes="true"
                v-model="deltel_user_id"
                :options="options"
                :max-height="250"
                :always-open="true"
                :default-expand-level="0"
                :z-index="0"
                placeholder="请选择要删除的成员"
              />
            </v-container>
          </v-list>
        </v-card>

        <v-layout
          justin-center
          style="width:100%; position:fixed; bottom:0; left:0;">
          <v-btn
            block
            class="mb-1"
            @click="deletedialogs"
          >确认删除
          </v-btn>
        </v-layout>
      </v-dialog>
    </v-layout>


    <component
      v-if="isLoading"
      :is="cView"
    />

    <!--新手引导-->
    <MessageGuide
      :iswhu="guide_show"
      :text="text"
      :fn="clickfun"
      :css="css"
    />

    <!--删除确认弹框-->
    <dialogs
      v-model="dialogs"
      :title="title"
      :text="text"
      :dialog.sync="dialogs"
      :fn="deltel_user"
    />


  </div>
</template>

<script>
import { mapGetters , mapState  , mapActions } from "vuex";
import Loading from "../Commons/Loading";
import MessageGuide from "../Messages/MessageGuide";

import Dialogs from "../Commons/Dialogs";

// import the component
import Treeselect from "@riophae/vue-treeselect";
// import the styles
import "@riophae/vue-treeselect/dist/vue-treeselect.css";

export default {
	name: "BatchOperation",
	inject: ["reload"],
	components: {
		Dialogs,
		Treeselect,
		Loading,
		MessageGuide
	},
	data() {
		return {
			cView: "Loading",

			isLoading: true,

			user_items: [
				{
					name: "",
					avatar: "",
					identity: ""
				}
			],

			show_userlist:[
				{
					name: "",
					avatar: "",
					identity: ""
				}
			],
			selected: [],
			dept_items: [],
			role_items: [],
			selected_depts: {},
			change_depts: [],
			selected_roles: {},
			dept_dialog: false,
			role_dialog: false,
			change_roles: "",

			// 新手指引
			dom_ready: false,
			guide_show: false,
			text:"",
			css: [{}, {}, {}],
			clickfun:null,

			// 搜索框
			search: null,
			states: [],
			search_input:null,
			boxselect:false,

			// 移除机构成员
			deltel_user_id: null,    //选中的用户的ID
			dialog_kick_out: false, // 踢出机构
			options: [],
			filter_array: [],
			title: "",
			dialogs: false,
			containSons: false,

		};
	},
	computed: {
		...mapState(["selected_org", "user_info","select_all"]),
		...mapGetters(["guide_info"]),
	},
	watch: {
		// 数据加载完成执行
		dom_ready(){
			// this.initGuide();
		},
		// 输入框
		search (val) {
			if (val.length > 0){
				this.boxselect = true;
			}else{
				this.boxselect = false;
				this.show_userlist = this.user_items;
			}
			val && val !== this.select && this.querySelections(val);
		},
	},
	mounted() {
		this.getItemList();
		this.initData();
	},
	updated (){
		this.$nextTick(() => {
			this.dom_ready = true;
		});
	},

	methods: {
		...mapActions(["initUser"]),
		initData(){
			this.search_input =   this.user_info.orgs.filter(n => {
				return n.id == this.selected_org.id && [1,2].indexOf(n.role.id) > -1 ;
			});

			// 获取可邀请加入群组的用户
			this.getOrgUser();
		},
		// 选择框中的点击事件
		selects(name,tel){
			this.show_userlist = this.user_items.filter(n => {
				return n.name.indexOf(name) > -1   ;
			});
			this.boxselect = false;
		},
		querySelections (v) {
			this.states =  this.user_items.filter(n => {
				return n.name.indexOf(v) > -1  || n.tel.indexOf(v) > -1;
			});
		},

		// 判断是否进行新手引导
		initGuide (dom) {
			let el = null;
			let arrow = null;
			if(dom == null){
				switch(this.guide_info){

				case "operation_members":
					this.text = "请按照指引操作，设置自己所属部门";
					this.clickfun = this.toggleAll;

					// 定制蒙版样式
					el = this.$refs.mask.$el.getBoundingClientRect();

					arrow = {
						top:  el.top +200 + el.height+  "px",
						left: el.left + "px",
						transform: "rotate(270deg)",
					};

					this.css = this.getGuideCss(el, arrow);
					this.guide_show = true;
					break;

				case "create_dept":
					this.text = "请按照指引操作，设置自己所属部门";
					this.clickfun = this.toggleAll;

					// 定制蒙版样式
					el = this.$refs.mask.$el.getBoundingClientRect();

					arrow = {
						top:  el.top +200 + el.height+  "px",
						left: el.left + "px",
						transform: "rotate(270deg)",
					};

					this.css = this.getGuideCss(el, arrow);
					this.guide_show = true;
					break;
				}
				return ;
			}

			this.text = "请按照指引操作，设置自己所属部门";
			this.clickfun = this.showBatchDept;
			// 定制蒙版样式
			el = dom;

			arrow = {
				top: el.top + (el.height/3) +  "px",
				left: el.left - 210   + "px",
				transform: "rotate(0)",
			};

			this.css = this.getGuideCss(el, arrow);

			this.guide_show = true;
		},
		//  删除成员 or 用户自己退出
		deltel_user() {
			this.axios.post(`/api/org/exitOrg/${this.deltel_user_id}/org_id/${this.selected_org.id}`).then((res) => {
				let data = res.data.errcode;
				if (data === 0) {
					this.$toast("删除成功！", "success");
					this.reload();
					return;
				}
				this.$toast(res.data.errmsg, "error");
			}).catch((err) => {

			});
		},
		//删除用户确认弹窗
		deletedialogs() {

			if (this.deltel_user_id == null || typeof this.deltel_user_id == "string") {
				this.$toast("请先选择要删除的成员！", "warning");
				return;
			}

			this.title = "确认删除此用户？";
			this.text = "删除用户之后，相应会在此机构所属部门或下级单位中删除！假如删除用户是群主，在群组用户大于2的情况下，会将群组权限自动转交到最早加入群组的用户身上";
			this.dialogs = !this.dialogs;
		},


		//获取此机构下所有用户
		getOrgUser() {
			let data = {
				org_id:this.selected_org.id
			};
			this.axios.get(`/api/user/org/${this.$store.state.selected_org.id}/flag/${this.containSons}`,{params:data}).then((res) => {
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


			});
		},


		getItemList () {
			let p1 = this.axios.get(`/api/dept/org/${this.$store.state.selected_org.id}`).then((res) => {
				this.dept_items = res.data.data;
				// 构造部门选项列表
				// let temp_index = null;
				this.dept_items.forEach((value,index) => {
					value.text = value.name;
					value.value = value.id;
				});

				// if(temp_index !== null){
				// 	this.dept_items.splice(temp_index, 1);
				// }
			});

			let p2 = this.axios.get("/api/role/get?type=1").then((res) => {
				this.role_items = res.data.data;
				// 构造角色选项列表
				this.role_items.forEach((value,index) => {
					if (value.id == 1) {
						value.disabled = true;
					}
					value.text = value.name;
					value.value = value.id;
				});
			});
			Promise.all([p1,p2]).then(() => {
				this.getUserList();
			});
		},
		getUserList () {
			this.axios.get(`/api/user/org/${this.$store.state.selected_org.id}`).then((res) => {
				this.user_items = res.data.data;

				let search_list  = [];
				// 构造部门、角色文案
				this.user_items.forEach((value,index) => {
					let temp_depts = [];
					value.depts.forEach((data) => {
						temp_depts.push(data.name);
					});
					value.orgs.forEach((org) => {
						if (org.id == this.$store.state.selected_org.id) {
							this.role_items.forEach((role) => {
								if (role.id == org.pivot.role_id) {
									this.selected_roles[value.id] = role.name;
								}
							});
						}
					});

					this.selected_depts[value.id] = temp_depts;

					search_list.push({name:value.name,tel:value.tel});
				});

				this.show_userlist = this.user_items;

				this.states = search_list;

				this.isLoading = false;
			});
		},

		toggleAll () {
			if (this.selected.length) {
				this.selected = [];
			} else {
				this.selected = [...this.show_userlist];

				if (this.dept_items.length == 1) {
					this.clickfun = this.showBatchDept;
				}

				// 判断进行的下一步
				if(this.selected_org.groups.length === 0   && this.selected_org.depts[0].users.length === 0 ){
					let dom  = this.$refs.masks.$el.getBoundingClientRect();
					this.initGuide(dom);
				}
			}
		},

		// 修改部门弹框
		showBatchDept () {
			if (!this.selected.length) {
				this.$toast("请先选择成员","warning");
				return;
			}
			// 如果只选中一个人，则默认选中他的当前部门
			if (this.selected.length === 1) {
				this.selected[0].depts.forEach((value,index) => {
					this.change_depts.push(value.id);
				});
			}
			this.dept_dialog = true;
		},

		cancelDept () {
			this.dept_dialog = false;
			this.change_depts = [];
		},

		// 确认修改部门
		confirmDept () {
			let targets = [];
			// 构造已选择角色的id数组
			this.selected.forEach((value) => {
				targets.push(value.id);
			});
			let temp_data = {
				targets: targets.join(","),
				depts: this.change_depts.join(","),
				org_id: this.$store.state.selected_org.id
			};
			this.axios.post("/api/dept/batch_change", temp_data).then((res) => {
				if (res.data.errcode == 0) {
					this.initUser().then(()=>{
						// 机构名称 + 我加入的群组(数量)
						this.$router.push({path: "/batch_operation"});
						this.$toast("修改成功", "success");
						this.reload();

					});
				}
			});
		},

		// 修改角色弹窗
		showBatchRole () {
			if (!this.selected.length) {
				this.$toast("请选择成员", "warning");
				return;
			}

			// 判断是否选择了超级管理员
			for (let i = 0; i < this.selected.length; i++) {
				if (this.selected_roles[this.selected[i].id] == "超级管理员") {
					this.$toast("不能修改超管的角色", "warning");
					return;
				}
			}
			// 判断是否只选中一个人，默认选择他的角色
			if (this.selected.length === 1) {
				this.selected[0].orgs.forEach((value,index) => {
					if (value.id === this.$store.state.selected_org.id) {
						this.change_roles = value.pivot.role_id;
					}
				});
			}
			this.role_dialog = true;
		},
		cancelRole () {
			this.role_dialog = false;
			this.change_roles = "";
		},
		// 确认修改角色
		confirmRole () {
			let targets = [];
			// 构造已选择角色的id数组
			this.selected.forEach((value) => {
				targets.push(value.id);
			});
			let temp_data = {
				targets: targets.join(","),
				roles: this.change_roles,
				role_type: "1",
				item: this.$store.state.selected_org.id
			};
			this.axios.post("/api/role/batch_change", temp_data).then((res) => {
				if (res.data.errcode == 0) {
					this.reload();
					this.$toast("修改成功", "success");
				} else {
					this.$toast(res.data.errmsg, "warning");
				}
			});
		}
	}
};
</script>

<style scoped>
.item td {
	height:auto!important;
}
.search-input{
  width: 100%;
  padding: 5px;
  border: 2px solid #cccccc;
}
.search_box{
  position: absolute;
  top: 52px;
  border: 1px solid #ccc;
  background: #ffffff;
  width: 92%;
  z-index: 999;
}
.select-box:hover{
  background: #cccccc;
}
.v-input--selection-controls {
	padding-top:0!important;;
}
  .autocompletes >>> .v-input__control{
    min-height: 34px !important;
  }
</style>
