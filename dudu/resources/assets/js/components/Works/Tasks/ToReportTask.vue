<template>
  <div>
    <v-container
      v-if="!isLoading"
      class="px-0 pb-2 border-be">
      <v-layout
        column
        class="px-3">
        <v-flex class="pb-1">
          <div>
            <p class="title pb-2 mb-1 border-b">{{ task.title }}</p>
          </div>
        </v-flex>


        <v-flex 
          class="caption grey--text text--darken-1 mb-4 "
          style="font-size: 1rem !important;"
        >
          <p class="mb-0">上报人：{{ reporter.user.name }} <span v-if="reporter.dept !== null">（ {{ reporter.dept.name }} ）</span></p>
          <p class="mb-0">工作群组：{{ task.group.name }}</p>
          <p class="mb-0">签收时间：{{ reporter.receive_time }}</p>
          <p class="mb-0">上报时间：{{ reporter.report_time }}</p>
        </v-flex>


        <!--        <v-flex-->
        <!--          v-if="task.desc"-->
        <!--          class="mb-4">-->
        <!--          <div>-->
        <!--            <label class="subheading">-->
        <!--              <v-icon-->
        <!--                size="20"-->
        <!--                color="grey"-->
        <!--                class="iconfont dudu-shuoming-copy-copy"/>-->
        <!--              任务内容-->
        <!--            </label>-->
        <!--            <p-->
        <!--              class="body-1 work-text grey&#45;&#45;text text&#45;&#45;darken-2 pa-3 mt-1 mb-0"-->
        <!--              v-html="task.desc"-->
        <!--            />-->
        <!--          </div>-->
        <!--        </v-flex>-->


        <v-flex class="mb-4">
          <div>
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-shuoming-copy-copy"/>
              上报内容
            </label>

            <v-btn
              outline
              class="ma-0  btn_task"
              color="blue darken-1"
              style="background-color: rgba(255,255,255,0.8) !important;color:#409EFF !important;"
              @click="checkDetails"
            >审核详情</v-btn>

            <p
              v-if="reporter.report_text"
              class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
              v-html="reporter.report_text"
            />

            <p
              v-else
              class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
            >无上报内容</p>

          </div>
        </v-flex>


        <div
          v-if="report_attach_pic.length > 0"
        >
          <v-flex
            v-if="task.if_report_need_attachment === 1 || (task.if_report_need_attachment === 0 && task.attachments.length !== 0)"
            class="mb-4 py-2">
            <label class="subheading">
              <v-icon
                size="20"
                class="iconfont dudu-fujian"/>
              上报附件
            </label>

            <files-downloader
              :pic="report_attach_pic"
              :file="report_attach_file"
              :work="task"
              :downloader-type="0"
              :work-item-id="task_item_id"
            />

          </v-flex>
        </div>



        <!--<v-flex class="py-2">-->
        <!--<label class="subheading">-->
        <!--<v-icon-->
        <!--size="20"-->
        <!--color="grey"-->
        <!--class="iconfont dudu-jiezhishijian"/>-->
        <!--签收时间-->
        <!--</label>-->

        <!--<p class="body-1 grey&#45;&#45;text text&#45;&#45;darken-2 pl-2 pt-1 mb-0">{{ reporter.receive_time }}</p>-->

        <!--</v-flex>-->
      </v-layout>
    </v-container>

    <div
      v-if="!isLoading && reply"
      class="place-holder-box pt-5 pb-2 grey--text text--lighten-2">
      <span>- - - - - - - - - - </span>
      <v-icon class="iconfont dudu-trial grey--text text--lighten-2"/>
      <span> - - - - - - - - - -</span>
    </div>

    <v-container
      v-if="!isLoading && reply && show_btn"
      class="px-0 pb-0 border-te">
      <v-layout
        column
        class="mt-2 px-3">
        <v-flex>
          <label class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-shuoming-copy-copy"/>
            批复说明
          </label>
          <v-textarea
            v-model="audit_text"
            solo
            placeholder="批复说明"
            single-line
            class="mt-2"
            @blur="scrollTo"
          />
        </v-flex>
      </v-layout>
    </v-container>


    <v-container
      v-if="!isLoading && reply"
      class="px-0 pt-0">
      <v-layout
        class="px-3"
        column>
        <v-flex
          v-if="show_btn"
          class="mt-3">
          <v-layout>
            <v-btn
              :disabled="btn_disabled"
              class="white"
              block
              @click="openTimeDialog()">不通过
            </v-btn>
            <v-btn
              class="white"
              block
              @click="submitReport(4)">通过
            </v-btn>
          </v-layout>
        </v-flex>

      </v-layout>
    </v-container>

    <component
      v-if="isLoading"
      :is="cView"
    />

    <v-bottom-sheet
      v-model="time_dialog"
    >
      <v-card>
        <v-card-text>
          <v-layout
            column
            align-center>
            <v-text-field
              v-model="dead_line"
              box
              background-color="white"
              label="重设上报完成时限"
              readonly
              @blur="scrollTo"
              @click="menu=true"
            />
            <v-dialog
              ref="menu"
              v-model="menu"
              persistent
              lazy
              full-width
              width="290px"
            >
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
              <v-time-picker
                v-if="show_time"
                v-model="time"
                :allowed-hours="allowedHours"
                format="24hr"
                @change="saveDeadLine()"
              />
            </v-dialog>
            <v-spacer/>
            <v-flex>
              <v-btn
                flat
                color="primary"
                @click="time_dialog=false">取消
              </v-btn>
              <v-btn
                v-btn-control="submitReport.bind(null, 2)"
                :disabled="!dead_line"
                flat
                color="primary">确定
              </v-btn>
            </v-flex>
          </v-layout>
        </v-card-text>
      </v-card>
    </v-bottom-sheet>



    <!--提示框-->
    <Dialogs
      :dialog.sync="tips_show"
      :title="tips_titile"
      :text="tips_text"
      :closefn="tips_fn"
      :close="tips_close"
      :agreed="tips_agreed"
    />
  </div>
</template>

<script>
import Loading from "../../Commons/Loading";
import FilesDownloader from "../FilesDownloader";
import Dialogs from "../../Commons/Dialogs";
import {mapState} from "vuex";

export default {
	name: "ToReportTask",
	components: {
		FilesDownloader,
		Loading,
		Dialogs
	},
	data() {
		return {
			time_dialog: false,
			dead_line: null,
			menu: false,
			show_date: true,
			show_time: false,
			time: "",
			num_headers: [
				{
					text: "数据项一",
					align: "center",
					sortable: false
				},
				{
					text: "数据项二",
					align: "center",
					sortable: false
				},
				{
					text: "数据项三",
					align: "center",
					sortable: false
				}
			],
			datas: [
				{
					value: false,
					num1: 11,
					num2: 1.1,
					num3: 0.11
				}
			],
			task: {},
			reporter: {
				user: {
					orgs: [{}],
					depts: [{}]
				}
			},
			audit_text: "",
			show_btn: true,
			dept_index: 0, // 该任务的部门下标
			isLoading: true,
			cView: "Loading",
			task_item_id: null,
			btn_disabled:true,
			// 文件下载相关
			// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
			report_attach_pic: [],
			report_attach_file: [],
			// 提示框参数
			tips_show:false,
			tips_titile:"",
			tips_text:"",
			tips_fn:null,
			tips_type:null,
			tips_agreed:null,
			tips_close:"",
			// -------------
			reply:true,
		};
	},
	computed: {
		...mapState(["user_info"]),
	},
	watch: {
		audit_text (){
			if (this.audit_text.trim().length === 0){
				this.btn_disabled = true;
				return;
			}
			this.btn_disabled = false;

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
		tips_show(n){
			if (!n){
				this.$router.push({path:"/messages"});
			}
		}
	},
	mounted() {
		this.initData();
	},
	methods: {
		initData(){
			this.getInfo(this.$route.params.id);
		},
		// 查看流转审批详情
		checkDetails(){
			let user_id = this.$route.query.user_id;
			this.$router.push({
				path:"/allrecord",
				query: {
					type:2,
					sid:this.task.id,
					user_id:user_id
				}
			});
		},

		getInfo(id) {

			this.axios.get(`/api/task/detail/${id}`).then((res) => {
				this.task = res.data.data;
				// 判断任务是否已经被废止
				if(res.data.data[0]){
					if (res.data.data[0].indexOf("废止") > 0 || res.data.data[0].indexOf("不存在") > 0 ){
						this.$toast("该任务已被废止", "error");
						this.tips_show = true;
						this.tips_titile = "提示";
						this.tips_text = "该任务已被废止";
						this.tips_agreed = " ";
						this.tips_close = "返回";
						this.tips_fn = () => {
							this.$router.push({path:"/messages"});
						};

						return ;
					}
				}


				let send_user = null;
				this.task.task_items.forEach((value, index) => {

					if(value.item_type == 1){
						send_user = value.user_id;
					}

					// 获取上报人
					if (value.item_type == 0 && value.receiver_id == this.$route.query.user_id && value.dept_id == this.$route.query.dept_id) {
						this.task_item_id = value.id;

						// 获取该任务对应的部门
						value.user.depts.forEach((dept, i) => {
							if (dept.id == value.dept_id) {
								this.dept_index = i;
							}
						});

						this.reporter = value;

						if (value.status != 3) {
							this.show_btn = false;
						}

						// 获取上报deadline
						this.dead_line = value.report_deadline;
					}

				});

				// 校验当前用户是否是发送人
				if (send_user != this.user_info.id){
					this.reply = false;
				}


				// 处理关联的附件，构建变量
				let build_attachments = this.structureAttachment(this.task.attachments, this.task_item_id);

				this.report_attach_pic = build_attachments.report_attach_pic;

				this.report_attach_file = build_attachments.report_attach_file;

				this.isLoading = false;

			}).catch((Err) => {

			});
		},
		submitReport(status) {
			let temp_data = {
				task_id: this.$route.params.id,
				report_user_id: this.reporter.user.id,
				audit_text: this.audit_text,
				audit_result: status,
				dept_id: this.reporter.dept_id,
				dead_line: this.dead_line
			};
			return this.axios.post("/api/task/audit_report", temp_data).then((res) => {
				if (res.data.errcode === 0) {
					this.$toast(res.data.data, "success", 2000);
					this.show_btn = false;
					this.$router.push(`/task_detail_self/${this.$route.params.id}`);
				} else {
					this.$toast(res.data.errmsg, "error", 2000);
				}
			}).catch((Err) => {
			});
		},
		showTime() {
			if (!this.dead_line) {
				this.$toast("请选择日期", "warning");
				return;
			}
			this.show_date = false;
			this.show_time = true;
		},
		saveDeadLine() {
			this.show_date = true;
			this.show_time = false;
			this.dead_line += ` ${this.time}`;
			this.$refs.menu.save(this.dead_line);
		},
		allowedDates(val) {
			return val >= this.reporter.report_deadline.split(" ")[0];
		},
		allowedHours(val) {
			if (this.dead_line.split(" ")[0] == this.reporter.report_deadline.split(" ")[0]) {
				return val > this.reporter.report_deadline.split(" ")[1].split(":")[0];
			}
			return true;
		},
		openTimeDialog() {
			// 若设定了dead_line
			if(this.dead_line){
				let nowTime = new Date().getTime();
				let dead_line = new Date(this.dead_line).getTime();
				// 若已逾期
				if(nowTime > dead_line){
					this.time_dialog = true;
				}else{
					this.submitReport(2);
				}
			}else{
				// 若未设定dead_line
				this.submitReport(2);
			}
		}
	}
};
</script>

<style scoped>
.btn_task{
height: 30px;
border-color: #fff;
}
</style>
