<template>
  <div>
    <v-container
      v-if="notice_id && !isLoading"
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
      v-if="!isLoading"
      ref="form"
      v-model="valid"
      class="create-form"
      lazy-validation>

      <v-select
        v-if="organization_items && organization_items.length !== 1 "
        v-model="organization"
        :items="organization_items"
        :label="organization_items.length===1?'机构':'选择机构'"
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
        label="通知标题"
        required
        @blur="scrollTo"
      />

      <v-textarea
        v-model="reports_text"
        box
        background-color="white"
        auto-grow
        label="通知内容（选填）"
        rows="3"
        hide-details
        @blur="scrollTo"
      />


      <v-text-field
        v-model="send_target_chosen"
        :rules="[v => (v && v != '请选择发送对象') || '请选择发送对象']"
        label="发送对象"
        box
        background-color="white"
        readonly
        required
        class="mt-4"
        @click="showSendTarget()"
      />


      <div class="report-setting-box px-2 pb-2 mb-4">
        <v-layout
          column
        >
          <v-flex
            xs12
          >
            <v-switch
              v-model="if_schedule"
              label="为发送对象创建日程"
              color="red"
              value="1"
              hide-details
              class="mt-0 pt-3 pb-2"
            />
          </v-flex>


          <v-flex
            v-if="if_schedule"
            class="report-setting-box pb-2  mt-4">


            <v-radio-group
              v-if="if_schedule"
              :rules="scheduleTypeRules"
              v-model="schedule_type"
              row>
              <v-radio
                :value="1"
                label="日程"/>
              <v-radio
                :value="2"
                label="提醒"/>
            </v-radio-group>

            <v-text-field
              v-if="if_schedule"
              v-model="schedule_title"
              :rules="scheduleTitleRules"
              background-color="white"
              box
              label="日程标题"
              @blur="scrollTo"
            />
            <v-textarea
              v-if="if_schedule"
              v-model="schedule_mark"
              :rules="scheduleCommentRules"
              box
              background-color="white"
              auto-grow
              label="日程备注"
              rows="1"
              @blur="scrollTo"
            />
            <v-text-field
              v-if="if_schedule && schedule_type==1"
              v-model="start_at"
              :rules="dateRules"
              label="开始时间"
              required
              readonly
              box
              background-color="white"
              @blur="scrollTo"
              @click="getStartTime()"
            />
            <v-text-field
              v-if="if_schedule && schedule_type==1"
              v-model="end_at"
              :rules="dateRules"
              label="结束时间"
              required
              readonly
              box
              background-color="white"
              @click="getEndTime()"
            />

            <v-select
              v-if="if_schedule && schedule_type === 1"
              :disabled="if_schedule == null || start_at === null"
              v-model="remind_at"
              :items="remind_items"
              :rules="dateRules"
              label="提醒时间"
              chips
              multiple
              required
              box
              background-color="white"
              @change="getRemindTime()"
            />

            <v-text-field
              v-if="if_schedule && schedule_type === 2"
              v-model="remind_at"
              :rules="[v => !!v || '请选择时间']"
              label="提醒时间"
              required
              readonly
              @click="getRemindTime()"
            />

            <v-checkbox
              v-if="if_schedule"
              v-model="self_schedule"
              label="是否同时给自己创建日程"
              color="blue"
              class="mt-0 mb-3"
              hide-details
            />
          </v-flex>
        </v-layout>
      </div>

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

      <!--任务详情  start -->
      <div
        v-if="is_other"
      >
        <div class="report-setting-box px-2 pb-2 mb-3">
          <v-layout>
            <v-flex
              xs5
              class="align-self-center">
              <v-switch
                v-model="is_upload"
                label="上传附件"
                color="red"
                value="1"
                hide-details
                height="36"
                class="mt-0"
              />
            </v-flex>
            <v-flex
              v-if="is_upload == 1"
              xs7
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
          v-if="notice_id && is_upload==1 && (attach_file.length !== 0 || attach_pic.length !== 0)"
          class="mt-3 mb-5"
        >
          <label class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-fujian"/>
            已上报附件
          </label>
          <files-downloader
            :pic.sync="attach_pic"
            :file.sync="attach_file"
            :downloader-type="1"
            :work="notice"
            :work-item-id="0"
            :if-can-delete="notice_status === 3"
            work-type="notification"
          />
        </div>

        <!--上传url地址-->
        <div class="report-setting-box px-2 pb-2 mb-3">
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
          <div v-if="is_uploadUrl == 1">
            <UploadUrl
              :url_id_list.sync="urlIdList"
              :works_id="notice_id"
              works_type="2"
            />
          </div>
        </div>
        <v-select
          v-model="significance"
          :items="levels"
          class="mb-3"
          box
          background-color="white"
          label="重要级别"
          required
          hide-details
        />
      </div>
      <!--通知详情  end -->

      <!-- 日期选择dialog start -->
      <v-dialog
        ref="dialog"
        v-model="date_dialog"
        persistent
        lazy
        full-width
        width="290px"
      >
        <!--        年月份-->
        <v-date-picker
          v-if="show_date_picker"
          v-model="date"
          :allowed-dates="allowedDates"
          locale="zh-cn"
          scrollable>
          <v-spacer/>
          <v-btn
            flat
            color="red"
            @click="date_dialog = false; show_time_picker = false">取消
          </v-btn>
          <v-btn
            flat
            color="red"
            @click="selectDate(date_type, date)">确定
          </v-btn>
        </v-date-picker>

        <!-- 时分-->
        <v-time-picker
          v-if="show_time_picker"
          :allowed-hours="allowedHours"
          :allowed-minutes="allowedMinutes"
          v-model="time"
          format="24hr">
          <v-spacer/>
          <v-btn
            flat
            color="red"
            @click="selectTime(date_type, time)">确定
          </v-btn>
        </v-time-picker>
      </v-dialog>

      <v-spacer/>

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
              :disabled="workgroup_items.length===1"
              :label="workgroup_items.length===1?'所在工作组':'选择工作组'"
              @change="chooseWorkgroup()"
            />
            <v-container
              v-if="show_target_type"
              class="pa-0"
            >
              <treeselect
                v-model="targets"
                :multiple="true"
                :searchable="false"
                :options="targets_items_person"
                :max-height="200"
                :always-open="true"
                :default-expand-level="0"
                :z-index="0"
                value-consists-of="LEAF_PRIORITY"
                no-options-text="机构中暂无可选成员"
                open-direction="below"/>
            </v-container>
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

      <!--submit(0)-->
      <v-layout
        class="pt-3"
        justify-space-between>
        <v-flex xs6>
          <v-btn
            v-if="!notice_id"
            class="submit-btn mx-0"
            @click.prevent="sumbitDialogs(0)"
          >
            发送
          </v-btn>
        </v-flex>
        <!--submit(1)-->
        <v-flex
          xs6
          style="text-align: right">
          <v-btn
            class="submit-btn mx-0"
            @click.prevent="sumbitDialogs(1)"
          >
            请示
          </v-btn>
        </v-flex>
      </v-layout>
    </v-form>

    <Loading v-if="isLoading"/>

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
import moment from "moment";
import Treeselect from "@riophae/vue-treeselect";
import UploadUrl from "../UploadUrl";
// import the styles
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Loading from "../../Commons/Loading";
import FilesUploader from "../FilesUploader";
import FilesDownloader from "../FilesDownloader";
import {mapMutations,mapState} from "vuex";

import Dialogs from "../../Commons/Dialogs";


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
		valid: false,
		title: "", // 标题
		title_rules: [
			v => !!v || "请填写通知标题",
			v => (v && v.length <= 20) || "通知标题必须不超过二十个字"
		],
		nameRules: [
			v => !!v || "标题不能为空",
			v => v.length <= 20 || "不可超过20个字符"
		],
		// schedule rules
		scheduleTitleRules: [
			v => !!v || "请填写日程标题",
			v => (v && v.length <= 20) || "日程标题必须不超过二十个字"
		],
		scheduleCommentRules: [
			v => !!v || "请填写日程备注"
		],
		scheduleTypeRules: [
			v => !!v || "请选择日程类型"
		],
		dateRules: [
			v => !!v || "请选择时间",
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

		// 通知类型的>>>>>>>>>>>>>>
		if_schedule: null,
		self_schedule: false,
		schedule_title: null, // 日程标题
		schedule_mark: null, // 日程备注
		schedule_type: 1,
		// 日期选择相关
		start_at: null,
		end_at: "",
		remind_at: "",
		remind_time: "",
		remind_items: [
			{
				value: 1,
				text: "开始时"
			},
			{
				value: 5,
				text: "开始前5分钟"
			},
			{
				value: 10,
				text: "开始前10分钟"
			},
			{
				value: 15,
				text: "开始前15分钟"
			},
			{
				value: 30,
				text: "开始前30分钟"
			},
			{
				value: 60,
				text: "开始前1小时"
			},
			{
				value: 24 * 60,
				text: "开始前1天"
			},
			{
				value: 48 * 60,
				text: "开始前2天"
			},
			{
				value: 7 * 24 * 60,
				text: "开始前1周"
			},
		],
		warn_date: null,
		date: null,
		time: null,
		date_dialog: false,
		show_time_picker: false,
		show_date_picker: false,
		date_type: null,
		// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>

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
		targets: [],
		targets_items_person: [],
		targets_old: [],
		// 初始发送对象
		init_targets: false,
		audit: {}, // 审批人

		notice: null, // 通知对象
		notice_id: null, // 审批不通过时的通知id
		notice_status: 0, // 通知状态

		// 附件上传相关
		// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
		is_upload: "0",
		uploadedFiles: [],
		emptyFileList: true,
		fileReady: false,
		uploadQuery: {works_type: 2, works_item_id: 0}, // works_type工作的类型，1任务，2通知，3附件;  type,0任务附件，1任务上报附件

		// 上传url地址相关
		// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
		is_uploadUrl: "0",
		urlIdList: [],
		// 附件下载相关
		// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
		attach_pic: [], // 流转审批不通过时，获取之前上传的附件
		attach_file: [], // 流转审批不通过时，获取之前上传的附件
		isLoading: true,
		submit_body:"",
		selected_org_name: null,
		is_other:false,

		temp_hours:null,
		selectdata:null

	}),
	computed: {
		...mapState(["selected_org"]),
		reports_text: {
			get () {
				let reg = new RegExp("<br/>","g");
				return this.body.replace(reg, "\n");
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
		schedule_type(newV, oldV) {
			// 从日程变为提醒，清空起止时间
			if (this.isLoading === false) {
				this.start_at = "";
				this.end_at = "";
				this.remind_at = "";
				this.remind_time = "";
			}

		},
		targets: {
			handler(newV, oldV) {
				if (newV.length == 0) {
					this.send_target_chosen = "请选择发送对象";
				} else {
					this.send_target_chosen = "已选择";
				}
			}
		},
		start_at() {
			if (Array.isArray(this.remind_at)) {
				this.getRemindTime();
			}
		},
		title() {
			if (!this.init_targets) {
				this.schedule_title = this.title;
			}
		},
		body() {
			if (!this.init_targets) {
				this.schedule_mark = this.body;
			}
		}
	},

	mounted: function () {
		this.warn_date = this.formatDate(new Date(), "yyyy-MM-dd");
		this.notice_id = this.$route.query.notice_id;
		this.dead_line = this.formatDate(new Date(), "yyyy-MM-dd hh:mm");
		this.now_date = this.formatDate(new Date(), "yyyy-MM-dd hh:mm").split(" ")[0];  // 在加载时获取到当前的日期
		this.now_times = this.formatDate(new Date(), "yyyy-MM-dd hh:mm").split(" ")[1]; // 加载当前的时间
		if (this.notice_id) {
			this.init_targets = true;
			this.initData();
			this.initUrl();
		} else {
			this.initSendTarget();
			this.isLoading = false;
		}
	},

	methods: {
		...mapMutations(["getControlledOrgs", "getControlledGroupsByOrg"]),

		sumbitDialogs(type){

			// 是否已经输入了通知内容
			if(this.title.trim().length === 0){
				this.$toast("请输入通知标题", "error");
				return;
			}

			if (this.targets.length < 1){
				this.$toast("请先选择发送对象", "error");
				return;
			}

			this.tips_type = type;
			this.tips_show = true;
			this.tips_fn = this.submit;

			if (type === 0){
				this.tips_titile = "发送";
				this.tips_agreed = "确认发送";
				this.tips_text  = "确认发送后，所选发送对象将收到此通知。确认发送请点“确认发送”，如需修改请点“取消”。";
				return false;
			}

			this.tips_titile = "发送";
			this.tips_agreed = "确认请示";
			this.tips_text  = "将此通知请示领导审批同意后才自动发送，继续请示请点“确认请示”，如需修改请点“取消”。";
		},

		// 查看流转审批详情
		checkDetails(){
			this.$router.push({
				path:"/allrecord",
				query: {
					type:0,
					sid:this.notice_id
				}
			});
		},

		// 提交
		submit(if_audit) {
			if (this.$refs.form.validate()) {
				// 是否已经输入了通知内容
				// if(this.reports_text.trim().length === 0){
				// 	this.$toast("请输入通知内容", "error");
				// 	return;
				// }

				// 判断是否有待上传的附件
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

				// 判断url是否有无上传
				if (this.is_uploadUrl === "0" || this.is_uploadUrl == null) {
					if (this.urlIdList.length > "0"){
						let data = {
							urlIdList: this.urlIdList,
							clear: "1"
						};
						this.axios.post("/api/task/deleteUrl",data).then((res) => {
						});
					}
				}

				// 处理发送对象
				let targets = this.deepClone(this.targets);
				targets.forEach((value, index) => {
					targets[index] = value.split("-")[1];
				});

				targets = [...new Set(targets)];
				// 解决vue-treeselect 报错问题，targets 必须要是数组
				// this.targets = this.targets.join(",");
				let targetsWithComma = targets.join(",");

				// 检查是否存在危险行为
				let strContent = "";
				if(this.reports_text){
					if(this.isValidate(this.reports_text,"check_html")){
						this.$toast("请重新输入通知内容","error");
						return ;
					}

					// 对内容进行转义
					let reg = new RegExp("\n","g");
					strContent = this.reports_text.replace(reg,"<br/>");
				}

				var temp_data = {
					title: this.title,
					desc: strContent.trim(),
					org_id: this.organization,
					group_id: this.workgroup,
					significance: this.significance,
					attachment: attachment,
					if_audit: if_audit,
					send_to_objs: targetsWithComma,
					// 如果有关联日程则需添加以下字段
					if_schedule: this.if_schedule,
					type: this.schedule_type,
					name: this.schedule_title,
					comment: this.schedule_mark,
					start_at: this.start_at,
					end_at: this.end_at,
					remind_at: this.schedule_type === 1 ? this.remind_time : this.remind_at,
					self_schedule: this.self_schedule
				};


				if (this.notice_id) {
					temp_data.notification_id = this.notice_id;
				} else {
					temp_data.notification_id = 0;
				}
				return this.axios.post("/api/notification/store", temp_data).then((res) => {
					this.updateWorksId(res.data.data.id);
					if (res.data.errcode === 0) {
						this.$toast(res.data.data.text, "success");
						if (if_audit == 0) {
							this.$router.push("/works/1");
						} else if (if_audit == 1) {
							this.$router.push({
								path: "/audit_task",
								query: {org_id: this.organization, id: res.data.data.id, work_type: 1}
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
				works_id: this.notice_id,
				works_type: "2",
			};
			this.axios.post("/api/task/lookupUrl", data).then((res) => {
				if(res.data.data.length>0){
					this.is_uploadUrl = "1";
				}
			});
		},

		// 日程时间选择相关操作
		getStartTime() {
			this.show_date_picker = true;
			this.date_dialog = true;
			this.date_type = 0;
		},

		getEndTime() {
			if (!this.start_at) {
				this.$toast("请先选择开始时间！", "warning");
				return;
			}

			this.show_date_picker = true;
			this.date_dialog = true;
			this.date_type = 1;
		},

		allowedDates(val) {
			this.selectdata = val;

			// 提醒的开始时间无限制
			if (this.schedule_type == 1 && this.date_type == 0) {
				let times = new Date(+new Date()+8*3600*1000).toISOString().replace(/T/g," ").replace(/\.[\d]{3}Z/,"").substr(0, 10);// 获取选择的日期
				return  val >= times;
			}

			if (this.schedule_type == 2){
				let times = new Date(+new Date()+8*3600*1000).toISOString().replace(/T/g," ").replace(/\.[\d]{3}Z/,"").substr(0, 10);// 获取选择的日期
				return  val >= times;
			}


			// 选择结束日期
			if (this.date_type == 1) {
				let start_at = this.start_at.split(" ")[0];
				return val >= start_at;
			}

			// 选择提醒日期
			if (this.date_type == 2) {
				let start_at = this.start_at.split(" ")[0];
				return val <= start_at;
			}

			return true;
		},

		allowedMinutes(val){
			let selected_time_at = this.date_type == 2 ? this.remind_at : this.date_type == 0 ? this.start_at : this.end_at;
			let now_hours = new Date().getHours();

			// 判断要选择的时间的类型 (提醒时间和开始时间一样处理)
			if(this.date_type == 1) {
				// 结束时间 》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》
				// 判断结束时间的日期是否与开始时间日期一样
				if(this.end_at.split(" ")[0] === this.start_at.split(" ")[0]){
					// 判断是否选中的小时是否等于开始时间的小时
					if(this.temp_hours == this.start_at.split(" ")[1].split(":")[0])
					// 如果是则选取已选中的开始时间的分钟数
						return val >= this.start_at.split(" ")[1].split(":")[1];
				}
			}else {
				// 开始时间/提醒时间 》》》》》》》》》》》》》》》》》》》》》》》》》》》》》
				// 判断是否选中了今天
				if (this.now_date === selected_time_at.split(" ")[0]){
					// 判断当前选中的小时是否等于当前小时
					if(this.temp_hours === now_hours)
					// 获取当前的分钟
						return val >= new Date().getMinutes();
				}
			}

			return val >= 0;
		},

		allowedHours(val) {
			this.temp_hours = val;
			let selected_time_at = this.date_type == 2 ? this.remind_at : this.date_type == 0 ? this.start_at : this.end_at;
			selected_time_at = selected_time_at.split(" ")[0];

			// 判断要选择的时间的类型 (提醒时间和开始时间一样处理)
			if(this.date_type == 1) {
				// 结束时间 》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》
				// 判断结束时间的日期是否与开始时间日期一样
				if (this.end_at.split(" ")[0] === this.start_at.split(" ")[0])
					// 结束时间必须大于等于已选中的开始时间小时数
					return val >= this.start_at.split(" ")[1].split(":")[0];
			}else {
				// 开始时间/提醒时间 》》》》》》》》》》》》》》》》》》》》》》》》》》》》》
				// 判断当前选中的日期是否为今天
				if (this.now_date === selected_time_at)
					// 开始时间必须大于等于当前时间
					return val >= new Date().getHours();
			}

			return val >= 0;
		},

		selectDate(date_type, date) {
			if (date == null) {
				this.$toast("请先选择日期", "warning");
				return;
			}
			switch (date_type) {
			case 0:
				this.start_at = date;
				if (this.fullday == "1") {
					this.end_at = "";
				}
				break;
			case 1:
				this.end_at = date;
				break;
			case 2:
				this.remind_at = date;
				break;
			}
			if (this.fullday == "1") {
				this.show_time_picker = false;
				this.date_dialog = false;
			} else {
				this.show_time_picker = true;
			}
			this.show_date_picker = false;
			this.date = null;
		},

		selectTime(date_type, time) {
			if (time == null) {
				this.$toast("请选择时间", "warning");
				return;
			}
			switch (date_type) {
			case 0:
				this.start_at += ` ${time}`;
				// 获取选中的 开始时间
				var tmp_time_str = new Date(this.start_at);
				// 开始时间加1个钟
				var tmp_end_time = new Date(tmp_time_str.getTime() + 60*60*1000);
				// 返回格式化的时间
				this.end_at = this.formatDate(tmp_end_time, "yyyy-MM-dd hh:mm");
				break;
			case 1:
				this.end_at += ` ${time}`;
				break;
			case 2:
				this.remind_at += ` ${time}`;
				break;
			}
			this.date_dialog = false;
			this.show_time_picker = false;
			this.time = null;
		},
		showSendTarget() {
			this.dialog = true;
		},
		initSendTarget() {
			let obj = {
				// 5 => 群组发放人 6 => 任务创建人
				arr: [5, 6],
				res: this.organization_items
			};

			// 默认选中当前的机构,在多机构情况下
			this.selected_org_name  = this.selected_org.name;

			// 直接从VueX 中获取当前用户可以发送通知的机构
			this.getControlledOrgs(obj);
			this.organization_items.forEach(function (value, index) {
				value.text = value.name;
				value.value = value.id;
			});


			if (!this.notice_id && this.targets.length == 0) {
				this.organization  = this.selected_org.id;
				this.chooseOrganization();
				return;
			}

			// 审核不通过情况，直接获取原有发送对象的机构
			if (this.init_targets) {
				this.chooseOrganization();
				return;

			}
		},

		chooseOrganization() {
			// 获取选中机构的名称
			this.selected_org_name = this.organization_items.find(item => item.id === this.organization)["name"];
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
			if (!this.workgroup) {
				return;
			}
			this.axios.get(`/api/dept/group/${this.workgroup}`).then((res) => {
				// let temp_id_arr = [];

				// 防止用户重复插入
				let temp_arr = [];
				// 无部门的所有用户id
				let no_depts_arr = [];
				// 要从无部门分类剔除的用户id
				let delete_from_no_depts_arr = [];
				// 无部门对应的索引
				let no_depts_index = null;

				res.data.data.forEach((value, index) => {
					// 构建部门对象数组
					for (let i = value.in_group_users.length - 1; i >= 0; i--) {
						// 机构本部改名与机构名称同名部门
						if(value.name === this.selected_org_name) {
							no_depts_index = index;
							value.name = this.selected_org_name;
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

							if (this.init_targets) {
							// 判断当前个人是否为原有发送对象
								if (this.targets_old.indexOf(data.id) !== -1) {
									this.targets.push(`${value.name}-${data.id}`);
								}
							}

							// 将用户归类到其他部门
							if(value.name !== this.selected_org_name){
								temp_arr.push(data.id);
								if(no_depts_arr.indexOf(data.id) !== -1){
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

							if (this.target_type == 0 && this.init_targets) {
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

				for (let i = this.targets_items_person.length - 1; i >= 0; i--) {
					if (this.targets_items_person[i].children.length == 0) {
						this.targets_items_person.splice(i, 1);
					}
				}
				if (this.init_targets) {
					this.isLoading = false;
					this.init_targets = false;
				}
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


		// 获取之前填的值
		initData() {
			this.axios.get(`/api/notification/detail/${this.notice_id}`).then((res) => {
				this.notice = res.data.data;
				this.title = res.data.data.title;
				this.body = res.data.data.desc;
				this.significance = res.data.data.significance;

				if (this.notice.complete){
					this.$router.push("/notice_detail_self/"+ this.notice.complete.notification_id +"?fo_msg_id="+  this.notice.complete.id  );
					return;
				}

				// 处理关联的附件，构建变量
				let build_attachments = this.structureAttachment(this.notice.attachments);
				this.attach_pic = build_attachments.attach_pic;
				this.attach_file = build_attachments.attach_file;
				this.fileReady = true;

				// 判断是否有附件, 是否默认开启附件上传
				if (this.attach_pic.length !== 0 || this.attach_pic.length !== 0) {
					this.is_upload = "1"; // todo why?!
				}

				// 保留原有发送对象的机构
				this.organization = this.notice.org_id;
				// 保留原有发送对象的工作组
				this.workgroup = this.notice.group_id;
				this.send_target_chosen = "已选择";

				res.data.data.notification_items.forEach((value, index) => {
					// 保留原有的发送对象
					if (value.item_type == 0) {
						this.targets_old.push(value.user.id);
						this.targets.push(`${value.user.depts[0].name}-${value.user.id}`);
					}
					if (value.item_type == 1) {
						this.notice_status = value.status;
					}
					if (value.item_type == 2) {
						if (value.audit_text != null) {
							this.audit.text = value.audit_text;
							this.audit.result = "未通过";
							this.audit.user = `${value.user.orgs[0].name}-${value.user.depts[0].name}-${value.user.name}`;
						}
					}
				});
				// 去除发送对象的重复值
				this.targets_old = [...new Set(this.targets_old)];
				this.targets = [...new Set(this.targets)];

				// 日程相关信息
				let schedules = res.data.data.schedules;
				if (schedules.length !== 0) {
					this.if_schedule = "1";
					this.schedule_type = schedules[0].type;
					this.schedule_title = schedules[0].name;
					this.schedule_mark = schedules[0].comment;
					this.start_at = schedules[0].start_at;
					this.end_at = schedules[0].end_at;

					schedules.forEach((value, i) => {
						if (this.$store.state.user_info.id === value.user_id)
							this.self_schedule = true;
					});

					// 提醒时间赋值
					if (this.schedule_type === 1) {
						let arr_remind_at = [];
						schedules[0].remind_at.forEach((value, i) => {
							arr_remind_at.push(this.getRemindAt(this.start_at, value));
						});
						this.remind_at = arr_remind_at;
						this.getRemindTime();
					} else {
						this.remind_at = schedules[0].remind_at[0];
					}
				}

				if (this.is_upload == "1" || this.is_uploadUrl == "1"){
					this.is_other = "1";
				}

				this.initSendTarget();
				this.isLoading = false;
			}).catch((Err) => {
				// console.log(Err);
			});
		},
		getRemindTime() {
			if (this.schedule_type === 1) {
				if (!this.start_at) {
					this.$toast("请先选择开始时间！", "warning");
					return;
				}
				let temp_start_at = this.start_at.replace(/-/g, "/");
				let start_at_format = "";
				start_at_format = new Date(temp_start_at);

				this.remind_time = "";
				this.remind_at.forEach((value, i) => {
					let temp_remind_time = "";
					switch (value) {
					case 1:
						temp_remind_time = moment(start_at_format).subtract(0, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 5:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 10:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 15:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 30:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 60:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 24 * 60:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 48 * 60:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 7 * 24 * 60:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					}
					this.remind_time += `${temp_remind_time},`;
				});
				this.remind_time = this.remind_time.substring(0, this.remind_time.length - 1);
			}

			// 如果是提醒，提醒时间选择的形式是date picker
			if (this.schedule_type === 2) {
				this.show_date_picker = true;
				this.date_dialog = true;
				this.date_type = 2;
			}
		},
		// 根据remind_at 时间获取select 选项
		getRemindAt(start, remind) {
			let sub = moment(start).diff(moment(remind), "m");
			switch (sub) {
			case 0:
				return this.remind_items[0].value;
			case 5:
				return this.remind_items[1].value;
			case 10:
				return this.remind_items[2].value;
			case 15:
				return this.remind_items[3].value;
			case 30:
				return this.remind_items[4].value;
			case 60:
				return this.remind_items[5].value;
			case 24 * 60:
				return this.remind_items[6].value;
			case 48 * 60:
				return this.remind_items[7].value;
			case 7 * 24 * 60:
				return this.remind_items[8].value;
			}
		}
	},
};
</script>

<style scoped>
  .create-form {
    background-color: #f6f6f6;
    padding: 1rem;
  }

  .submit-btn {
    width: 98%;
  }

  .audit-box {
    background: #f44336;
    color: #fff;
  }
  .report-setting-box {
    background: #fff;
  }
  .btn_task{
    height: 30px;
    border-color: #fff;
  }
</style>
