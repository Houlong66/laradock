<template>
  <div>
    <div v-if="!isLoading">
      <v-container
        v-if="task_id"
        class="pb-0 audit-box">
        <v-flex>
          <v-layout row>
            <label class="font-weight-bold">审批人：</label>
            <p>{{ audit.user }}</p>
          </v-layout>
        </v-flex>
        <v-flex>
          <v-layout row>
            <label class="font-weight-bold">审批结果：</label>
            <p>{{ audit.result }}</p>
          </v-layout>
        </v-flex>

        <v-flex>
          <v-layout row>
            <label class="font-weight-bold">批复内容：</label>
            <p>{{ audit.text }}</p>
          </v-layout>
        </v-flex>

        <v-flex>
          <v-layout row>
            <v-btn
              outline
              class="ma-0 mb-2 btn_task"
              color="white"
              style="background: none !important;"
              @click="checkDetails"
            >审批详情</v-btn>
          </v-layout>
        </v-flex>

      </v-container>


      <v-form
        ref="form"
        v-model="valid"
        class="create-form"
        lazy-validation>

        <v-select
          v-if="organization_items && organization_items.length !== 1"
          v-model="organization"
          :items="organization_items"
          :label="organization_items.length === 1? '机构' : '选择机构' "
          background-color="white"
          box
          @change="chooseOrganization()"
        />


        <v-text-field
          v-model="title"
          :rules="title_rules"
          :counter="20"
          background-color="white"
          box
          label="任务标题"
          required
          @blur="scrollTo"
        />

        <v-textarea
          v-model="reports_text"
          name="input-7-1"
          box
          label="任务内容（选填）"
          rows="3"
          value=""
          background-color="white"
          @blur="scrollTo"
        />


        <v-text-field
          v-model="dead_line"
          box
          background-color="white"
          label="完成时限（选填）"
          readonly
          @blur="scrollTo"
          @click="menu=true"
        />




        <!--逾期提醒-->
        <!--<div class="report-setting-box px-2 pt-2 pb-2">-->
        <!--<v-layout>-->
        <!--<v-flex-->
        <!--xs12-->
        <!--class="align-self-center">-->
        <!--<v-switch-->
        <!--v-model="overdue"-->
        <!--label="逾期提醒设置"-->
        <!--color="primary"-->
        <!--value="1"-->
        <!--hide-details-->
        <!--height="36"-->
        <!--class="mt-0"-->
        <!--/>-->
        <!--</v-flex>-->
        <!--</v-layout>-->

        <!--<v-layout-->
        <!--v-if="select_ovtime"-->
        <!--&gt;-->
        <!--<v-flex-->
        <!--xs12-->
        <!--class="align-self-center"-->
        <!--&gt;-->
        <!--<v-combobox-->
        <!--v-model="overdue_time"-->
        <!--:items="overdue_timelist"-->
        <!--label="选择提前多少天进行提醒"-->
        <!--multiple-->
        <!--chips-->
        <!--/>-->
        <!--</v-flex>-->

        <!--</v-layout>-->
        <!--</div>-->


        <!--时间弹窗-->
        <v-dialog
          ref="menu"
          v-model="menu"
          persistent
          lazy
          full-width
          width="290px"
        >
          <!--年月份-->
          <v-date-picker
            v-if="show_date"
            v-model="dead_line"
            :allowed-dates="allowedDates"
            no-title
            locale="zh-cn"
            scrollable>
            <v-spacer/>
            <v-btn
              flat
              color="primary"
              @click="menu = false">Cancel
            </v-btn>
            <v-btn
              flat
              color="primary"
              @click="showTime()">OK
            </v-btn>
          </v-date-picker>

          <!--时分-->
          <v-time-picker
            v-if="show_time"
            v-model="time"
            :allowed-hours="dead_line==date ? allowedHours : allowedHours_next"
            :allowed-minutes="allowedMinutes_next"
            format="24hr"
            @change="saveDeadLine()"
          />

        </v-dialog>
        <v-spacer/>

        <v-select
          v-if="false"
          v-model="cycle"
          :items="cycle_items"
          box
          background-color="white"
          label="周期设置"
          disabled
        />

        <v-text-field
          v-model="send_target_chosen"
          :rules="[v => (v && v != '请选择发送对象') || '请选择发送对象']"
          label="发送对象"
          box
          background-color="white"
          readonly
          required
          @click="showSendTarget"
        />

        <v-dialog
          v-model="dialog"
          :fullscreen="true"
          scrollable
          transition="dialog-bottom-transition"
          max-width="100%">
          <v-card>
            <v-toolbar
              dark
              color="red">
              <v-btn
                icon
                dark
                @click.native="dialog = false">
                <v-icon
                  small
                  class="iconfont dudu-guanbi1"/>
              </v-btn>
              <v-toolbar-title class="subheading">选择发送对象</v-toolbar-title>
              <v-spacer/>
              <!--<v-toolbar-items>-->
              <!--<v-btn-->
              <!--dark-->
              <!--flat-->
              <!--class="font-weight-bold"-->
              <!--@click.native="finishChooseTargets()">确定-->
              <!--</v-btn>-->
              <!--</v-toolbar-items>-->
            </v-toolbar>
            <v-card-text style="height: 100vh;">


              <v-select
                v-if="show_workgroup"
                v-model="workgroup"
                :items="workgroup_items"
                :label="workgroup_items.length===1?'所在工作群组':'选择工作组'"
                :disabled="workgroup_items.length===1"
                @change="chooseWorkgroup()"
              />

              <v-radio-group
                v-if="show_target_type"
                v-model="target_type"
                row
                class="mt-0 mb-2"
                hide-details
                @click="changeTargetType()"
              >
                <v-radio
                  v-if="depts_obj"
                  label="部门/单位"
                  value="1"/>
                <v-radio
                  label="个人"
                  value="0"/>
              </v-radio-group>


              <v-container
                v-if="show_target_type && target_type==1"
                class="pa-0"
                fluid>
                <treeselect
                  v-model="targets"
                  :multiple="true"
                  :searchable="false"
                  :options="targets_items_groups"
                  :max-height="200"
                  :always-open="true"
                  :default-expand-level="0"
                  :z-index="0"
                  no-options-text="机构中暂无可选部门或单位"
                  open-direction="below"
                  value-consists-of="LEAF_PRIORITY"/>
              </v-container>
              <v-container
                v-if="show_target_type && target_type==0"
                class="pa-0">
                <treeselect
                  v-model="targets"
                  :multiple="true"
                  :searchable="false"
                  :options="targets_items_person"
                  :max-height="200"
                  :always-open="true"
                  :default-expand-level="0"
                  :z-index="0"
                  style="z-index:0!important"
                  no-options-text="机构中暂无可选成员"
                  open-direction="below"
                  value-consists-of="LEAF_PRIORITY"/>
              </v-container>

              <div
                v-if="show_target_type"
                class="body-1 work-text grey--text text--darken-2 pa-2 mt-3">
                <p
                  v-if="target_type==0"
                  class="ma-0">任务将发送给选中的群组成员</p>
                <p
                  v-if="target_type==1"
                  class="ma-0">任务将发送给选中部门或单位的群组成员</p>
              </div>
            </v-card-text>
            <v-divider/>
          </v-card>

          <!--按钮-->
          <v-layout
            style="background:#fff; width:100%; position:fixed; bottom:0; left:0;"
            justin-center>
            <v-flex>
              <v-btn
                class="mb-1"
                block
                @click.native="finishChooseTargets()">确定
              </v-btn>
            </v-flex>
          </v-layout>
        </v-dialog>

        <!--  是否展开其他设置项 -->
        <div class="report-setting-box px-2 pt-2 pb-2 mb-3">
          <v-layout>
            <v-flex
              xs6
              class="align-self-center">
              <v-switch
                v-model="is_other"
                label="其他设置"
                value="1"
                hide-details
                height="36"
                class="mt-0"
              />
            </v-flex>
          </v-layout>
        </div>

        <!--其他设置  start -->
        <div
          v-if="is_other"
        >
          <div class="report-setting-box px-2 pt-2 pb-2">
            <v-layout>
              <v-flex
                xs6
                class="align-self-center">
                <v-switch
                  v-model="is_upload"
                  label="上传附件"
                  value="1"
                  hide-details
                  height="36"
                  class="mt-0"
                />
              </v-flex>
              <v-flex
                v-if="is_upload == 1"
                xs6
                class="align-self-center">
                <span>单个文件建议小于5M</span>
              </v-flex>
            </v-layout>

            <files-uploader
              v-if="is_upload == 1"
              :empty-file-list.sync="emptyFileList"
              :file-ready.sync="fileReady"
              :uploaded-files.sync="uploadedFiles"
              :upload-query="uploadQuery"
            />

          </div>

          <div
            v-if="task_id && is_upload==1 && (attach_file.length !== 0 || attach_pic.length !== 0)"
            class="mt-3">
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-fujian"/>
              已上传附件
            </label>

            <files-downloader
              :pic.sync="attach_pic"
              :file.sync="attach_file"
              :downloader-type="1"
              :work="task"
              :work-item-id="0"
              :if-can-delete="task_status === 3"
              work-type="task"
            />

          </div>

          <!--上传url地址-->
          <div class="report-setting-box px-2 pt-2 pb-2 mt-3">
            <v-layout>
              <v-flex
                xs12
                class="align-self-center">
                <v-switch
                  v-model="is_uploadUrl"
                  label="附加外部网址"
                  color="red"
                  value="1"
                  hide-details
                  height="36"
                  class="mt-0"
                />
              </v-flex>
            </v-layout>
          </div>


          <div v-if="is_uploadUrl == 1">
            <UploadUrl
              :url_id_list.sync="urlIdList"
              :works_id="task_id"
              works_type="1"
            />
          </div>

          <div class="report-setting-box px-2 pt-2 pb-2 mb-3 mt-3">
            <v-flex
              class="align-self-center">
              <v-switch
                v-model="if_report_need_attachment"
                label="上报任务时必须上传附件"
                color="red"
                value="1"
                hide-details
                height="36"
                class="mt-0"
              />
            </v-flex>
          </div>


          <v-select
            v-model="significance"
            :items="levels"
            box
            background-color="white"
            label="重要级别"
            hide-details
          />

        </div>

        <!--其他设置  end -->





        <div
          v-if="false"
          class="report-setting-box px-2 pt-3 pb-3">
          <v-layout>
            <v-flex
              xs5
              class="align-self-center">
              <v-switch
                v-model="auto_table"
                label="自动表格"
                color="red"
                value="1"
                hide-details
                height="36"
                class="mt-0"
              />
            </v-flex>
            <v-flex
              xs7
              class="align-self-center">
              <v-btn
                v-if="auto_table==1"
                height="36"
                style="width:90%"
                class="mx-0 mt-0 mb-0">未设置
              </v-btn>
            </v-flex>
          </v-layout>
        </div>

        <v-layout
          class="pt-3"
          justify-space-between>
          <v-flex xs6>

            <v-btn
              v-if="!task_id"
              class="submit-btn mx-0"
              @click.stop="sumbitDialogs(0)"
            >
              发送
            </v-btn>

          </v-flex>
          <v-flex
            xs6
            style="text-align: right">
            <v-btn
              class="submit-btn mx-0"
              @click="sumbitDialogs(1)"
            >
              请示
            </v-btn>
          </v-flex>
        </v-layout>
      </v-form>
    </div>

    <!--提示框-->
    <Dialogs
      :dialog.sync="tips_show"
      :title="tips_titile"
      :text="tips_text"
      :fn="tips_fn"
      :agreed="tips_agreed"
      :types="tips_type"
    />

  </div>
</template>

<script>
// import the component
import Treeselect from "@riophae/vue-treeselect";
// import the styles
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import {mapState, mapMutations} from "vuex";
import Loading from "../../Commons/Loading";
import FilesUploader from "../FilesUploader";
import FilesDownloader from "../FilesDownloader";
import UploadUrl from "../UploadUrl";

import Dialogs from "../../Commons/Dialogs";

// import func from './vue-temp/vue-editor-bridge';

export default {
	components: {
		Treeselect,
		FilesUploader,
		FilesDownloader,
		Loading,
		UploadUrl,
		Dialogs
	},
	data: () => ({
		// 提示框参数
		tips_show:false,
		tips_titile:"",
		tips_text:"",
		tips_fn:null,
		tips_type:null,
		tips_agreed:null,
		// -------------
		cView: "Loading",
		isLoading: true,
		valid: false,
		task: null, // 任务对象
		task_id: null, // 审批失败时的任务
		task_status: 0, // 任务项状态
		audit: {}, // 审批人
		title: "", // 标题
		title_rules: [
			v => !!v || "请填写任务标题",
			v => (v && v.length <= 20) || "任务标题必须不超过二十个字"
		],
		body: "", // 内容
		body_rules: [
			v => !!v || "请填写任务内容"
		],
		significance: 0, // 重要级别
		levels: [
			{
				text: "重要级别（普通）",
				value: 0
			},
			{
				text: "重要级别（重要）",
				value: 1
			},
			{
				text: "重要级别（非常重要）",
				value: 2
			}
		],
		overdue:false,
		// 工作类型的>>>>>>>>>>>>>
		cycle_items: [
			{
				text: "每日"
			},
			{
				text: "每周"
			},
			{
				text: "每月"
			},
			{
				text: "每季"
			},
			{
				text: "每年"
			},
		],
		dead_line: null, // 选择当前的日期
		date: null, // 获取当前的日期
		times: null, // 获取当前的时间
		hours: null, // 获取当前时间的小时数
		file_text: "未选择任何文件",
		cycle: null,
		time: null,
		works_file: "未选择任何文件",
		// >>>>>>>>>>>>>>>>>>>>>>>>>

		// 显示判断值
		menu: false,
		dialog: false,
		show_workgroup: false,
		show_time: false,
		show_date: true,
		show_target_type: false,
		show_choose_target: false,

		// 发送对象
		send_target: false,
		send_target_chosen: "请选择发送对象",
		organization: null,
		organization_items: [],
		workgroup: null,
		workgroup_items: [],
		target_type: "1",
		targets: [],
		targets_old: [],

		// 初始发送对象
		init_targets: false,
		targets_items_person: [],
		targets_items_groups: [],
		if_report_need_attachment: "0", // 默认不必须上传附件

		// 文件上传相关
		// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
		is_upload: "0",
		uploadedFiles: [],
		emptyFileList: true,
		fileReady: false,
		uploadQuery: {works_type: 1, works_item_id: 0}, // works_type工作的类型，1任务，2通知，3附件;  type,0任务附件，1任务上报附件

		// 上传网址相关
		// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
		is_uploadUrl: "0",
		urlIdList: [],

		// 文件下载相关
		// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
		attach_pic: [], // 流转审批不通过时，获取之前上传的附件
		attach_file: [], // 流转审批不通过时，获取之前上传的附件

		// 自动表格相关
		// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
		auto_table: "0",
		submit_body:"",

		// 时间
		Date_time:null,
		overdue_timelist:["提前1天","提前12小时","提前6小时","提前3小时","提前1小时"],
		overdue_time:null,
		select_ovtime:false,
		selected_org_name: null,
		depts_obj: true,

		// 是否需要其他设置
		is_other:false,
		attachments:0
	}),

	computed: {
		...mapState(["user_info","selected_org"]),
		reports_text: {
			get () {
				let reg = new RegExp("<br/>","g");
				if (this.body){
					return this.body.replace(reg, "\n");
				}
			},
			set (newValue) {
				this.body = newValue;
			}
		}
	},
	watch: {
		reports_text(n, o) {
			if(this.body){
				let reg = new RegExp("\n","g");
				let copy_report_text = this.body;
				this.submit_body = copy_report_text.replace(reg, "<br/>");
			}
		},
		overdue(n,o){
			this.select_ovtime = !this.select_ovtime;
		},
		menu: function (newV, oldV) {
			if (newV) {
				if (this.dead_line) {
					if (this.show_time) {
						var date = this.dead_line.split(" ")[0];
						this.dead_line = date;
					} else {
						this.dead_line = null;
					}
				}
			}
		},
		targets: {
			handler (newV, oldV) {
				if (newV.length == 0) {
					this.send_target_chosen = "请选择发送对象";
				} else {
					this.send_target_chosen = "已选择";
				}
			}
		},

	},

	mounted: function () {
		this.task_id = this.$route.query.task_id;
		// this.dead_line = this.formatDate(new Date(), "yyyy-MM-dd hh:mm");
		if (this.task_id) {
			this.init_targets = true;
			this.initData();
			this.initUrl();
		} else {
			// 获取任务可发送机构
			this.initSendTarget();
			this.isLoading = false;
		}

		this.date = this.formatDate(new Date(), "yyyy-MM-dd hh:mm").split(" ")[0]; // 在加载时获取到当前的日期
		this.times = this.formatDate(new Date(), "yyyy-MM-dd hh:mm").split(" ")[1]; // 加载当前的时间
	},

	methods: {
		...mapMutations(["getControlledOrgs", "getControlledGroupsByOrg"]),
		sumbitDialogs(type){

			if (this.title.trim().length === 0){
				this.$toast("请输入任务标题", "error");
				return;
			}

			// 判断是否有待上传的附件;
			let attachment = 0;
			if (this.is_upload === "1") {

				// 判断附件是否全部上传成功
				if (this.fileReady) {
					attachment = this.getAttachmentIdStr(this.uploadedFiles);
				} else {
					// 有未完成上传的附件
					this.$toast("请先上传附件", "error");
					return;
				}

				attachment = this.plusAttachmentIdStr(attachment, this.attach_pic, this.attach_file);
			}

			this.attachments = attachment;

			// 判断url是否有无上传
			if (this.is_uploadUrl === "0" || this.is_uploadUrl == null) {
				if (this.urlIdList.length > "0"){
					let data = {
						urlIdList: this.urlIdList,
						clear: "1",
					};

					this.axios.post("/api/task/deleteUrl",data).then((res) => {
					});
				}
			}

			if(this.targets.length < 1){
				this.$toast("请先选择发送对象", "error");
				return ;
			}

			this.tips_show = true;

			this.tips_fn = this.submit;

			this.tips_type = type;

			if (type === 0){
				this.tips_text = "确认发送后，所选发送对象将收到此任务。确认发送请点“确认发送”，如需修改请点“取消”。";
				this.tips_titile = "发送";
				this.tips_agreed = "确认发送";
				return ;
			}

			this.tips_text = "将此任务请示领导审批同意后才自动发送，继续请示请点“确认请示”，如需修改请点“取消”。";
			this.tips_titile = "请示";
			this.tips_agreed = "确认请示";

		},
		// 查看流转审批详情
		checkDetails(){
			this.$router.push({
				path:"/allrecord",
				query: {
					type:1,
					sid:this.task_id
				}
			});
		},
		// 新增权限管理，不从接口获取数据
		initSendTarget() {
			// 审核不通过任务，初始化原有发送对象时要loading
			// if (this.init_targets) {
			// this.isLoading = true;  // 流转审批时会报错 todo 研究原因
			// }

			// 默认选中当前的机构,在多机构情况下
			this.selected_org_name  = this.selected_org.name;

			let obj = {
				// 5 => 群组发放人 6 => 任务创建人
				arr: [5, 6],
				res: this.organization_items
			};


			// 直接从VueX 中获取当前用户可以发送通知的机构
			this.getControlledOrgs(obj);
			this.organization_items.forEach(function (value, index) {
				value.text = value.name;
				value.value = value.id;
			});


			if (!this.task_id && this.targets.length == 0) {
				this.organization = this.selected_org.id;
				this.chooseOrganization();
				return;
			}


			// 审核不通过情况，直接获取原有发送对象的机构
			if (this.init_targets) {
				this.chooseOrganization();
				return;
			}
		},

		// 任务发布或流转审批
		submit(if_audit) {
			if (this.$refs.form.validate()) {

				// 是否已经输入了任务内容
				// if(this.reports_text.trim().length === 0){
				// 	this.$toast("请输入任务内容", "error");
				// 	return;
				// }
				// 判断是否开启了选择逾期提醒,选择了就必须传入
				// if(this.overdue && !this.overdue_time){
				//       this.$toast("请选择逾期提醒时间,如果不需要,请先关闭", "error");
				//       return;
				// }
				//
				// // 开启了逾期提醒,必须设置完成时限
				// if(!this.dead_line && this.overdue){
				// 	this.$toast("请选择完成时限,如果不需要,请先关闭逾期提醒", "error");
				// 	return;
				// }


				// 构建发送对象字符串
				let targets = this.deepClone(this.targets);

				if (this.target_type === "0" && !this.init_targets) {
					// 处理发送对象
					targets.forEach((value, index) => {
						targets[index] = value.split("-")[1];
					});
					targets = [...new Set(targets)];
					// 解决vue-treeselect 报错问题，targets 必须要是数组
					// this.targets = this.targets.join(",");
				}


				let targetsWithComma = targets.join(",");

				// 检查是否存在危险行为
				let  strContent = "";
				if(this.reports_text){
					if(this.isValidate(this.reports_text,"check_html")){
						this.$toast("请重新输入任务内容","error");
						return ;
					}
					// 对内容进行转义
					let reg = new RegExp("\n","g");
					strContent = this.reports_text.replace(reg,"<br/>");
				}

				// 提取出时间
				// let timelist = this.overdue_time;
				// let reminds = [];
				//
				// 一天的时间等与  60 * 60 * 24
				// 开启了逾期提醒再设置这些
				// if(this.overdue){
				// 	// 兼容IOS 转换成/
				// 	let resetat =  Date.parse(this.dead_line.replace(/-/g,"/"))/1000;   //拿到时间错
				//
				// 	timelist.forEach((v,i)=>{
				//            // 小时转换
				// 		switch (v) {
				// 		case "提前1天" :
				//                 reminds.push(this.resetDate(resetat - (60*60*24)));
				//                 break;
				//
				// 		case "提前12小时" :
				//
				//                 reminds.push(this.resetDate(resetat - ( 60*60*12)));
				//
				//                 break;
				// 		case "提前6小时" :
				//                 reminds.push(this.resetDate(resetat - ( 60*60*6)));
				//
				//                 break;
				// 		case "提前3小时":
				//                 reminds.push(this.resetDate(resetat - ( 60*60*3)));
				//
				//                 break;
				// 		case "提前1小时":
				//                 reminds.push(this.resetDate(resetat - ( 60*60)));
				// 			break;
				// 		}
				//
				// 	});
				// }

				// 构建提交数据
				let temp_data = {
					org_id: this.organization,
					group_id: this.workgroup,
					if_dept: this.target_type,
					title: this.title,
					// reminds:reminds,
					desc: strContent.trim(),
					// reminds_time:,
					significance: this.significance,
					deadline: this.dead_line,
					attachment: this.attachments,
					if_report_need_attachment: !this.if_report_need_attachment ? "0" : this.if_report_need_attachment,
					if_report_need_autotable: this.auto_table,
					if_audit: if_audit,
					send_to_objs: targetsWithComma
				};

				// 判断是新建还是重新流转审批
				if (this.task_id) {
					temp_data.task_id = this.task_id;
				} else {
					temp_data.task_id = 0;
				}

				// 发送请求
				return this.axios.post("/api/task/store", temp_data).then((res) => {
					// console.log(res);
					this.updateWorksId(res.data.data.id);
					if (res.data.errcode === 0) {
						this.$toast(res.data.data.text, "success");
						if (if_audit === 0) {
							this.$router.push("/works/0");
						} else if (if_audit === 1) {
							this.$router.push({
								path: "/audit_task",
								query: {org_id: this.organization, id: res.data.data.id, work_type: 0}
							});
						}
					} else {
						this.$toast(res.data.errmsg, "error");
					}
				}).catch((Err) => {

				});
			}
		},

		updateWorksId(works_id){
			let data = {
				works_id: works_id,
				id: this.urlIdList
			};
			this.axios.post("/api/task/updateTaskId", data).then((res) =>{

			});
		},

		initUrl() {
			let data = {
				works_id: this.task_id,
				works_type: "1",
			};
			this.axios.post("/api/task/lookupUrl", data).then((res) => {
				if(res.data.data.length>0){
					this.is_uploadUrl = "1";
				}
			});
		},

		allowedDates: val => val >= new Date(+new Date()+8*3600*1000).toISOString().replace(/T/g," ").replace(/\.[\d]{3}Z/,"").substr(0, 10), // 获取选择的日期
		allowedHours: time => time >= new Date().getHours(), // 获取当前的小时
		allowedHours_next: h_time => h_time >= 0, // 所有的小时
		allowedMinutes: time => time >= new Date().getMinutes(), // 获取当前的分钟
		allowedMinutes_next: m_time => m_time >= 0, // 所有的分钟

		showTime() {
			if (!this.dead_line) {
				this.$toast("请选择日期", "warning");
				return;
			}
			this.show_date = false;
			this.show_time = true;
			// window.console.log(this.dead_line);
		},
		// 时分选择
		saveDeadLine() {
			this.show_date = true;
			this.show_time = false;
			this.hours = this.time.split(":")[0]; // 选择时间的小时
			let  now_times = this.formatDate(new Date(), "yyyy-MM-dd hh:mm").split(" ")[1]; // 加载当前的时间

			// 选择的月天跟当前一样时
			if (this.dead_line == this.date) {
				// 判断当前选择的时间是否小于当前时间
				if (this.time < this.times) {
					this.dead_line += ` ${now_times}`;
				}else{
					this.dead_line += ` ${this.time}`;
				}
			} else {
				this.dead_line += ` ${this.time}`;
			}

			this.$refs.menu.save(this.dead_line);
		},
		showSendTarget() {
			this.dialog = true;
		},


		// 同新增权限管理，直接从VueX 中获取数据
		chooseOrganization() {

			// 获取选中机构的名称
			this.selected_org_name = this.organization_items.find(item =>  item.id === this.organization)["name"];

			// 把id 和res 组合到一起，因为mutataions 只能传一个参数
			let obj = {
				id: this.organization,
				// 5 => 群组发放人 6 => 任务创建人
				arr: [5, 6],
				res: this.workgroup_items
			};

			this.getControlledGroupsByOrg(obj);

			this.workgroup_items.forEach(function (value, index) {
				value.text = value.name;
				value.value = value.id;
			});

			// 保留原有发送对象的处理逻辑
			if (!this.init_targets) {
				this.workgroup = null;
				this.show_choose_target = false;
				this.show_target_type = false;
				this.targets_items_person = [];
				this.targets_items_groups = [];
				this.targets = [];
			}
			this.show_workgroup = true;

			// 如果是审核不通过，则直接显示发送对象
			if (this.init_targets) {
				this.chooseWorkgroup();
				return;
			}

			this.workgroup = this.workgroup_items[0].value;
			this.chooseWorkgroup();
		},


		chooseWorkgroup() {
			this.targets = [];
			this.show_choose_target = true;
			this.show_target_type = true;
			this.targets_items_person = [];
			this.targets_items_groups = [];
			if (!this.workgroup) {
				return;
			}
			this.axios.get(`/api/dept/group/${this.workgroup}`).then((res) => {
				var temp = {
					id: "本级部门",
					label: "本级部门",
					children: []
				};
				this.targets_items_groups.push(temp);
				temp = {
					id: "下级单位",
					label: "下级单位",
					children: []
				};
				this.targets_items_groups.push(temp);
				// 防止用户重复插入
				let temp_arr = [];
				// 无部门的所有用户id
				let no_depts_arr = [];
				// 要从无部门分类剔除的用户id
				let delete_from_no_depts_arr = [];
				// 无部门对应的索引
				let no_depts_index = null;

				// 只有机构本部的机构，不展示部门/单位选项
				this.depts_obj = res.data.data.length === 1 ? false : true;
				// 默认只有个人任务
				if(!this.depts_obj) this.target_type = "0";

				res.data.data.forEach((value, index) => {
					// 构建部门对象数组
					for (let i = value.in_group_users.length - 1; i >= 0; i--) {
						// 机构本部改名为无部门 todo 机构本部
						if(value.name === this.selected_org_name) {
							no_depts_index = index;
							no_depts_arr.push(value.in_group_users[i].id);
						}
						if (value.in_group_users[i].id == this.$store.state.user_info.id) {
							value.in_group_users.splice(i, 1);
						}
					}

					var temp_dept = {
						id: value.name,
						label: value.name,
						children: []
					};
					this.targets_items_person.push(temp_dept);
					if (value.in_group_users.length == 0) return;
					var temp_child = {
						id: value.id,
						label: value.name
					};
					// level作数组下标
					// 忽略机构本部
					if(value.name !== this.selected_org_name) {
						this.targets_items_groups[value.level].children.push(temp_child);
						if (this.target_type == 1 && this.init_targets) {
							// 判断当前部门是否为原有发送对象
							if (this.targets_old.indexOf(value.id) !== -1) {
								this.targets.push(value.id);
							}
						}
					}

					// 构建个人对象数组
					value.in_group_users.forEach((data, i) => {
						// if (data.id == this.$store.state.user_info.id) {
						// 	return;
						// }
						temp_child = {
							id: `${value.name}-${data.id}`,
							label: data.name
						};

						// 用户id不重复数组
						if(temp_arr.indexOf(data.id) === -1){
							this.targets_items_person[index].children.push(temp_child);

							if (this.target_type == 0 && this.init_targets) {
								// 判断当前个人是否为原有发送对象
								if (this.targets_old.indexOf(data.id) !== -1) {
									this.targets.push(`${value.name}-${data.id}`);
								}
							}

							// 将用户归类到其他部门
							if(value.name !== this.selected_org_name){
								temp_arr.push(data.id);
								if(no_depts_arr.length !== 0 && no_depts_arr.indexOf(data.id) !== -1){
									// 记录要剔除的用户id
									delete_from_no_depts_arr.push(data.id);
								}
							}
						}
					});

					// 剔除即属于无部门，又属于其他部门的用户
					if(no_depts_index !== null){
						let children = this.targets_items_person[no_depts_index].children;
						children.forEach((v, i) =>{
							let id = parseInt(v.id.split("-")[1]);
							if(delete_from_no_depts_arr.indexOf(id) !== -1){
								children.splice(i,1);
							}

							// console.log(this.target_type);
							// console.log(this.init_targets);
							// console.log(this.depts_obj);
							if (this.target_type == 0 && this.init_targets && this.depts_obj) {
								// console.log(v.id);
								// console.log(this.targets);
								let targets_index = this.targets.indexOf(v.id);
								// console.log(targets_index);
								if(targets_index !== -1){
									this.targets.splice(targets_index, 1);
								}
							}
						});
					}
				});

				for (let i = this.targets_items_groups.length - 1; i >= 0; i--) {
					if (this.targets_items_groups[i].children.length == 0) {
						this.targets_items_groups.splice(i, 1);
					}
				}
				for (let i = this.targets_items_person.length - 1; i >= 0; i--) {
					if (this.targets_items_person[i].children.length == 0) {
						this.targets_items_person.splice(i, 1);
					}
				}
				if (this.init_targets) {
					this.isLoading = false;
					this.init_targets = false;
				}
				// this.changeTargetType();
			}).catch((Err) => {
				// console.log(Err.errmsg);
			});
		},
		finishChooseTargets() {
			if (!this.targets || this.targets.length == 0) {
				this.$toast("请选择对象", "warning");
				return;
			}
			this.dialog = false;
			this.send_target_chosen = "已选择";
		},
		// 切换个人和单位
		changeTargetType() {
			this.targets = [];
		},

		// 获取之前填的值
		initData() {
			this.axios.get(`/api/task/detail/${this.task_id}`).then((res) => {

				let task = res.data.data;

				this.title = res.data.data.title;
				this.body = res.data.data.desc;
				this.significance = res.data.data.significance;
				this.dead_line = res.data.data.deadline;
				this.auto_table = `${res.data.data.if_report_need_autotable}`;
				this.task = task;
				this.if_report_need_attachment = (this.task.if_report_need_attachment).toString();
				
				// 处理关联的附件，构建变量
				let build_attachments = this.structureAttachment(task.attachments);
				this.attach_pic = build_attachments.attach_pic;
				this.attach_file = build_attachments.attach_file;
				this.fileReady = true;

				// 判断是否有附件, 是否默认开启附件上传
				if (this.attach_pic.length !== 0 || this.attach_file.length !== 0) {
					this.is_upload = "1"; // todo why?!
				}
				// 保留原有发送对象的机构
				this.organization = this.task.org_id;
				// 保留原有发送对象的工作组
				this.workgroup = this.task.group_id;
				this.send_target_chosen = "已选择";

				task.task_items.forEach((value, index) => {
					// 保留原有的发送对象
					if (value.item_type == 0) {
						if (value.dept_id == 0) {
							this.targets_old.push(value.user.id);
							this.targets.push(value.user.id);
							this.target_type = "0";
						} else {
							this.targets_old.push(value.dept_id);
							this.targets.push(value.dept_id);
							this.target_type = "1";
						}
					}

					if (value.item_type == 1) {
						this.task_status = value.status;
					}
					// 判断是否已经重新审批
					if (value.item_type == 1 && value.status == 1) {
						this.$router.replace({path: `/task_detail_self/${this.task_id}`});
					}

					if (value.item_type == 2) {
						if (value.status === 2){
							this.$router.replace({path: `/task_detail_self/${this.task_id}`});
							return ;
						}

						this.audit.text = value.audit_text=== null ? "无批复内容":value.audit_text;
						this.audit.result = "未通过";
						this.audit.user = `${value.user.orgs[0].name}-${value.user.depts[0].name}-${value.user.name}`;

					}
				});
				// 去除发送对象的重复值
				this.targets_old = [...new Set(this.targets_old)];
				this.targets = [...new Set(this.targets)];
				this.isLoading = false;
				
				if (this.is_upload == "1" || this.is_uploadUrl == "1"){
					this.is_other = "1";
				}
				

				// 获取任务可发送机构
				this.initSendTarget();

			}).catch((Err) => {

			});
		}
	},

};
</script>

<style scoped>
.create-form {
background-color: #f6f6f6;
padding: 1rem;
}

.report-setting-box {
background: #fff;
}

.submit-btn {
width: 98%;
}

.audit-box {
background: #f44336;
color: #fff;
}
.btn_task{
height: 30px;
border-color: #fff;
}
.v-text-field{
}
.cad{
background: white;
box-shadow: 0 2px 1px -1px rgba(0,0,0,.2), 0 1px 1px 0 rgba(0,0,0,.14), 0 1px 3px 0 rgba(0,0,0,.12);
border-radius: 2px;
min-width: 0;
margin-bottom: 10px;
padding-bottom: 2px;
}
.hid{
overflow:hidden;
text-overflow: ellipsis;
white-space: nowrap;
}
.list{
background:#f5f5f5;
margin: 0 10px 10px 10px;
padding:3px;
color:#616161
}

</style>
