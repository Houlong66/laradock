<template>
  <div>
    <!-- 无访问权限 -->
    <div
      v-if="!isLoading && !permission"
      class="empty">这里似乎什么也没有~
    </div>


    <div
      v-if="!isLoading && permission">
      <!--任务 审核回复结果-->
      <v-container
        v-if="!isLoading && show_text"
        class="red white--text">

        <v-layout column>

          <v-flex
            class="mb-2" >
            <p class="subheading mb-1">{{ audit.result }}</p>

            <!-- 有批复文本时在显示-->
            <!--<div-->
            <!--v-if="audit.text"-->
            <!--&gt;-->
            <!--<p class="subheading mb-1">批复内容：</p>-->
            <!--<span-->
            <!--class="body-1"-->
            <!--v-html=" audit.text"/>-->
            <!--</div>-->
          </v-flex>


          <v-flex>
            <v-layout row>
              <v-btn
                outline
                class="ma-0 mb-2 btn_task"
                color="white"
                style="background-color: #f44336 !important;"
                @click="checkDetails"
              >查看审核详情</v-btn>
            </v-layout>
          </v-flex>


        </v-layout>

      </v-container>


      <v-container
        v-if="!isLoading"
        class="px-0 pb-2 border-be">
        <v-layout
          column
          class="px-3">
          <v-flex class="pb-1">
            <div>
              <p class="title pb-2 mb-1 border-b">
                {{ task.title }}
              </p>

            </div>
          </v-flex>

          <v-flex class="caption grey--text text--darken-1 mb-4 msg-font">
            <p class="mb-0">发送人：{{ sender.name }} <span>（{{ task.org.name }}）</span></p>
            <p class="mb-0">工作群组：{{ task.group.name }}</p>
            <p class="mb-0">发送时间：{{ task.send_time }}</p>
          </v-flex>




          <v-flex
            class="mb-4">
            <div>
              <label class="subheading">
                <v-icon
                  size="20"
                  color="grey"
                  class="iconfont dudu-shuoming-copy-copy"/>
                任务内容
              </label>
              <p
                v-if="task.desc"
                class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
                v-html="task.desc"/>
              <p
                v-else
                class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
              >无内容</p>
            </div>
          </v-flex>




          <v-flex
            v-if="iswork_transfers"
            class="mb-4">
            <div>
              <label class="subheading">
                <v-icon
                  size="20"
                  color="grey"
                  class="iconfont dudu-shuoming-copy-copy"/>
                来自{{ transfers_info.from_user.name }}的转交说明
              </label>
              <p
                class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
                v-html="transfers_info.remark"/>
            </div>
          </v-flex>

          <v-flex
            v-if="attach_pic.length !== 0 || attach_file.length !== 0"
            class="mb-4 py-2">
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-fujian"/>
              任务附件
            </label>
            <files-downloader
              :pic="attach_pic"
              :file="attach_file"
              :work="task"
              :downloader-type="0"
              :work-item-id="0"
              work-type="task"
            />
          </v-flex>


          <v-flex
            v-if="urlList.length > 0"
            class="mb-4 py-1"
          >
            <label
              class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-link"/>
              附加网址
            </label>
            <div
              class="cad mt-1 pa-1">
              <div
                v-for="(item,index) in urlList"
                :key="index"
                class="list ma-2">
                <div class="hid">标题: {{ item.url_title }}</div>
                <div class="hid">链接: <a :href="item.url_path">{{ item.url_path }}</a></div>
              </div>
            </div>
          </v-flex>



          <v-flex 
            v-if="deadline"
            class="mb-2 py-2 border-b"
          >
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-jiezhishijian"/>
              完成时限
            </label>
            <p class="body-1 grey--text text--darken-2 pl-2 pt-1 mb-0">{{ deadline }}</p>
          </v-flex>


          <v-flex class=" py-2 ">
            <!--  是否展开其他设置项 -->
            <div class="report-setting-box">
              <v-layout>
                <v-flex
                  xs6
                  class="align-self-center">
                  <v-switch
                    v-model="is_other"
                    label="任务详情"
                    value="1"
                    hide-details
                    height="36"
                    class="mt-0"
                  />
                </v-flex>
              </v-layout>
            </div>
          </v-flex>

          <!--其他任务设置   start-->

          <v-flex
            v-if="is_other"
            class="mb-4">

            <v-flex class="mb-4 py-2 border-b">
              <label class="subheading">
                <v-icon
                  :class="significance_color"
                  size="20"
                  class="iconfont dudu-zhongyaochengdu"/>
                重要级别
              </label>
              <p
                :class="significance_color"
                class="body-1 text--darken-2 pl-2 pt-1 mb-0"
              >{{ task.significance }}</p>
            </v-flex>

            <v-flex
              v-if="!isDepOthers"
              class="mb-4 py-2 border-b">
              <label class="subheading">
                <v-icon
                  size="20"
                  color="grey"
                  class="iconfont dudu-zhuangtai"/>
                任务状态
              </label>
              <p class="body-1 blue--text text--darken-2 pl-2 pt-1 mb-0">{{ taskStatus }}</p>
            </v-flex>




            <v-flex class="mb-4 py-2 border-b">
              <label class="subheading">
                <v-icon
                  size="20"
                  color="grey"
                  class="iconfont dudu-qianshou"/>
                签收对象
              </label>
              <p class="body-1 grey--text text--darken-2 pl-2 pt-1 mb-0">{{ receiver_name + receiver_info }}</p>
            </v-flex>


            <!-- 部门任务其他人看不到以下信息 -->
            <div
              v-if="!isDepOthers"
            >
              <v-flex
                class="py-2">
                <label class="subheading">
                  <v-icon
                    size="20"
                    color="grey"
                    class="iconfont dudu-tongji"/>
                  完成情况
                </label>


                <p class="body-1 grey--text text--darken-2 pl-2 pt-1 mb-0">
                  <v-layout>
                    <v-flex
                      xs7
                      class="align-self-center">
                      {{ finish_situation }}
                      <span class="grey--text">（已完成数 / 总数）</span>
                    </v-flex>

                    <v-flex
                      xs5
                      class="align-self-center"
                      style="text-align:right;">
                      <v-dialog
                        v-model="dialog"
                        content-class="mx-2"
                        scrollable>
                        <span
                          slot="activator"
                          class="grey--text"
                          style="position:relative; bottom:1px;"
                        >
                          点击查看详情
                          <v-icon
                            small
                            class="iconfont dudu-arrow"/>
                        </span>

                        <v-card>
                          <v-card-title
                            class="subheading grey lighten-2 pt-3 pb-2"
                            primary-title
                          >
                            完成情况
                          </v-card-title>

                          <v-card-text
                            class="px-0"
                            style="height: 350px">
                            <task-tab
                              ref="task_tab"
                              :task="task"/>
                          </v-card-text>

                          <v-divider/>

                          <v-card-actions>
                            <v-spacer/>
                            <v-btn
                              color="primary"
                              flat
                              @click="dialog = false"
                            >
                              关闭
                            </v-btn>
                          </v-card-actions>
                        </v-card>
                      </v-dialog>
                    </v-flex>
                  </v-layout>
                </p>
              </v-flex>
            </div>
          </v-flex>
          <!--其他任务设置   end-->
        </v-layout>
      </v-container>

      <div
        v-if="task_status >= 2 && !isLoading && !isDepOthers"
        class="place-holder-box pt-5 pb-2 grey--text text--lighten-2">
        <span>- - - - - - - - - - </span>
        <v-icon class="iconfont dudu-shangbao grey--text text--lighten-2"/>
        <span> - - - - - - - - - -</span>
      </div>

      <!--上报-->
      <v-container
        v-if="!isLoading && !isDepOthers"
        class="px-0 border-te">
        <v-layout
          v-if="task_status != 1"
          column
          class="mt-2 px-3">
          <v-flex class="mb-4">
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-shuoming-copy-copy"/>
              上报说明
            </label>

            <!--上报完成-->
            <p
              v-if="task_status == 3 || task_status == 4 || isDuban"
              class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
              v-html="report_text ? report_text : '无上报说明'"/>

            <!--上报未完成-->
            <v-textarea
              v-else
              v-model="reports_text"
              solo
              placeholder="请根据要求完成任务后，在此说明完成情况"
              single-line
              class="mt-2"
              @blur="scrollTo"
            />

          </v-flex>

          <v-flex
            v-if="report_attach_file.length !== 0 || report_attach_pic.length !== 0"
            class="mb-4">
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-fujian"/>
              已上报附件
            </label>

            <files-downloader
              :pic.sync="report_attach_pic"
              :file.sync="report_attach_file"
              :downloader-type="1"
              :work="task"
              :work-item-id="task_item_id"
              :if-can-delete="task_status === 2"
              work-type="task"
            />
          </v-flex>

          <v-flex
            v-if="task_status === 2 && !isDuban"
            class="mt-1"
          >
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-shangchuan"/>
              上传附件
            </label>

            <files-uploader
              :empty-file-list.sync="emptyFileList"
              :file-ready.sync="fileReady"
              :uploaded-files.sync="uploadedFiles"
              :upload-query="uploadQuery"
            />
          </v-flex>
        </v-layout>
      </v-container>
      <v-container
        v-if="!isLoading && (isPersonalReceiver || isDepReceiver)"
        class="px-0">
        <v-layout class="px-3">
          <v-flex v-show="task_status == 1">
            <v-layout>
              <!--confirmSign-->
              <v-flex>
                <v-btn
                  class="white"
                  block
                  @click="sumbitDialogs(0)">签收
                </v-btn>
              </v-flex>

              <!---->
              <v-flex>
                <v-btn
                  class="white"
                  block
                  @click="sumbitDialogs(1)">转交
                </v-btn>
              </v-flex>
            </v-layout>
          </v-flex>

          <!--confirmSubmit-->
          <v-flex
            v-show="task_status == 2">
            <v-btn
              :disabled="btn_disabled"
              class="white"
              block
              @click="sumbitDialogs(2)">
              上报
            </v-btn>
          </v-flex>

          <v-flex
            v-show="task_status == 3">
            <v-btn
              block
              disabled>已上报
            </v-btn>
          </v-flex>
        </v-layout>
      </v-container>
      <!-- 讨论区 -->
      <MsgBoard
        v-if="!isLoading && permission"
        :msg_list="task.msg_boards"
        :user_list="task.task_items"
        :msg_sender="sender.id"
        prefix="任务"
        @submit="submitDiscuss"/>
      <works-transfer
        v-if="task_status === 1"
        :dialog.sync="transferDialog"
        :work="task"
        :work_item_id="task_item_id"
        :dept_id="dept_id"
        work_type="task"/>
    </div>

    <!--提示框-->
    <Dialogs
      :dialog.sync="tips_show"
      :title="tips_titile"
      :text="tips_text"
      :fn="tips_fn"
      :closefn="tips_colsefn"
      :close="tips_close"
      :agreed="tips_agreed"
      :types="tips_type"
    />

    <component
      v-if="isLoading"
      :is="cView"
    />

</div></template>

<script>
import TaskTab from "./TaskTab";
import Loading from "../../Commons/Loading";
import MsgBoard from "../MsgBoard";
import FilesDownloader from "../FilesDownloader";
import FilesUploader from "../FilesUploader";
import WorksTransfer from "../WorksTransfer";
import Dialogs from "../../Commons/Dialogs";

export default {
	components: {
		WorksTransfer,
		FilesDownloader,
		FilesUploader,
		TaskTab,
		Loading,
		MsgBoard,
		Dialogs
	},
	inject: ["reload"],
	data() {
		return {
			// 提示框参数
			tips_show:false,
			tips_titile:"",
			tips_text:"",
			tips_fn:null,
			tips_type:null,
			tips_agreed:null,
			tips_close:"",
			tips_colsefn:null,
			// -------------
			id: null,
			deadline: null,
			dialog: false,
			task: {}, //任务详情
			task_status: 0, //任务状态
			task_item_id: null, // 当前用户对应的任务项id
			significance_color: "grey--text",
			sender: {
				orgs: [{}],
				depts: [{}]
			},
			report_text: "", //上报说明
			user_info: {}, //用户信息
			receiver_info: null, // 签收人的机构信息
			receiver_name: "", // 签收人的姓名
			finish_num: 0, //完成数量
			total_num: 0, // 任务总数
			finish_situation: "", //完成情况
			audit: {},
			dept_id: null, // 本人部门id
			isLoading: true,
			cView: "Loading",
			// 当前用户针对当前任务不是督办人
			isDuban: false,
			// 判断是否为部门任务
			isDept: false,
			// 文件下载相关
			// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
			attach_pic: [],
			attach_file: [],
			report_attach_pic: [],
			report_attach_file: [],
			// 文件上传相关
			// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

			// url 相关
			urlList: [],

			uploadedFiles: [],
			emptyFileList: true,
			fileReady: false,
			uploadQuery: {works_type: 1, works_item_id: ""}, // works_type工作的类型，1任务，2通知，3附件;  type,0任务附件，1任务上报附件
			// 个人任务，接收人
			isPersonalReceiver: false,
			// 部门任务接受者、监督者、其他人
			isDepReceiver: false,
			isDepOthers: false,
			permission: false,
			transferDialog: false,
			submit_report_text: "",  // 提交的上报说明
			// 转交相关
			iswork_transfers:false,
			transfers_info:null,
			show_text:false,
			// 是否需要其他设置
			is_other:false
		};
	},
	computed: {
		taskStatus() {
			return ["未发送", "未签收", "未上报", "已上报", "已办结"][this.task_status];
		},
		btn_disabled(){
			if (this.reports_text.trim().length !== 0){
				return false;
			}
			return true;
		},
		reports_text: {
			get () {
				let reg = new RegExp("<br/>","g");
				return this.report_text.replace(reg, "\n");
			},
			set (newValue) {
				this.report_text = newValue;
			}
		}
	},
	watch: {
		report_text(n, o) {
			if(this.report_text){
				let reg = new RegExp("\n","g");
				let copy_report_text = this.report_text;
				this.submit_report_text = copy_report_text.replace(reg, "<br/>");
			}
		},

		tips_show(n){
			if(!this.task.task_items){
				if (!n){
					this.$router.push({path:"/messages"});
				}
			}
		}

	},
	created() {
		this.user_info = this.$store.state.user_info;
		this.id = this.$route.params.id;
		this.getInfo(this.id);
	},
	mounted (){
		this.initData();
	},
	methods: {
		// 0:签收或1:转交
		sumbitDialogs(type) {
			this.tips_show = true;

			if (type === 0){
				this.tips_titile = "签收";
				this.tips_text = "“确认签收”后，您将负责办理、上报此任务。如属他人办理事项，请点“取消”后“转交”。";
				this.tips_agreed = "确认签收";
				this.tips_close = "取消";
				this.tips_fn = this.confirmSign;
				return ;
			}

			if (type === 2){
				this.tips_titile = "上报";
				this.tips_text = "“确认上报”后，任务发送人将收到您的上报信息并进行审核，审核不通过的将退回重报。如需修改请点“取消”。";
				this.tips_agreed = "确认上报";
				this.tips_close = "取消";
				this.tips_fn = this.confirmSubmit;
				return ;
			}

			this.tips_titile = "转交";
			this.tips_agreed = "确认转交";
			this.tips_text = "将此任务转交给他人办理，由对方负责上报。继续请点“确认转交”，如属本人办理请点“取消”后“签收”。";
			this.tips_close = "取消";
			this.tips_fn = this.transfer;
		},
		// 查看流转审批详情
		checkDetails(){
			this.$router.push({
				path:"/allrecord",
				query: {
					type:2,
					sid:this.task.id,
					dept_id:this.dept_id
				}
			});
		},
		// 初始化数据
		initData(){

			// 判断是否是转交的任务
			let data = {task_id:this.id};
			this.axios.post("/api/task/transfer_history",data).then((res)=>{
				let work_transfers = res.data.data;
				if(work_transfers.length !== 0){
					this.iswork_transfers  = !this.iswork_transfers;
					this.transfers_info = work_transfers[0];
				}
			}).catch((err)=>{


			});

		},
		transfer() {
			this.transferDialog = true;
		},

		confirmSign() {
			return this.axios.post("/api/task/sign", {
				task_id: this.task.id,
				dept_id: this.dept_id
			}).then((res) => {
				if (res.data.errcode === 0) {
					this.$toast(res.data.data, "success");
					this.reload();
					this.task.task_items.forEach((value, index) => {
						// 找到当前任务对应的 task_item, 用户id、部门id相同，且为item_type要是0
						if (value.user.id == this.user_info.id && value.dept_id === this.dept_id && value.item_type === 0) {
							value.status = 2;
							value.receive_time = this.formatDate(new Date(), "yyyy-MM-dd hh:mm:ss");
							this.$refs.task_tab.init();
							if (this.isDept) {
								let dept_name = value.dept === null ? "部门已注销" : value.dept.name;
								this.receiver_info = `（${this.task.org.name}-${dept_name}）`;
								this.receiver_name = `${value.user.name}`;
							} else {
								this.receiver_info = `（${this.task.org.name}）`;
								this.receiver_name = `${value.user.name}`;
							}
						}
					});
					this.task_status = 2;
					// 修改签收对象内容
				} else {
					this.$toast(res.data.errmsg, "error");
				}
			}).catch((Err) => {

			});
		},

		// 确认上报
		confirmSubmit() {
			// 检查是否存在危险行为
			if(this.isValidate(this.reports_text,"check_html")){
				this.$toast("请重新输入上报内容","error");
				return ;
			}

			// 对内容进行转义
			let  strContent = this.submit_report_text;

			// 判断是否有待上传的附件
			let attachment = 0;
			// 判断是否有新上传的附件
			if(!this.emptyFileList){
				// 判断新上传附件是否全部上传成功
				if (this.fileReady) {
					attachment = this.getAttachmentIdStr(this.uploadedFiles);
				} else {
					// 有未完成上传的附件
					this.$toast("请先上传附件", "error");
					return;
				}
			}

			// 拼接旧的上传附件
			attachment = this.plusAttachmentIdStr(attachment, this.report_attach_pic, this.report_attach_file);

			// 若上传必须带附件，则附件变量不可为空
			if (this.task.if_report_need_attachment === 1) {
				if (attachment === 0) {
					this.$toast("此工作必须上传附件", "error");
					return;
				}
			}

			this.axios.post("/api/task/report", {
				task_id: this.task.id,
				report_text: strContent.trim(),
				dept_id: this.dept_id,
				attachment: attachment
			}).then((res) => {
				if (res.data.errcode === 0) {
					this.$toast(res.data.data, "success");
					this.task.task_items.forEach((value, index) => {
						if (value.user.id == this.user_info.id) {
							value.status = 3;
						}
					});
					this.task_status = 3;
					this.$router.push("/works/0");
				} else {
					this.$toast(res.data.errmsg, "error");
				}
			}).catch((Err) => {

			});
		},

		initUrl() {
			let data = {
				works_id: this.id,
				works_type: "1",
			};
			this.axios.post("/api/task/lookupUrl", data).then((res) => {
				this.urlList = res.data.data;

			});
		},

		getInfo(id) {

			this.axios.get(`/api/task/detail/${id}`).then((res) => {

				// 判断任务是否已经被废止
				if(res.data.data[0]){
					if (res.data.data[0].indexOf("废止") > 0 || res.data.data[0].indexOf("不存在") > 0 ){
						this.tips_show = true;
						this.tips_titile = "提示";
						this.tips_text = "该任务已被废止";
						this.tips_agreed = " ";
						this.tips_close = "返回 ";
						this.tips_colsefn = () => {
							this.$router.push({path:"/messages"});
						};
						return ;
					}
				}

				this.task = res.data.data;

				// 查询对应上传的url
				this.initUrl(); // 代替了预加载,不用做条件筛选
				if (this.task.significance == 0) {
					this.task.significance = "重要级别（普通）";
					this.significance_color = "grey--text";
				} else if (this.task.significance == 1) {
					this.task.significance = "重要级别（重要）";
					this.significance_color = "orange--text";
				} else {
					this.task.significance = "重要级别（非常重要）";
					this.significance_color = "red--text";
				}

				let depts = {}; // 部门分组计数
				this.isDept = this.task.task_items[0].dept_id === 0 ? this.isDept : true;
				// 审批信息
				this.task.task_items.forEach((value, index) => {
					// 获取发送人
					if (value.item_type == 1) {
						this.sender = value.user;
					}
					// 获取审批人  我接收的任务
					if (value.item_type == 0 && value.user_id == this.user_info.id && this.$route.query.dept_id == value.dept_id) {


						this.audit.text = value.audit_text;
						if (value.status == 2 && value.report_text) {
							this.show_text = true;
							this.audit.result = "审核不通过";
						}
						if (value.status == 4) {
							this.show_text = true;
							this.audit.result = "审核通过";
						}

					}

					// 部门任务获取任务执行项的数据
					if (this.isDept) {
						// 部门任务的执行项
						if (value.item_type === 0 && this.$route.query.dept_id == value.dept_id) {
							// 上报附件所需参数
							this.uploadQuery.works_item_id = value.id;
							this.report_text = value.report_text === null ? "" : value.report_text;
							this.task_status = value.status;
							this.task_item_id = value.id; // 任务项id
							this.dept_id = value.dept_id;
							this.deadline = value.report_deadline;

							// 获取签收人信息
							if (value.receiver_id === null) {
								this.receiver_info = "暂无";
							} else {
								// 签收人机构信息
								let dept_name = value.dept === null ? "部门已注销" : value.dept.name;
								this.receiver_info = `（${this.task.org.name}-${dept_name}）`;
								// 签收人姓名
								this.receiver_name = `${value.user.name}`;
							}
						}

						// todo 部门任务，任务的流转审批人不能上报同一个任务
						if (this.user_info.id === value.user_id) {
							// console.log('in');
							// 部门任务任务接收人
							if (value.item_type === 0) {
								// console.log('in 0');
								this.isDepReceiver = true;
							} else if (value.item_type === 3) {
								// console.log('in 3');
								// 部门任务督办
								this.isDuban = true;
							} else {
								// console.log('in 4');
								// 部门任务其他
								// this.isDepOthers = true;
							}
						}
						// console.log(this.isDepReceiver);
						// console.log(this.isDepOthers);

						// 统计完成情况
						if (value.item_type === 0) {
							var dept_id = value.dept_id;
							if (depts[dept_id] == undefined) {
								depts[dept_id] = [];
							}
							depts[dept_id].push(value);
						}

					} else {

						// 判断当前用户是否是单人任务接收人
						if (value.item_type === 0 && this.user_info.id === value.user_id) {
							this.isPersonalReceiver = true;
						}

						// 个人任务的执行项
						if (value.item_type === 0 && this.user_info.id === value.user_id) {
							// 上报附件所需参数
							this.uploadQuery.works_item_id = value.id;
							this.report_text = value.report_text === null ? "" : value.report_text;
							this.task_status = value.status;
							this.task_item_id = value.id; // 任务项id
							this.dept_id = value.dept_id;
							this.deadline = value.report_deadline;

							// 获取签收人信息
							if (value.receiver_id === null) {
								this.receiver_info = "暂无";
							} else {
								// 签收人机构信息
								this.receiver_info = `（${this.task.org.name}）`;
								// 签收人姓名
								this.receiver_name = `${value.user.name}`;
							}
						}

						// 统计完成情况
						if (value.item_type === 0) {
							this.total_num++;
							if (value.status === 4) {
								this.finish_num++;
							}
						}
					}

				});

				this.permission = this.isPersonalReceiver || this.isDepReceiver || this.isDuban || this.isDepOthers;

				// 若是部门任务,重新处理统计数据
				if (this.isDept) {
					this.total_num = Object.keys(depts).length;
					for (let key in depts) {
						depts[key].forEach((value, index) => {
							if (value.status == 4) {
								this.finish_num++;
								return;
							}
						});
					}
				}

				// 处理关联的附件，构建变量
				let build_attachments = this.structureAttachment(this.task.attachments, this.task_item_id);
				this.attach_pic = build_attachments.attach_pic;
				this.attach_file = build_attachments.attach_file;
				this.report_attach_pic = build_attachments.report_attach_pic;
				this.report_attach_file = build_attachments.report_attach_file;
				if (this.report_attach_file.length !== 0 || this.report_attach_pic.length !== 0) {
					this.fileReady = true;
				}

				this.finish_situation = `${this.finish_num} / ${this.total_num}`;
				this.isLoading = false;

				// 判断是否已经签收任务
				if (this.task_status === 2){
					document.title  = "上报任务";
				}


				if (this.task_status === 4){
					document.title = "已上报任务";
				}


			}).catch((Err) => {
			});
		},
		submitDiscuss(obj) {
			let postData = {
				foreign_id: this.id,
				type: 1,
				content: obj.content,
				at_sign: obj.id
			};
			this.axios.post("/api/msgboard/create", postData)
				.then(res => {
					if (res.data.errcode == 0) {
						this.$toast("操作成功", "success");
						this.discuss_content = "";
						this.getInfo(this.id);
					} else {
						this.$toast(res.data.errmsg, "error");
					}
				}).then(err => {

				});
		}
	}
};
</script>

<style scoped>
.details-text{
color:darkolivegreen;
width:247px;
overflow: hidden;
font-size: .9rem;
padding: 4px;
word-break: break-word;
word-break: break-all;
}
.cad{
background: white;
box-shadow: 0 2px 1px -1px rgba(0,0,0,.2), 0 1px 1px 0 rgba(0,0,0,.14), 0 1px 3px 0 rgba(0,0,0,.12);
border-radius: 2px;
min-width: 0;
}
.hid{
overflow:hidden;
text-overflow: ellipsis;
white-space: nowrap;
}
.list{
background:#f5f5f5;
padding:3px;
color:#616161
}
</style>
