<template>
  <div>
    <!-- 无权访问 -->
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
            <p class="title pb-2 mb-1 border-b">{{ notice.title }}</p>
          </div>
        </v-flex>

        <v-flex class="caption grey--text text--darken-1 mb-4 msg-font">
          <p class="mb-0">发送人：{{ sender.name }} <span>（{{ notice.org.name }}）</span></p>
          <p class="mb-0">工作群组：{{ notice.group.name }}</p>
          <p class="mb-0">发送时间：{{ notice.send_time }}</p>
        </v-flex>


        <v-flex
          class="mb-2">
          <div>
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-shuoming-copy-copy"/>
              通知内容
            </label>

            <p
              v-if="notice.desc"
              class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
              v-html="notice.desc"/>
            <p
              v-else
              class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
            >
              无内容
            </p>
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
            通知附件
          </label>
          <files-downloader
            :pic="attach_pic"
            :file="attach_file"
            :work="notice"
            :downloader-type="0"
            :work-item-id="0"
            work-type="notification"
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
              class="list">
              <div class="hid">标题: {{ item.url_title }}</div>
              <div class="hid">链接: <a :href="item.url_path">{{ item.url_path }}</a></div>
            </div>
          </div>
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
                  label="通知详情"
                  value="1"
                  hide-details
                  height="36"
                  class="mt-0"
                />
              </v-flex>
            </v-layout>
          </div>
        </v-flex>



        <!--其他详情 start-->
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
            >{{ notice.significance }}</p>
          </v-flex>


          <v-flex
            class="py-2">
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-tongji"/>
              查阅情况
            </label>

            <p class="body-1 grey--text text--darken-2 pl-2 pt-1 mb-0">
              <v-layout>
                <v-flex
                  xs7
                  class="align-self-center">
                  {{ finish_situation }}
                  <span class="grey--text">（已读数 / 总数）</span>
                </v-flex>

                <v-flex
                  xs5
                  class="align-self-center"
                  style="text-align:right;">


                  <v-dialog
                    v-model="dialog"
                    scrollable
                    content-class="mx-2"
                  >
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
                        class="headline grey lighten-2 pt-3 pb-2"
                        primary-title
                      >
                        查阅情况
                      </v-card-title>

                      <v-card-text
                        class="px-0"
                        style="height: 350px">
                        <notice-tab
                          ref="notice_tab"
                          :notice="notice"/>
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


        </v-flex>
        <!--其他详情 end-->



      </v-layout>
    </v-container>

    <v-container
      v-if="!isLoading && permission"
      class="px-0">
      <v-layout class="px-3">
        <v-flex
          v-if="notice_status == 1"
        >
          <v-layout>
            <v-flex>
              <!--confirmSign-->
              <v-btn
                class="white"
                block
                @click="sumbitDialogs(0)">确认阅读
              </v-btn>
            </v-flex>
            <v-flex>
              <!--transfer-->
              <v-btn
                class="white"
                block
                @click="sumbitDialogs(1)">转交
              </v-btn>
            </v-flex>
          </v-layout>
        </v-flex>

        <v-flex
          v-if="notice_status == 2">
          <v-btn
            block
            disabled>已读
          </v-btn>
        </v-flex>
      </v-layout>
    </v-container>

    <!-- 讨论区 -->
    <MsgBoard
      v-if="!isLoading && permission"
      :msg_list="notice.msg_boards"
      :user_list="notice.notification_items"

      :msg_sender="sender.id"

      prefix="通知"
      @submit="submitDiscuss"/>

    <!--提示框-->
    <Dialogs
      :dialog.sync="tips_show"
      :title="tips_titile"
      :text="tips_text"
      :fn="tips_fn"
      :agreed="tips_agreed"
      :types="tips_type"
      :close="tips_close"
      :closefn="tips_colsefn"
    />

    <!--转交弹窗-->
    <works-transfer
      v-if="notice_status === 1"
      :dialog.sync="transferDialog"
      :work="notice"
      :work_item_id="work_item_id"
      work_type="notification"/>

    <component
      v-if="isLoading"
      :is="cView"
    />

  </div>
</template>

<script>
import NoticeTab from "./NoticeTab";
import MsgBoard from "../MsgBoard";
import Loading from "../../Commons/Loading";
import FilesDownloader from "../FilesDownloader";
import WorksTransfer from "../WorksTransfer";
import Dialogs from "../../Commons/Dialogs";


export default {
	inject: ["reload"],
	components: {
		WorksTransfer,
		FilesDownloader,
		NoticeTab,
		Loading,
		MsgBoard,
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
			tips_close:null,
			tips_colsefn:null,
			// -------------
			id: null,
			user_info: {}, // 用户信息
			notice: {},
			significance_color: "grey--text",
			sender: {
				orgs: [{}],
				depts: [{}]
			},
			finish_num: 0, // 完成总数
			total_num: 0, // 总数
			finish_situation: "", // 完成情况
			dialog: false,
			notice_status: 0,
			isLoading: true,
			cView: "Loading",
			// 文件下载相关
			// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

			// url 相关
			urlList: [],

			attach_pic: [],
			attach_file: [],
			permission: false,
			transferDialog: false,
			/** 转交相关 */
			iswork_transfers:false,
			transfers_info:null,
			work_item_id:null,
			is_other:false
		};
	},
	computed: {
	},
	watch:{
		tips_show(n){
			if (!n && JSON.stringify(this.notice) == "{}"){
				this.$router.push({path:"/messages"});
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
		sumbitDialogs(type){
			this.tips_show = true;
			this.tips_close = "取消 ";

			if (type === 0){
				this.tips_titile =  "确认阅读";
				this.tips_text   =  "您将确认阅读此通知。 如属他人受理事项，请点“取消”后“转交”";
				this.tips_agreed =  "确认阅读";
				this.tips_fn     =  this.confirmSign;
				return false;
			}


			this.tips_titile =  "转交";
			this.tips_text   =  "将此通知转交给他人受理，继续请点“确认转交”， 如属本人受理请点“取消”后“确认阅读”。";
			this.tips_agreed =  "确认转交";
			this.tips_fn     =  this.transfer;

		},
		// 初始化数据
		initData(){
			// 判断是否是转交的通知
			let data = {notification_id:this.id};
			this.axios.post("/api/notification/transfer_history",data).then((res)=>{
				let work_transfers = res.data.data;
				if(work_transfers.length !== 0){
					this.iswork_transfers  = !this.iswork_transfers;
					this.transfers_info = work_transfers[0];
				}
			}).catch((err)=>{


			});

		},

		initUrl() {
			let data = {
				works_id: this.id,
				works_type: "2",
			};
			this.axios.post("/api/task/lookupUrl", data).then((res) => {
				this.urlList = res.data.data;

			});
		},
		transfer() {
			this.transferDialog = true;
		},
		confirmSign() {
			return this.axios.post("/api/notification/sign", {
				notification_id: this.notice.id,
			}).then((res) => {
				if (res.data.errcode === 0) {
					this.$toast(res.data.data, "success");
					this.reload();
					this.notice.notification_items.forEach((value, index) => {
						if (value.user.id == this.user_info.id) {
							value.status = 2;
							value.check_time = this.formatDate(new Date(), "yyyy-MM-dd hh:mm:ss");
						}
					});
					this.$refs.notice_tab.init();
					this.finish_num++;
					this.finish_situation = `${this.finish_num}/${this.total_num}`;
					this.notice_status = 2;
				} else {
					this.$toast(res.data.errmsg, "error");
				}

			}).catch((Err) => {

			});
		},

		getInfo(id) {

			this.axios.get(`/api/notification/detail/${id}`).then((res) => {

				// 判断任务是否已经被废止
				if(res.data.data[0]){
					if (res.data.data[0].indexOf("废止") > 0 || res.data.data[0].indexOf("不存在") > 0 ){
						this.tips_show = true;
						this.tips_titile = "提示";
						this.tips_text = "该通知已被废止";
						this.tips_agreed = " ";
						this.tips_close = "返回 ";
						this.tips_colsefn = () => {
							this.$router.push({path:"/messages"});
						};
						return ;
					}
				}

				this.notice = res.data.data;
				this.initUrl(); // 代替了预加载,不用做条件筛选

				if (this.notice.significance == 0) {
					this.notice.significance = "重要级别（普通）";
					this.significance_color = "grey--text";
				} else if (this.notice.significance == 1) {
					this.notice.significance = "重要级别（重要）";
					this.significance_color = "orange--text";
				} else {
					this.notice.significance = "重要级别（非常重要）";
					this.significance_color = "red--text";
				}

				// 查找到当用户的work_item_id
				// 			work_item_id
				this.notice.notification_items.forEach((v,i) => {
					if(v.user_id === this.user_info.id){
						this.work_item_id = v.id;
						return ;
					}
				});


				this.notice.notification_items.forEach((value, index) => {
					// 获取发送人
					if (value.item_type == 1) {
						this.sender = value.user;
					}
					// 当前用户是该任务的接收人
					if (value.user_id == this.user_info.id && value.item_type == 0) {
						this.permission = true;
						this.notice_status = value.status;
					}
					// 统计完成情况
					if (value.item_type == 0) {
						if (value.status == 2) {
							this.finish_num++;
						}
						this.total_num++;
					}
				});
				this.finish_situation = `${this.finish_num}/${this.total_num}`;

				// 处理关联的附件，构建变量
				let build_attachments = this.structureAttachment(this.notice.attachments);
				this.attach_pic = build_attachments.attach_pic;
				this.attach_file = build_attachments.attach_file;

				this.isLoading = false;
			}).catch((Err) => {

			});
		},
		submitDiscuss(obj) {
			let postData = {
				foreign_id: this.id,
				type: 2,
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
    margin: 10px;
    padding:3px;
    color:#616161
  }
</style>
