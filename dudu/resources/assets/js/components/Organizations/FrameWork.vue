<template>
  <div>
    <div
      v-if="!isLoading && accessControl.permission"
    >
      <FrameHeader
        :org="org"/>
      <v-subheader
        center
        style="background:#f5f5f5;">
        本级部门
      </v-subheader>

      <v-card flat>
        <FrameItem
          v-for="(item,index) in depts"
          :key="index"
          :group="group"
          :name="item.name"
          :org_name="org.name"
          :number="item.num"
          :id="item.id"
          :org_type="orgType.dept"
          :depys_types="item.level"
          entry_type="dept"
        />
        <p
          v-if="depts.length === 0"
          class="tips mb-0 py-4 grey--text">暂无本级部门</p>

      </v-card>

      <v-subheader
        center
        style="background:#f5f5f5;">
        下级单位
      </v-subheader>
      <v-card flat>
        <FrameItem
          v-for="(item,index) in units"
          :key="index"
          :group="group"
          :name="item.name"
          :number="item.num"
          :id="item.id"
          :org_type="orgType.unit"
          :depys_types="item.level"
          entry_type="dept"
        />

        <p
          v-if="units.length === 0"
          class="tips mb-0 py-4 grey--text">暂无下级单位</p>
      </v-card>
      <v-subheader
        center
        style="background:#f5f5f5;">
        群组
      </v-subheader>
      <v-card
        flat
        class="pb-5">

        <FrameItem
          v-for="(item,index) in groups"
          :key="index"
          :group="isgroup"
          :name="item.name"
          :number="item.num"
          :id="item.id"
          :org_type="orgType.group"
          entry_type="group"
        />
        <p
          v-if="groups.length === 0"
          class="tips py-4 mb-0 grey--text">暂无群组</p>

      </v-card>
      <!-- <FrameBottomNav/> -->
      <v-flex style="position: fixed; bottom: 0; width: 100%;">
        <v-layout 
          row 
          style="background: white">
          <v-flex xs6>
            <v-btn
              ref="mask"
              block
              class="my-1"
              color="white"
              @click="deptdialog()">添加部门/单位
            </v-btn>
          </v-flex>
          <v-flex xs6>
            <v-btn
              ref="masks"
              block
              class="my-1"
              color="white"
              @click="jumpToBatchOperation">批量操作成员
            </v-btn>
          </v-flex>


        </v-layout>
      </v-flex>

      <v-bottom-sheet
        v-model="dept_dialog"
      >
        <v-card>
          <v-card-text>
            <v-layout
              column
              align-center>
              <v-radio-group
                v-model="dept_type"
                style="width: 100%"
                row>
                <v-radio
                  :value="0"
                  label="本级部门"/>
                <v-radio
                  :value="1"
                  label="下级单位"/>
              </v-radio-group>

              <!--输入框-->
              <v-text-field
                v-model="dept_name"
                style="width: 100%"
                label="部门名称"
                @blur="scrollTo"
              />


              <v-checkbox
                v-model="of_adddept"
                style="width: 100%"
                class="ma-0"
                label="是否同时加入此部门/单位"/>

              <v-flex>
                <v-btn
                  flat
                  color="blue"
                  @click="cancelDept()">取消
                </v-btn>
                <v-btn
                  :disabled="!dept_name"
                  flat
                  color="primary"
                  @click="createDept()">确定
                </v-btn>
              </v-flex>
            </v-layout>
          </v-card-text>
        </v-card>
      </v-bottom-sheet>
    </div>
    <div
      v-if="!isLoading && !accessControl.permission"
      class="empty">
      这里似乎什么也没有~
    </div>
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

    <!--跳过按钮-->
    <div
      v-if="guide_show && miss_btn "
      class="miss-btn"
      @click="skip"
    >
      <button>跳过</button>
    </div>

    <!--跳过按钮-->
    <div
      v-if="guide_show && know_btn "
      class="miss-btn"
      @click="know"
    >
      <button>知道了</button>
    </div>
  </div>
</template>


<script>
import FrameItem from "../Organizations/Popmodal/FrameItem";
import FrameHeader from "../Organizations/Popmodal/FrameHeader";
import MessageGuide from "../Messages/MessageGuide";


import Loading from "../Commons/Loading";
import {mapState, mapMutations, mapGetters, mapActions} from "vuex";
// import FrameBottomNav from "../Organizations/Popmodal/FrameBottomNav";

export default {
	inject: ["reload"],
	name: "FrameWork",
	components: {
		FrameHeader,
		FrameItem,
		Loading,
		MessageGuide
		// FrameBottomNav
	},
	data: function () {
		return {
			isLoading: true,
			cView: "Loading",
			org: {},
			orgType: {
				dept: 0,
				unit: 1,
				group: 2
			},
			group: false,
			isgroup: true,
			dept_dialog: false, // 显示创建部门
			dept_type: 0, // 创建部门类型
			dept_name: null, // 创建部门名称
			depts: [], // 本级部门
			units: [], // 下级单位
			groups: [], // 群组
			// 设置权限，根据orgid 进行设置
			accessControl: {
				arr: [1, 2, 3],
				permission: false
			},

			// 新手指引相关
			dom_ready: false,
			guide_show: false,
			text: "",
			css: [{}, {}, {}],
			clickfun: null,
			miss:null,
			miss_btn:true,
			know_btn:false,
			of_adddept: false
		};
	},
	computed: {
		...mapGetters(["guide_info"]),
		...mapState(["selected_org_user_info", "selected_org"]),
	},
	watch: {
		// 数据加载完成执行
		dom_ready() {
			this.initGuide();
		},
		miss(){
			this.initGuide(this.miss);
		}
	},
	mounted() {
		this.initData();
	},
	updated() {
		this.$nextTick(() => {
			this.dom_ready = true;
		});
	},
	methods: {
		...mapActions(["initUser"]),
		...mapMutations(["haveOrgAccess","setTempGuideInfo"]),
		initData(){
			this.haveOrgAccess(this.accessControl);
			this.org = this.$store.state.selected_org;
			this.getInfo();

		},
		// 下一步操作
		skip(){
			// this.miss = this.$store.state.guide_info;
			// this.miss_btn = false;
			let storage = window.localStorage;
			if (!storage.create_dept_finished) {
				storage.setItem("create_dept_finished", true);
			}
			this.setTempGuideInfo("invite_users");
			this.$router.push({path: "/organizations"});
		},
		know() {
			this.guide_show = false;
			this.know_btn = false;
			let storage = window.localStorage;
			if (!storage.create_dept_finished) {
				storage.setItem("create_dept_finished", true);
			}
			this.setTempGuideInfo("invite_users");
		},
		// 判断是否进行新手引导
		initGuide(txt) {
			let el = null;
			let arrow = null;
			let guide_info = txt != null ? txt : this.guide_info;
			switch (guide_info) {
			case "create_dept":
				document.title = "创建部门";
				// 引导文案
				this.text = "创建您所在的部门，或点击“跳过”开始邀请同事加入机构";
				// 点击事件
				this.clickfun = this.deptdialog;
				// 定制蒙版样式
				el = this.$refs.mask.$el.getBoundingClientRect();
				arrow = {
					top: el.top - (el.height+200) + "px",
					left: el.left + (el.width/2) + "px",
					transform: "rotate(90deg)",
				};
				this.css = this.getGuideCss(el, arrow);
				// 开启蒙版
				this.guide_show = true;
				this.miss_btn = true;
				this.miss = 1;
				break;
			case "create_dept_continue":
				document.title = "创建部门";
				// 引导文案
				this.text = "创建成功，您可继续创建部门，或点击后退开始邀请同事加入";
				// 点击事件
				this.clickfun = this.deptdialog;
				// 定制蒙版样式
				el = this.$refs.mask.$el.getBoundingClientRect();
				arrow = {
					top: el.top - (el.height+200) + "px",
					left: el.left + (el.width/2) + "px",
					transform: "rotate(90deg)",
				};
				this.css = this.getGuideCss(el, arrow);
				// 开启蒙版
				this.guide_show = true;
				this.miss_btn = false;
				this.know_btn = true;
				this.miss = 1;
				break;
			case "operation_members":
				// 引导文案
				// this.text = "接下来，请点击批量操作成员，将自己添加进部门";
				// 点击事件
				// this.clickfun = this.jumpToBatchOperation;
				// 定制蒙版样式
				// el = this.$refs.masks.$el.getBoundingClientRect();
				// arrow = {
				// 	top: el.top - (el.height+200) + "px",
				// 	left: el.left + (el.left/2) + "px",
				// 	transform: "rotate(90deg)",
				// };
				// this.css = this.getGuideCss(el, arrow);
				// 开启蒙版
				// this.guide_show = true;
				// break;
			}

		},

		//创建部门
		createDept() {

			this.miss_btn = false;
			this.axios.post("/api/dept/store", {
				org_id:   this.org.id,
				name:     this.dept_name,
				level:    this.dept_type,
				adddept:  this.of_adddept
			}).then((res) => {

				if (res.data.errcode == 0) {
					// 兼容ios12 输入框导致页面上浮问题
					window.scrollBy(0, 0);

					this.$toast("创建部门成功！", "success");
					this.dept_dialog = false;
					this.dept_name = "";

					let storage = window.localStorage;
					// 如果第一次创建部门则做好标记
					if (!storage.create_dept_once) {
						storage.setItem("create_dept_once", true);
					}else{
						// 如果非第一次创建部门则标记创建跳转完成
						if (!storage.create_dept_finished) {
							storage.setItem("create_dept_finished", true);
						}
					}

					this.initUser().then((res)=>{
						this.reload();
					});
				}
				// console.log(this)
			}).catch((Err) => {

			});
		},


		deptdialog() {
			this.dept_dialog = true;
		},

		cancelDept() {
			// 兼容ios12 输入框导致页面上浮问题
			window.scrollBy(0, 0);
			this.dept_dialog = false;
			this.dept_name = "";
		},

		getInfo() {
			// this.axios.get(`/api/dept/org/${this.org.id}`).then((res) => {
			this.selected_org.depts.forEach((value, index) => {
				value.num = value.users.length;
				if (value.level == 0) {
					this.depts.push(value);
				} else if (value.level == 1) {
					this.units.push(value);
				}
			});
			// 如果无部门
			if(this.depts.length === 1 && this.units.length === 0){
				this.of_adddept = true;
			}

			// this.axios.get(`/api/group/org/${this.org.id}`).then((res) => {
			this.selected_org.groups.forEach((value, index) => {
				value.num = value.users.length;
			});
			this.groups = this.selected_org.groups;
			this.isLoading = false;
			// });

			if (this.depts.length  > 1){
				this.miss_btn = false;
			}
			// });
		},

		jumpToBatchOperation(test) {
			this.$router.push({path: "/batch_operation", query: {"in": test}});
			this.initUser().then(() => {
				this.setTempGuideInfo(null);
			});
		}
	}
};
</script>

<style scoped>
  .tips {
    width: 100%;
    text-align: center;
  }
  /*跳过按钮*/
  .miss-btn{
    position: fixed;
    z-index: 102;
    width: 100px;
    color: #ffffff;
    height: 38px;
    text-align: center;
    line-height: 24px;
    border: 1px solid;
    padding: 5px 15px 5px 15px;
    left: 0;
    right: 0;
    bottom: 12rem;
    margin: auto;
  }
</style>