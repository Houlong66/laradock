<template>
  <div>
    <!--首屏   start -->
    <v-container
      v-if="has_org"
      class="pa-0 header-titile">

      <v-layout
        column
        align-center>
        <v-flex
          style="width:80%; border-bottom:solid 1px #ddd"
          class="py-2 mt-3 mb-4 ">
          <p
            class="pt-3 pb-1 min-phone"
            style="text-align:center; font-size:16px!important;">
            工作落实难，<span
              class="font-weight-light"
              style="color:red">都督</span>帮你忙
          </p>
        </v-flex>

        <v-flex
          class="right-box c1">
          <v-layout justify-center>
            <v-flex
              xs2
              class="align-self-center">
              <v-icon
                color="blue"
                class="iconfont dudu-xianshi"/>
            </v-flex>
            <v-flex
              xs5
              class="align-self-center">
              <p class="mb-0">任务限时督办</p>
            </v-flex>
          </v-layout>
        </v-flex>

        <v-flex
          justify-center
          class="right-box c1">
          <v-layout
            justify-center
          >
            <v-flex
              xs2
              class="align-self-center">
              <v-icon
                color="orange"
                class="iconfont dudu-notice mr-2"/>
            </v-flex>
            <v-flex
              xs5
              class="align-self-center">
              <p class="mb-0">通知接收反馈</p>
            </v-flex>
          </v-layout>
        </v-flex>

        <v-flex
          justify-center
          class="right-box c1">
          <v-layout
            justify-center
          >
            <v-flex
              xs2
              class="align-self-center">
              <v-icon
                color="green"
                class="iconfont dudu-richeng1"/>
            </v-flex>
            <v-flex
              xs5
              class="align-self-center">
              <p class="mb-0">日程共享设置</p>
            </v-flex>
          </v-layout>
        </v-flex>

        <v-flex
          justify-center
          class="right-box c1">
          <v-layout
            justify-center
          >
            <v-flex
              xs2
              class="align-self-center">
              <v-icon
                color="brown"
                class="iconfont dudu-yingyong"/>
            </v-flex>
            <v-flex
              xs5
              class="align-self-center">
              <p class="mb-0">众多公务应用</p>
            </v-flex>
          </v-layout>
        </v-flex>


        <v-flex
          class="btn"
          @click="$router.push('/organizations/joinOrg')">
          <v-btn
            large
            style="border-radius: 50px; width: 100%"
            color="error">创建机构</v-btn>
        </v-flex>

        <v-flex
          class="pb-3 btn"
          @click="$router.push('/apply_join_org')">
          <v-btn
            large
            style="border-radius: 50px; width: 100%"
            color="error">加入机构</v-btn>
        </v-flex>
      </v-layout>

    </v-container>

    <!--首屏   end -->


    <!--三个模块-->
    <div v-if="!has_org">
      <v-tabs
        v-model="work_type_index"
        fixed-tabs
        style="position:fixed; top:0; z-index:4; width:100%;">
        <v-tabs-slider color="red"/>
        <v-tab
          v-for="(item, index) in items"
          :key="index"
          @click="changeWorkType(index)">
          {{ item.title }}
        </v-tab>
      </v-tabs>

      <div class="mt-5"/>

      <v-select
        v-if="work_type==='ask'"
        v-model="selected_asks_type"
        :items="asks_type"
        label="请示类型"
        box
        color="red"
        append-inner-icon="iconfont dudu-arrow_down"
        @change="changeAskType"
      />


      <!--
这里存在着需要相应展示的模块
start
模块消息只在任务跟通知模块展示
-->

      <div
        v-if="work_type === 'task' ||work_type === 'notification' "
        style="margin-top: 76px;"
      >
        <!--提醒加入工作群组 start-->
        <div v-if="!has_group && flag == 2">
          <v-flex
            justify-center
          >
            <v-layout
              v-if="selected_org.groups.length !== 0"
              align-center
              justify-center
              row
              class="mt-5">
              <h3>请先加入 <span style="color: red"> 工作 </span>群组</h3>

            </v-layout>

            <div
              v-if="selected_org.groups.length !== 0"
              style="font-size: .9rem;text-align: center;">
              <p>目前可使用<span style="color: red"> 请示 </span>功能</p>
            </div>

            <!--无群组的情况下-->
            <div
              v-else
              class="pb-2"
              style="font-size: 1rem;text-align: center;">
              您的机构尚未创建工作群组
              <p
                v-if="selected_org.pivot.role_id === 2 || selected_org.pivot.role_id === 3 || selected_org.pivot.role_id === 1"
              >请先创建工作群组</p>
              <p v-else>请等待或联系<span style="color: red"> 管理员 </span>创建工作群组</p>
            </div>


            <!--加入群组按钮-->
            <v-layout
              v-if="selected_org.groups.length !== 0"
              column
              align-center>

              <v-flex
                class="py-2 btn"
                @click="addDialog">
                <v-btn
                  large
                  class="py-2"
                  style="border-radius: 50px; width: 100%"
                  color="error">加入群组</v-btn>
              </v-flex>

            </v-layout>

            <!--直接创建自己的群组，在身份是管理员的时候-->
            <v-layout
              v-if="selected_org.pivot.role_id === 2 || selected_org.pivot.role_id === 3 || selected_org.pivot.role_id === 1"
              column
              align-center>
              <v-flex
                class="pb-3 btn"
                @click="createGroups">
                <v-btn
                  large
                  class="py-2"
                  style="border-radius: 50px; width: 100%"
                  color="error">创建群组</v-btn>
              </v-flex>

            </v-layout>

          </v-flex>
        </div>
        <!--提醒加入工作群组 end-->


        <!--新用户申请加入机构时  start-->
        <div
          v-if="tips"
          class="tips">
          <v-layout
            column
            align-center>

            <v-flex
              style="width:80%; border-bottom:solid 1px #ddd;"
              class="py-2 mb-2">
              <p
                class="subheading pt-3 pb-1 mb-0"
                style="text-align:center;">
                您已申请加入机构
              </p>
              <p
                class="subheading font-weight-light text-truncate"
                style="color:red; text-align: center;">{{ org_names }}</p>
              <p
                class="subheading py-2 mb-0"
                style="text-align: center;">请等待审核</p>
            </v-flex>

            <p
              class="mb-2"
              style="text-align:center; font-size:15px!important;padding:10px">
              你目前可以使用的功能是
            </p>

            <v-flex
              class="right-box c1">
              <v-layout
                justify-center
              >

                <v-flex
                  xs2
                  class="align-self-center">
                  <v-icon
                    color="blue"
                    class="iconfont dudu-richeng2"/>
                </v-flex>

                <v-flex
                  xs5
                  class="align-self-center">
                  <p class="mb-0">日程管理</p>
                </v-flex>

              </v-layout>
            </v-flex>

          </v-layout>
        </div>
        <!--新用户申请加入机构时  end-->


        <!--用户有机构申请加入群组时 start-->
        <div
          v-if="addgroup"
          class="tips">
          <v-layout
            column
            align-center>

            <v-flex
              style="width:80%; border-bottom:solid 1px #ddd"
              class="py-2 mb-1">
              <p
                class="pt-2 pb-1"
                style="text-align:center; font-size:16px!important;">
                您已申请加入<span
                  class="font-weight-light"
                  style="color:red">{{ groups_name }}</span>
              </p>
            </v-flex>
            <p
              class="pt-1 pb-4"
              style="text-align:center; font-size:15px!important;">
              请等待群主处理
            </p>

          </v-layout>

        </div>
        <!--用户有机构申请加入群组时 end-->

      </div>

      <!--
         这里存在着需要相应展示的模块
         end
     -->

      <component
        :is="c_view"
        :list="unfinished_list"
      />

      <v-layout
        v-if="c_view !== 'Loading'"
        style="padding-bottom:56px;">
        <v-flex
          v-if="finished_list"
          xs12>
          <v-subheader
            class="grey lighten-4">
            <span class="pl-2">已完成 ({{ finishedCount(finished_list) }}) </span>
          </v-subheader>
          <div
            class="btn-text pt-3 pb-3"
            @click="jumpToFinished()">
            <v-icon
              class="iconfont dudu-click grey--text text--lighten-1"
              style="vertical-align: middle"/>
            <span class="grey--text text--darken-1">查看详情</span>
          </div>
        </v-flex>
      </v-layout>


      <!--  按钮组 -->
      <div
        class="btn-count"
        style="position:fixed; bottom:100px; right:5%;">
        <!--<v-icon class="iconfont dudu-tianjia"></v-icon>-->
        <v-layout>
          <v-flex xs-11>
            <div :class="{ btnBoxShow: create_btn_flag }">
              <v-btn
                v-if="can_publish"
                fab
                small
                dark
                color="red"
                @click="showCreateForm(0)">
                <span class="font-weight-bold">任务</span>
              </v-btn>
              <v-btn
                v-if="can_publish"
                fab
                small
                dark
                color="red"
                @click="showCreateForm(1)">
                <span class="font-weight-bold">通知</span>
              </v-btn>

              <v-btn
                fab
                small
                dark
                color="red"
                @click="showCreateForm(2)">
                <span class="font-weight-bold">请示</span>
              </v-btn>
            </div>
          </v-flex>
          <v-flex xs-1>
            <v-btn
              :class="{ btnRotation: !create_btn_flag }"
              fab
              small
              dark
              color="red"
              @click="showCreateBtnBox()">
              <v-icon
                dark
                class="iconfont dudu-tianjia1"/>
            </v-btn>
          </v-flex>
        </v-layout>

      </div>
      <!--<div-->
      <!--class="btn-count"-->
      <!--style="position:fixed; bottom:150px; right:5%;">-->
      <!--&lt;!&ndash;统计按钮&ndash;&gt;-->
      <!--<v-btn-->
      <!--fab-->
      <!--small-->
      <!--color="red"-->
      <!--dark-->
      <!--class="sumwork"-->
      <!--@click="showStatistic()">-->
      <!--<span class="font-weight-bold">统计</span>-->
      <!--</v-btn>-->
      <!--</div>-->
    </div>


    <!--选择加入的群组-->
    <Dialogs
      v-model="checkout_dialogs"
      :title="title"
      :show.sync="checkout_dialogs"
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
            @click="addGroups"
          >申请加入</v-btn>
        </v-layout>
      </v-list>
    </Dialogs>

  </div>
</template>

<script>
import WorksList from "../components/Works/WorkLists";
import Loading from "../components/Commons/Loading";
import {mapState, mapMutations, mapActions} from "vuex";
import Dialogs from  "../components/Commons/Dialogs";
import GroupRoleIntroduction from "../components/Organizations/Popmodal/GroupRoleIntroduction";
// import the component
import Treeselect from "@riophae/vue-treeselect";
// import the styles
import "@riophae/vue-treeselect/dist/vue-treeselect.css";

export default {
	name: "Works",
	inject: ["reload"],
	components: {
		Loading,
		WorksList,
		Dialogs,
		Treeselect,
		GroupRoleIntroduction
	},
	data() {
		return {
			c_view: "Loading",
			work_type: "",
			work_type_index: "",
			o_list: "123",
			unfinished_list: "",
			finished_list: "",
			items: [
				{
					title: "任务",
					work_type: "task",
					finished_status_index: 4,
					type_text_array: [
						{
							index: "unsend",
							text: "未发送",
						},
						{
							index: "unsign",
							text: "未签收",
						},
						{
							index: "unpush",
							text: "未上报",
						},
						{
							index: "pushed",
							text: "已上报",
						},
						{
							index: "finished",
							text: "已办结",
						}
					]
				},
				{
					title: "通知",
					work_type: "notification",
					finished_status_index: 2,
					type_text_array: [
						{
							index: "unsend",
							text: "未发送",
						},
						{
							index: "unread",
							text: "未读",
						},
						{
							index: "unsign",
							text: "已读",
						},
					]
				},
				{
					title: "请示",
					work_type: "ask",
					finished_status_index: 1,
					type_text_array: [
						{
							index: "undeal",
							text: "未处理",
						},
						{
							index: "finished",
							text: "已批复",
						},
					]
				}
			],
			create_btn_flag: 1,
			selected_asks_type: "全部类型",
			asks_type: ["全部类型", "工作", "请假", "用车"],
			selected_finished_status_index: "",
			selected_type_text_array: "",
			has_org: false, // 是否有组织
			has_group: false, // 是否有工作组
			can_publish: false, // 是否可以发布工作或者通知
			dialog: false,
			countNum: 0,
			btnText: "获取",
			code_rules: [
				v => !!v || "请填写验证码"
			],
			sms_code: null,
			identity:"",
			tips:false,
			is_status:"",
			flag:0,
			org_names:"",
			addgroup:false,
			groups_name:"",
			checkout_dialogs:false,
			title:"",
			add_groups_id:null,
			groups_list:[],
			seleted_role:false,
			role_list:[],
			add_role_id:null,
			introduce:[],
			role_type: 2,
		};
	},
	computed: {
		...mapState(["selected_org","user_info","selected_org_user_info"])
	},
	watch:{
		add_groups_id(){
			this.getGroupRole();
		}
	},
	created() {
		this.work_type_index = parseInt(this.$route.params.type);
		this.changeWorkType(this.work_type_index);
	},
	updated() {
	},
	mounted (){
		this.isTips();
		this.getOrgGrous();
	},
	methods: {
		...mapActions(["initUser"]),
		...mapMutations(["setFinishedList"]),
		// 统计页面
		showStatistic(){
			this.$router.push({path: "/taskscount"});
		},
		// 跳转到创建群组页面
		createGroups(){
			this.$router.push({path:"/organizations/group_list"});
		},
		// 获取角色列表
		getGroupRole(){
			// 获取角色列表
			let groups_id = this.add_groups_id;
			this.axios.get(`/api/group/info/${groups_id}`).then((res)=>{
				let group = res.data.data;
				group.type == 1 ? this.role_type = 3 : this.role_type = 2;
				this.axios.get("/api/role/get?type="+this.role_type).then((res)=>{
					this.role_list = res.data.data;
					if( this.role_list[0]["name"] == "群组创建人")  this.role_list.shift();
					// 构造角色选项列表
					this.role_list.forEach((value, index) => {

						if(value.type == 3){
							this.defaults="日程共享人";
							// this.add_role_id =  this.role_list[0].id;
							this.add_role_id =  9;
							// 角色描述文案
							let desp = "";
							switch (value.name) {
							case "日程共享人":
								desp = "日程共享人";
								this.add_role_id =  value.id;
								break;
							case "日程查看人":
								desp = "日程查看人";
								break;
							default:
							}
							value.text = desp;
							value.value = value.id;
							return ;
						}
						this.defaults="签收人";
						// this.add_role_id =  this.role_list[0].id;
						this.add_role_id =  7;
						// 角色描述文案
						let desp = "";
						switch (value.name) {
						case "任务签收人":
							desp = "任务签收人";
							this.add_role_id =  value.id;
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
		// 加入群组
		addGroups (){
			let postData = {
				org_id:this.$store.state.selected_org.id,
				role_id : this.add_role_id
			};

			this.axios.post(`/api/group/addgroup/${this.add_groups_id}`,postData).then((res)=>{
				if (res.data.errcode === 0){
					this.initUser().then(() => {
						this.$toast("已向群主发送申请！请等待群主同意!","success");
						this.reload();
					});

				}else{
					this.$toast(res.data.errmsg,"error");
				}

			}).catch((err)=>{

			});
		},

		// 获取机构下面所有的群组
		getOrgGrous (){
			if(this.selected_org == null){
				document.title = "欢迎使用都督";
				return false;
			}

			this.axios.get(`/api/group/getallgroups/${this.selected_org.id}`).then((res) => {
				let data = res.data.data;
				for (let index in res.data.data) {
					var temp_data = {
						id: index,
						label: index,
						children: []
					};
					data[index].forEach((item,i) => {
						item.label = item.name;
						temp_data.children.push(item);
					});
					this.groups_list.push(temp_data);
				}

			}).catch((err)=>{

			});
		},
		// 加入群组弹窗
		addDialog (){
			this.checkout_dialogs = !this.checkout_dialogs;
			this.title = "请选择要加入的群组!";
		},
		// 提示判断函数
		isTips(){
			// 获取发送的消息
			this.axios.get("/api/message/getsend").then((res)=>{
				let data = res.data.data;

				// 当存在消息,但是已经存在机构的时候
				if (data != null && this.selected_org != null){
					this.isAddGroups();
				}

				// 当存在消息时 但是还没有机构的时候
				if(data != null &&  this.selected_org == null){
					let str =  data.content;
					// 截取字符串
					this.org_names = str.substring(str.indexOf("入")+1,str.lastIndexOf("，"));
					// todo 字符串切割优化
					let split_str = this.org_names.split("「").join("").split("」").join("").split("-");
					this.org_names = split_str[0] === split_str[1] ? split_str[0] : this.org_names;
					this.tips = true;
					this.is_status = data.status;
					return ;
				}


				// 没有机构也没有发送加入机构消息的时候
				if(this.selected_org == null && data == null){
					this.has_org = !this.has_org;
					return ;
				}


				// 已经加入了机构,但是还没有群组的时候!这个时候应该不存在消息
				if(this.selected_org_user_info.groups.length === 0 && this.selected_org !== null ){
					this.isAddGroups();
					return ;
				}

				// 已经存在群组的时候
				if(this.selected_org_user_info.groups.length > 0 ){
					this.isAddGroups();
					return ;
				}

			}).catch((err)=>{
			});
		},
		// 是否正在加入群组
		isAddGroups(){

			let  data = {addGroups:1};
			this.axios.get("/api/message/getsend",{ params: data }).then((res)=>{


				// 没有在加入群组但已经有群组的情况下
				if(res.data.data == null && this.selected_org_user_info.groups.length > 0){

					let check_groups = null;


					// 判断群组是否存在工作群组
					this.selected_org_user_info.groups.forEach((value, index) => {

						// 当存在工作群组的时候
						if(value.type === 0){
							// 判断用户是否有发布工作或者通知的权限
							if (value.pivot.role_id === 5 || value.pivot.role_id === 6) {
								this.can_publish = true;
							}
							check_groups = 1;
							this.has_group = true ;
							this.flag = true;
							return ;
						}
					});

					// 没有群组属于工作群组的时候
					if(check_groups == null) this.flag = 2;
					return ;
				}

				// 有消息正在加入群组的时候
				if(res.data.data != null){

					let datas = res.data.data;
					let str = datas.content;

					// 并且还没有存在群组的时候
					if (this.selected_org_user_info.groups.length === 0){
						this.addgroup = true;
						this.groups_name =   str.substring(str.indexOf("入")+1,str.lastIndexOf(","));
						return ;
					}


					// 存在消息,但是已经存在群组了!
					if(this.selected_org_user_info.groups.length > 0){
						let check_groups = null;
						// 判断群组是否存在工作群组
						this.selected_org_user_info.groups.forEach((value, index) => {
							// 当存在工作群组的时候
							if(value.type === 0){
								// 判断用户是否有发布工作或者通知的权限
								if (value.pivot.role_id === 5 || value.pivot.role_id === 6) {
									this.can_publish = true;
								}
								check_groups = 1;
								this.has_group = true ;
								this.flag = true;
								return ;
							}
						});


						// 不存在群组为工作的时候
						if (check_groups == null){
							this.addgroup = true;
							this.groups_name =  this.strMatch(str,"入",",")[0];
							return;
						}
					}
				}

				// 不存在申请加入工作群组消息,并且没有群组的时候
				if(res.data.data == null && this.selected_org_user_info.groups.length == 0){
					this.flag = 2;
					return ;
				}


			}).catch((err) => {

			});
		},

		changeWorkType(i) {
			// 若用户为加入机构，则不发请求
			// if (!this.has_org) {
			// 	return;
			// }
			// Show the Loading view
			this.c_view = "Loading";
			let timeFlag = new Date().getTime();
			let work_type = this.items[i].work_type;
			this.work_type = "";
			this.selected_finished_status_index = this.items[i].finished_status_index;
			this.selected_type_text_array = this.items[i].type_text_array;
			// change route
			this.$router.replace("/works/" + i);

			// Get Data
			this.axios.get("/api/" + work_type + "/show").then((res) => {
				this.o_list = res.data.data;
				const return_data = this.listsRank(
					this.o_list,
					this.work_type,
					this.selected_type_text_array,
					this.selected_finished_status_index,
					this.selected_asks_type
				);

				this.unfinished_list = return_data.unfinished_list;
				this.finished_list = return_data.finished_list;
				let vue = this;
				if (new Date().getTime() - timeFlag >= 600) {
					vue.c_view = "WorksList";
					vue.work_type = work_type;
				} else {

					setTimeout(function () {
						vue.c_view = "WorksList";
						vue.work_type = work_type;
					}, 600);

				}
			}).catch((err) => {
			});
		},
		finishedCount(finished_list) {
			let counts = 0;
			for (let j in finished_list) {
				counts += finished_list[j].list.length;
			}
			return counts;
		},
		jumpToFinished() {
			this.setFinishedList(this.finished_list);
			this.$router.push(`/finished_works/${this.$route.params.type}`);
		},
		showCreateBtnBox: function () {
			this.create_btn_flag = this.create_btn_flag === 1 ? 0 : 1;
		},
		// 请示类型选择
		changeAskType: function () {
			const return_data = this.listsRank(
				this.o_list,
				this.work_type,
				this.selected_type_text_array,
				this.selected_finished_status_index,
				this.selected_asks_type
			);
			this.unfinished_list = return_data.unfinished_list;
			this.finished_list = return_data.finished_list;
		},
		showCreateForm(type) {
			switch (type) {
			case 0:
				this.$router.push({path: "/create_task"});
				break;
			case 1:
				this.$router.push({path: "/create_notice"});
				break;
			case 2:
				this.$router.push({path: "/create_ask"});
				break;
			default:
				break;
			}
		},

	}
};
</script>

<style scoped>
  .round-img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
  }

  .tip-item {
    display: inline-block;
    width: 160px;
    line-height: 32px;
  }

  .text-right {
    text-align: right;
  }

  .btnBoxShow {
    display: none;
  }

  .btnRotation {
    transform: rotate(-45deg);
  }

  .btn-text {
    background: white;
    text-align: center;
  }

  .right-box{
    width:70%;
    border-radius: 50px;
    padding: 10px 20px;
    margin: 0 0 20px 0;
  }

  .c1{
    background: #f5f5f5;
  }
  p {
    margin-bottom: 0!important;
    /* font-size: 14px!important; */
    font-size: .9rem;
  }
  .left-box p {
    text-align: right;
    color: #333;
  }
  .right-box p {
    text-align: center;
    color: #333;
  }

  .btn {
    width: 70%;
  }

  .btn button {
    margin: 6px 0;
  }

  .dialogs-title{
    border-left:red 3px solid;
    border-top:#eee 1px dashed;
    border-bottom:#eee 1px dashed;
  }

  .tips{
    /*margin-top: 20%;*/
  }

  /*  媒体查询兼容  */
  @media screen and (max-width: 500px){

    .header-titile >>> div {
      padding:0;
    }

    .header-titile >>> .header-titile{
      padding: 2px 20px;
      margin: 0 0 18px 0;
    }

    .header-titile >>> .v-btn{
      font-size: 13px;
      height: 39px;
    }

  }
</style>
