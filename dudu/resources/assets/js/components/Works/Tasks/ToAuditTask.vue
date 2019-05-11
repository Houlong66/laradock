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

        <v-flex class="caption grey--text text--darken-1 mb-4 msg-font">
          <p class="mb-0">发放人：{{ sender.user.name }} <span>（{{ task.org.name }}）</span></p>
          <p class="mb-0">工作群组：{{ task.group.name }}</p>
        </v-flex>

        <v-flex
          v-if="task.desc"
          class="mb-4">
          <div>
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-shuoming-copy-copy"/>
              {{ content_label }}
            </label>
            <p
              class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
              v-html="task.desc"/>
          </div>
        </v-flex>


        <!-- 申请备注 -->
        <v-flex
          v-if="show_note"
          class="mb-4">
          <div>
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-beizhu"/>
              {{ note_label }}
              <span
                v-if="send_user_name !== ''"
                class="grey--text caption">（申请转发人：{{ send_user_name }}）</span>
            </label>
            <p
              class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
              v-html="task.note_text"/>
          </div>
        </v-flex>


        <v-flex
          v-if="type==1 && task.schedules.length !== 0"
          class="mb-4">
          <div>
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-richeng"/>
              关联日程
            </label>
            <p class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0">
              <v-layout column>
                <v-flex class="pb-1 text-truncate">
                  <label class="body-2">日程标题:</label>
                  <span> {{ task.schedules[0].name }} </span>
                </v-flex>
                <v-flex class="pb-1 text-truncate">
                  <label class="body-2">日程备注:</label>
                  <span> {{ task.schedules[0].comment }} </span>
                </v-flex>
                <v-flex class="pb-1 text-truncate">
                  <label class="body-2">日程类型:</label>
                  <span v-if="task.schedules[0].type === 1"> 日程 </span>
                  <span v-else> 提醒 </span>
                </v-flex>
                <v-flex
                  v-if="task.schedules[0].type === 1"
                  class="pb-1 text-truncate">
                  <label class="body-2">开始时间:</label>
                  <span>{{ task.schedules[0].start_at }}</span>
                </v-flex>
                <v-flex
                  v-if="task.schedules[0].type === 1"
                  class="pb-1 text-truncate">
                  <label class="body-2">结束时间:</label>
                  <span>{{ task.schedules[0].end_at }}</span>
                </v-flex>
              </v-layout>
            </p>
          </div>
        </v-flex>

        <v-flex
          v-if="attach_pic.length !== 0 && attach_file !== 0"
          class="mb-4 py-2">
          <label class="subheading">
            <v-icon
              size="20"
              class="iconfont dudu-fujian"/>
            {{ attachment_label }}
          </label>
          <files-downloader
            :pic="attach_pic"
            :file="attach_file"
            :work="task"
            :downloader-type="0"
            :work-type="workType"
          />
        </v-flex>

        <v-flex
          v-if="urlList.length > 0"
          class="mb-4">
          <label class="subheading">
            <v-icon
              size="20"
              class="iconfont dudu-fujian"/>附加网址</label>
          <div class="cad">
            <div
              v-for="(item,index) in urlList"
              :key="index"
              class="list">
              <div class="hid">标题: {{ item.url_title }}</div>
              <div class="hid">URL地址: <a :href="item.url_path">{{ item.url_path }}</a></div>
            </div>
          </div>
        </v-flex>

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
          v-if="type == 0"
          class="mb-4 py-2 border-b">
          <label class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-jiezhishijian"/>
            完成时限
          </label>
          <p class="body-1 grey--text text--darken-2 pl-2 pt-1 mb-0">{{ task.deadline === null?"不限":task.deadline }}</p>
        </v-flex>

        <v-flex class="mb-4 py-2 border-b">
          <label class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-zhuangtai"/>
            {{ status_label }}
          </label>
          <p class="body-1 blue--text text--darken-2 pl-2 pt-1 mb-0">{{ taskStatus }}</p>
        </v-flex>

        <v-flex
          class="py-2">
          <label class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-tongji"/>
            接收对象
          </label>

          <p class="body-1 grey--text text--darken-2 pl-2 pt-1 mb-0">
            <v-layout>
              <v-flex
                xs7
                class="align-self-center">
                {{ target_datas.length }}
                <span class="grey--text">个</span>
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
                    查看接收对象
                    <v-icon
                      small
                      class="iconfont dudu-arrow"/>
                  </span>

                  <v-card>
                    <v-card-title
                      class="subheading grey lighten-2 pt-3 pb-2"
                      primary-title
                    >
                      接收对象
                    </v-card-title>

                    <v-card-text
                      class="px-0"
                      style="height: 350px">
                      <v-data-table
                        :headers="target_headers"
                        :items="target_datas"
                        hide-actions
                        class="elevation-1"
                      >
                        <template
                          slot="items"
                          slot-scope="props">
                          <td class="text-xs-center">{{ props.item.dept }}</td>
                          <td class="text-xs-center">{{ props.item.name }}</td>
                        </template>
                      </v-data-table>
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


      </v-layout>
    </v-container>

    <div
      v-if="!isLoading"
      class="place-holder-box pt-5 pb-2 grey--text text--lighten-2">
      <span>- - - - - - - - - - </span>
      <v-icon class="iconfont dudu-trial grey--text text--lighten-2"/>
      <span> - - - - - - - - - -</span>
    </div>

    <v-container
      v-if="!isLoading"
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
            审批意见
          </label>
          <v-textarea
            v-model="audit_text"
            solo
            placeholder="如不同意输入审批意见"
            single-line
            class="mt-2"
            @blur="scrollTo"
          />
        </v-flex>
      </v-layout>
    </v-container>


    <v-container
      v-if="!isLoading"
      class="px-0 pt-0">
      <v-layout
        class="px-3"
        column>
        <v-flex>
          <v-layout>
            <v-flex
              v-if="sender.status != 2 && sender.status != 3"
              xs6
              class="mt-3">
              <v-btn
                :disabled="btn_disabled"
                class="white"
                block
                @click="submitAudit(3)">不同意
              </v-btn>
            </v-flex>
            <v-flex
              v-if="sender.status != 2 && sender.status != 3"
              xs6
              class="mt-3">
              <v-btn
                class="white"
                block
                @click="submitAudit(2)">同意
              </v-btn>
            </v-flex>
          </v-layout>
        </v-flex>


        <v-flex
          v-if="sender.status != 2 && sender.status != 3"
          xs12>
          <v-btn
            class="white"
            block
            @click="flowAudit()">转发他人审批
          </v-btn>
        </v-flex>
      </v-layout>
    </v-container>


    <ShareTips :dialog_flag.sync="pop_dialog"/>
    <component
      v-if="isLoading"
      :is="cView"
    />

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
import {mapState} from "vuex";
import Loading from "../../Commons/Loading";
import FilesDownloader from "../FilesDownloader";
import ShareTips from "../../Commons/ShareTips";
import Dialogs from "../../Commons/Dialogs";


export default {
	name: "ToAuditTask",
	components: {
		FilesDownloader,
		Loading,
		ShareTips,
		Dialogs
	},
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
			// -------------
			pop_dialog: false,
			content_label: "",
			attachment_label: "",
			status_label: "",
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
			target_headers: [
				{
					text: "部门",
					align: "center",
					sortable: false
				},
				{
					text: "姓名",
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
			target_datas: [],
			task: {},
			significance_color: "grey--text",
			sender: {
				user: {
					orgs: [{}],
					depts: [{}]
				}
			},
			audit_text: "",
			dialog: false,
			type: null,
			isLoading: true,
			cView: "Loading",
			workType: "",
			// 附件下载相关
			// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
			attach_pic: [],
			attach_file: [],

			urlList:[],

			note_label:null,
			show_note:false,
			note_text:"",
			send_user_name: "",
		};
	},
	computed: {
		...mapState(["user_info"]),
		taskStatus() {
			return ["无需审核", "待审核", "审核通过", "审核不通过"][this.sender.status];
		},
		btn_disabled(){
			if(this.audit_text.trim().length === 0){
				return true;
			}
			return false;
		}
	},
	watch:{
		tips_show(n){
			if (!n){
				this.$router.push({path:"/messages"});
			}
		}
	},
	mounted() {
		this.type = this.$route.query.type;
		this.addAuditor();
		this.initWxShare();
	},
	methods: {
		// 流转审批
		flowAudit() {
			this.$router.push({
				path: "/audit_task",
				query: {org_id: this.task.org_id, id: this.task.id, work_type: this.type}
			});
		},

		// 初始化微信分享
		initWxShare() {
			/*global host*/
			let url = window.location.href;

			let title = "新的审批"; //分享的标题
			let share_img = ""; //分享的图片
			let desc = "请及时处理！"; //分享的描述信息
			let cb = () => {
				this.$toast("已成功发送给微信好友", "success");
			};
			this.wxShare(url, title, share_img, desc, cb);
		},


		getInfo(id) {

			let url = "";

			let items = "";


			this.note_label = "备注说明";

			if (this.type == 0) {

				this.workType = "task";

				url = `/api/task/detail/${id}`;

				items = "task_items";

				this.content_label = "任务内容";
				this.attachment_label = "任务附件";
				this.status_label = "任务状态";


			} else if (this.type == 1) {

				this.workType = "notification";
				url = `/api/notification/detail/${id}`;
				items = "notification_items";
				this.content_label = "通知内容";
				this.attachment_label = "通知附件";
				this.status_label = "通知状态";

			}



			// 获取数据
			this.axios.get(url).then((res) => {


				this.task = res.data.data;

				// 获取相关对应的url地址
				// this.urlList = this.task.url;
				if (this.type == 0){
					this.task.url.forEach((item) => {
						if (item.works_type == "App\\Models\\Task"){
							this.urlList.push(item);
						}
					});
				}else if (this.type == 1){
					this.task.url.forEach((item) => {
						if(item.works_type == "App\\Models\\Notification"){
							this.urlList.push(item);
						}
					});
				}


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

				this.task[items].forEach((value, index) => {
					// 获取发送人
					if (value.item_type == 1) {
						this.sender = value;
						if(value.audit_text){
							this.audit_text = value.audit_text;
						}
					}

					// 获取流转审批内容
					if(value.user_id == this.user_info.id && value.item_type == 2 ){
						if(value.task_id == this.task.id ||  value.notification_id == this.task.id){
							// 判断url是否带参数
							let url_msg = JSON.stringify(this.$route.query).indexOf("note_text");
							let note_text_msg  = this.$route.query;

							this.show_note = true;

							if(url_msg > 0){
								this.task.note_text = note_text_msg.note_text;
								this.task.send_id  = note_text_msg.work_send_id;
								this.send_user_name = "";
								return;
							}

							this.task.note_text = value.note_text;
							this.task.send_id   = value.work_send_id;
							this.send_user_name = value.send_user.name;
						}
					}

					// 获取接收对象
					if (value.item_type == 0) {
						let temp_target = {};
						if (this.type == 0) {
							// 判断是否为个人任务
							if (value.dept_id === 0) {
								this.target_headers[0].text = "机构";
								temp_target = {
									dept: this.task.org.name,
									name: value.user.name
								};
							} else {
								temp_target = {
									dept: value.dept.name,
									name: value.user.name
								};
							}
						} else {
							this.target_headers[0].text = "机构";
							temp_target = {
								dept: this.task.org.name,
								name: value.user.name
							};
						}

						this.target_datas.push(temp_target);
					}


				});

				// 处理关联的附件，构建变量
				let build_attachments = this.structureAttachment(this.task.attachments, this.task_item_id);
				this.attach_pic = build_attachments.attach_pic;
				this.attach_file = build_attachments.attach_file;

				this.isLoading = false;
			}).catch((Err) => {

			});
		},
		// 同意审批
		submitAudit(status) {

			let url = "";
			let id = "";

			if (this.type == 0) {
				url = "/api/task/flow_audit";
				id = "task_id";
			} else if (this.type == 1) {
				url = "/api/notification/flow_audit";
				id = "notification_id";
			}

			let temp_data = {
				audit_text: this.audit_text,
				audit_result: status,
				note_text:this.task.note_text,
				work_send_id : this.task.send_id
			};

			temp_data[id] = this.$route.params.id;

			this.axios.post(url, temp_data).then((res) => {

				if (res.data.errcode === 0) {
					this.$toast(res.data.data, "success");
					this.$router.push("/works/0");
				} else {
					this.$toast(res.data.errmsg, "error");
				}
			}).catch((Err) => {

			});
		},

		// 获取流转审批记录
		addAuditor() {

			let msg_id = this.$route.query.fo_msg_id;

			let url = "";

			let wx_text = null;

			let wx_send_id = null;

			if (this.type == 0) {

				url = "/api/task/add_flow_auditors";

			} else if (this.type == 1) {

				url = "/api/notification/add_flow_auditors";

			}

			// 检查url是否带参数
			if(this.$route.query.note_text){
				wx_text = this.$route.query.note_text;
				wx_send_id =this.$route.query.work_send_id;
			}

			this.axios.post(url, {
				id: this.$route.params.id,
				send_to_objs: this.$store.state.user_info.id,
				if_send_wx_message: 0,
				msg_id:msg_id ? msg_id : null,
				//微信需要的参数
				wx_note_text  : wx_text,
				wx_work_send_id : wx_send_id
			}).then((res) => {

				if (res.data.data[0]){
					if(res.data.data[0].indexOf("废止") > 0){
						let str = "";
						url.indexOf("task") != -1 ? str = "任务"  : str = "通知";
						this.$toast(`该${str}已被废止`, "error");
						this.tips_show = true;
						this.tips_titile = "提示";
						this.tips_text = `该${str}已被废止`;
						this.tips_agreed = " ";
						this.tips_close = "返回";
						this.tips_fn = () => {
							this.$router.push({path:"/messages"});
						};
						return ;
					}
				}

				// 添加新审批人后，获取任务/通知详情
				this.getInfo(this.$route.params.id);

			}).catch((Err) => {

			});
		}
	}
};
</script>

<style scoped>
.cad{
background: white;
box-shadow: 0 2px 1px -1px rgba(0,0,0,.2), 0 1px 1px 0 rgba(0,0,0,.14), 0 1px 3px 0 rgba(0,0,0,.12);
border-radius: 2px;
min-width: 0;
padding: 1px 0 1px 0;
margin-top: 10px
}
.hid{
overflow:hidden;
text-overflow: ellipsis;
white-space: nowrap;
}
.list{
background:#f5f5f5;
margin: 10px;
padding:3px;
color:#616161
}
</style>
