<template>
  <div>
    <!-- 无权访问页面 -->
    <div
      v-if="!isLoading && !permission"
      class="empty">这里似乎什么也没有~
    </div>

    <v-container
      v-if="!isLoading && permission"
      class="px-0 pb-2 border-be">

      <v-layout
        column
        class="px-3">

        <v-flex class="pb-1">
          <div>
            <p class="title pb-2 mb-1 border-b">{{ ask.title }}</p>
          </div>
        </v-flex>

        <v-flex class="caption grey--text text--darken-1 mb-4 msg-font">
          <p class="mb-0">请示人：{{ sender.name }} <span>（{{ ask.org.name }}）</span></p>
          <p class="mb-0">请示时间：{{ ask.send_time }}</p>
        </v-flex>


        <v-flex
          class="mb-4">

          <div>
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-shuoming-copy-copy"/>
              请示内容
            </label>

            <v-btn
              v-if="ask_status_str != '待审批'"
              outline
              class="ma-0  btn_task"
              color="blue darken-1"
              style="background-color: rgba(255,255,255,0.8) !important;color:#409EFF !important;"
              @click="checkDetails"
            >请示详情  </v-btn>

            <p
              class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
              v-html="ask.desc ? ask.desc : `无内容` "/>
          </div>

        </v-flex>


        <v-flex
          v-if="note_text && note_text !== 'undefined'"
          class="mb-4">
          <div
            class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-beizhu"/>
            转发说明<span class="grey--text caption">（转发人:{{ work_send_name }} , 转发时间:{{ work_send_time }} ）</span>

            <p
              class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
              v-html="note_text"/>
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
            请示附件
          </label>
          <files-downloader
            :pic="attach_pic"
            :file="attach_file"
            :work="ask"
            :downloader-type="0"
            :work-item-id="0"
            work-type="ask"
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
              class="iconfont dudu-link pr-1"/>附加网址</label>
          <div
            class="cad mt-1 pa-1">
            <div
              v-for="(item,index) in urlList"
              :key="index"
              class="list">
              <div class="hid">标题: {{ item.url_title }}</div>
              <div class="hid">链接: <a :href="item.url_path">{{ item.url_path }}</a></div>
            </div>
          </div>
        </v-flex>

        <!--  是否其他请示详情 -->
        <div class="report-setting-box pt-2 pb-2 mb-2">
          <v-layout>
            <v-flex
              xs6
              class="align-self-center">
              <v-switch
                v-model="is_other"
                label="请示详情"
                value="1"
                hide-details
                height="36"
                class="mt-0"
              />
            </v-flex>
          </v-layout>
        </div>

        <div
          v-if="is_other"
        >
          <v-flex class="mb-4 py-2 border-b">
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-leixing"/>
              类型
            </label>
            <p class="body-1 grey--text text--darken-2 pl-2 pt-1 mb-0">{{ ask_type_str }}</p>
          </v-flex>

          <v-flex class="mb-4 py-2 border-b">
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-zhuangtai"/>
              审批状态
            </label>
            <p class="body-1 grey--text text--darken-2 pl-2 pt-1 mb-0">{{ ask_status_str }}</p>
          </v-flex>

        </div>


        <v-flex v-if="self_send == 1 && !audit">
          <v-btn
            class="white"
            block
            @click="flowAudit()">发送审批申请
          </v-btn>
        </v-flex>
      </v-layout>
    </v-container>




    <div
      v-if="!isLoading && (self_send == 0 || audit) && permission"
      class="place-holder-box pt-5 pb-2 grey--text text--lighten-2">
      <span>- - - - - - - - - - </span>
      <v-icon class="iconfont dudu-trial grey--text text--lighten-2"/>
      <span> - - - - - - - - - -</span>
    </div>

    <!-- 批复结果 -->
    <v-container v-if="!isLoading && audit && permission">
      <v-layout
        column>
        <v-flex class="mb-4">
          <div>
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-shuoming-copy-copy"/>
              批复内容
            </label>
            <p class="caption grey--text text-no-wrap text-truncate my-1">批复人:{{ audit.user.name }}</p>
            <p class="caption grey--text text-no-wrap text-truncate my-1">批复时间:{{ audit.audit_time }}</p>
            <div class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0">
              <p
                class="mb-1"
                v-html="audit.audit_text"/>
            </div>
          </div>
        </v-flex>


      </v-layout>
    </v-container>

    <!-- 只有请示接收人才能进行批复和转发 -->
    <v-container
      v-if="!isLoading && self_send==0 && !audit && permission && is_receiever"
      class="px-0 border-te">
      <v-layout
        column
        class="mt-2 px-3">
        <v-flex>
          <label class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-shuoming-copy-copy"/>
            批复内容
          </label>
          <v-textarea
            v-model="audit_text"
            solo
            placeholder="请在此输入对该请示项的批复内容，转发他人审批的无需输入"
            single-line
            class="mt-2"
            @blur="scrollTo"
          />
        </v-flex>
      </v-layout>

      <!-- 批复转发按钮 -->
      <v-layout
        class="px-3">

        <v-flex
          class="xs6"
        >
          <!--confirmAudit-->
          <v-btn
            class="white"
            block
            @click="sumbitDialogs(0)">批复
          </v-btn>
        </v-flex>

        <v-flex
          class="xs6"
        >
          <!--flowAudit-->
          <v-btn
            class="white"
            block
            @click="sumbitDialogs(1)"
          >转发他人审批
          </v-btn>
        </v-flex>
      </v-layout>

    </v-container>

    <!-- 讨论区 -->
    <!--<MsgBoard -->
    <!--v-if="!isLoading && permission"-->
    <!--:msg_list="ask.msg_boards"-->
    <!--:user_list="ask.ask_items"-->
    <!--:msg_sender="sender.id"-->
    <!--prefix="请示"-->
    <!--@submit="submitDiscuss"/>-->

    <component
      v-if="isLoading"
      :is="cView"
    />

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
import Loading from "../../Commons/Loading";
import FilesDownloader from "../FilesDownloader";
// import MsgBoard from "../MsgBoard";
import { mapState } from "vuex";
import Dialogs from "../../Commons/Dialogs";

export default {
	components: {
		Loading,
		FilesDownloader,
		// MsgBoard,
		Dialogs
	},

	data() {
		return {
			id: null,
			// 提示框参数
			tips_show:false,
			tips_titile:"",
			tips_text:"",
			tips_fn:null,
			tips_type:null,
			tips_agreed:null,
			// -------------
			ask: {},
			sender: {
				depts: [{}],
				orgs: [{}]
			}, // 发送人
			self_send: null,
			audit: null, // 审批人
			audit_text: "", // 批复内容
			isLoading: true,
			cView: "Loading",
			// 文件下载相关
			// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
			attach_pic: [],
			attach_file: [],
			permission: false,
			is_receiever: false,
			pop_dialog: false,
			note_text:null,
			work_send_name:null,
			work_send_time:null,
			// url相关
			urlList: [],
			is_other:false,
			ask_type_str:null,
		};
	},

	computed: {
		...mapState(["user_info"]),



		ask_status_str(){
			let status = 0;

			let arr  = this.ask.ask_items.filter(function (v) {
				return v.item_type == 0 && v.status == 1;
			});

			if (arr.length !== 0){
				status = 1;
			}

			return ["待审批","已审批"][status];

		}
	},

	mounted: function () {
		// 审批人添加
		this.addAuditor();
		this.initWxShare();
		this.initUrl();

	},
	methods: {
		sumbitDialogs(type){

			this.tips_show = true;
			if (type === 0){
				this.tips_titile = "批复";
				this.tips_text = "您将批复此请示。 如需他人审批，请点“取消”后“转发他人审批”";
				this.tips_agreed = "确认批复";
				this.tips_fn = this.confirmAudit;
				return ;
			}

			this.tips_titile = "转发他人审批";
			this.tips_text = "将此请示转发他人审批，继续请点“确认转发”，如属本人审批请点“取消”后“批复”。";
			this.tips_agreed = "确认转发";
			this.tips_fn = this.flowAudit;

		},

		// 查看流转审批详情
		checkDetails(){
			this.$router.push({
				path:"/allrecord",
				query: {
					type:3,
					sid:this.ask.id,
				}
			});
		},

		// 流转审批
		flowAudit() {
			this.$router.push({
				path: "/audit_task",
				query: {org_id: this.ask.org_id, id: this.ask.id, work_type: 2}
			});
		},

		// 批复按钮
		confirmAudit() {
			// 检查是否存在危险行为
			if(this.isValidate(this.audit_text,"check_html")){
				this.$toast("您输入的批复内容不合法，请重新输入","error");
				return ;
			}else if (this.audit_text.trim().length <= 0) {
				this.$toast("请输入批复内容","error");
				return;
			}

			// 对内容进行转义
			let reg = new RegExp("\n","g");
			let  strContent = this.audit_text.replace(reg, "<br/>");

			this.axios.post("/api/ask/flow_audit", {
				ask_id: this.$route.params.id,
				audit_text: strContent,
				audit_result: 1
			}).then((res) => {
				if (res.data.errcode === 0) {
					this.$toast(res.data.data, "success");
					this.$router.push("/works/2");
				} else {
					this.$toast(res.data.errmsg, "error");
				}
			}).catch((Err) => {

			});
		},

		getInfo() {

			this.axios.get(`/api/ask/detail/${this.id}`).then((res) => {

				this.ask = res.data.data;


				this.ask.ask_items.forEach((value, index) => {
					// 获取发送人
					if (value.item_type == 1) {
						this.sender = value.user;
					}

					// 判断当前用户是否是发送人或者接收人之一
					if(value.user_id == this.user_info.id) {
						this.permission = true;
					}

					// 获取到申请备注内容
					if (value.user_id == this.user_info.id && value.item_type == 0){
						// 判断URL是否带参
						this.note_text = typeof(this.$route.query.note_text) != "undefined" ?  this.$route.query.note_text : value.note_text;
						this.work_send_name = value.work_send.name;
						this.work_send_time = value.updated_at;
					}

					// 获得审批记录
					if (value.item_type == 0) {

						// 当前用户是接收人之一
						if(value.user_id == this.user_info.id) {
							this.is_receiever = true;
						}

						if(value.status == 1) {
							this.audit = value;
						}
					}
				});


				let str_arr = ["工作事项", "请假申请", "用车申请"];
				this.ask_type_str =  str_arr[this.ask.ask_type - 1];

				// 处理关联的附件，构建变量
				let build_attachments = this.structureAttachment(this.ask.attachments);
				this.attach_pic = build_attachments.attach_pic;
				this.attach_file = build_attachments.attach_file;

				this.isLoading = false;
			}).catch((Err) => {

			});
		},
		// submitDiscuss(obj) {
		// 	let postData = {
		// 		foreign_id: this.id,
		// 		type: 3,
		// 		content: obj.content,
		// 		at_sign: obj.id,
		// 	};
		// 	this.axios.post("/api/msgboard/create", postData)
		// 		.then(res => {
		// 			if(res.data.errcode == 0) {
		// 				this.$toast("操作成功", "success");
		// 				this.discuss_content = "";
		// 				this.getInfo(this.id);
		// 			} else {
		// 				this.$toast(res.data.errmsg, "error");
		// 			}
		// 		}).then(err => {
		//
		// 		});
		// },
		// 添加审批记录
		addAuditor() {

			let url = "/api/ask/add_flow_auditors";


			let wx_text = null;
			let wx_send_id = null;

			// 微信需要的消息
			if(this.$route.query.work_send_id){
				wx_send_id = this.$route.query.work_send_id;
			}

			if(this.$route.query.note_text != "undefined"){
				wx_text = this.$route.query.note_text;
			}

			this.axios.post(url, {

				id: this.$route.params.id,
				send_to_objs: this.$store.state.user_info.id,
				if_send_wx_message: 0,

				// 微信需要的消息
				wx_note_text : wx_text,
				wx_send_id : wx_send_id

			}).then((res) => {
				// 添加成功后获取请示信息详情
				this.id = this.$route.params.id;
				this.self_send = this.$route.query.self_send;
				this.getInfo();
			}).catch((Err) => {

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
		// 查看url
		initUrl() {
			let data = {
				works_id: this.$route.params.id,
				works_type: "3",
			};
			this.axios.post("/api/task/lookupUrl", data).then((res) => {
				this.urlList = res.data.data;
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
