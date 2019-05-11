<template>
  <div>
    <!-- 无权访问页面 -->
    <div
      v-if="!isLoading && !permission"
      class="empty">这里似乎什么也没有~
    </div>

    <!--审批盒子-->
    <div
      v-if="!isLoading && permission">
      <v-container
        v-if="show_text"
        class="red white--text mb-2">

        <v-layout
          column>
          <v-flex class="mb-2">
            <p class="subheading mb-0 pt-1">{{ audit.result }}</p>
            <!--            <div-->
            <!--              v-if="audit.text"-->
            <!--            >-->
            <!--              <p class="subheading mt-2 mb-1">批复内容：</p>-->
            <!--              <span class="body-1">{{ audit.text }}</span>-->
            <!--            </div>-->
          </v-flex>

          <v-flex>
            <v-layout row>
              <v-btn
                v-if="!can_transfer"
                outline
                class="ma-0 mb-2 btn_task"
                color="white"
                style="background-color: #f44336 !important;"
                @click="checkDetails"
              >查看审批详情</v-btn>
              <v-btn
                v-else
                outline
                class="ma-0 mb-2 btn_task"
                color="white"
                style="background-color: #f44336 !important;"
                @click="flowAudit"
              >发送审批申请</v-btn>
            </v-layout>
          </v-flex>
        </v-layout>
      </v-container>
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
          <p class="mb-0">所属机构：{{ notice.org.name }}</p>
          <p class="mb-0">工作群组：{{ notice.group.name }}</p>
          <p class="mb-0">发送时间：{{ notice.send_time !== null ? notice.send_time : "未发放" }}</p>
        </v-flex>

        <v-flex
          v-if="notice.desc"
          class="mb-4">
          <div>
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-shuoming-copy-copy"/>
              通知内容
            </label>
            <p
              class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
              v-html="notice.desc"/>
          </div>
        </v-flex>


        <!--<v-flex class=" py-2 ">-->
        <!--&lt;!&ndash;  是否展开其他设置项 &ndash;&gt;-->
        <!--<div class="report-setting-box px-2 ">-->
        <!--<v-layout>-->
        <!--<v-flex-->
        <!--xs6-->
        <!--class="align-self-center">-->
        <!--<v-switch-->
        <!--v-model="is_other"-->
        <!--label="任务详情"-->
        <!--value="1"-->
        <!--hide-details-->
        <!--height="36"-->
        <!--class="mt-0"-->
        <!--/>-->
        <!--</v-flex>-->
        <!--</v-layout>-->
        <!--</div>-->

        <!--</v-flex>-->

        <!--其他任务设置   start-->

        <!--<v-flex-->
        <!--v-if="is_other"-->
        <!--class="mb-4">-->
        <v-flex
          class="mb-4">

          <!--<v-flex class="mb-4 py-2 border-b">-->
          <!--<label class="subheading">-->
          <!--<v-icon-->
          <!--size="20"-->
          <!--class="iconfont dudu-zhongyaochengdu"/>-->
          <!--重要级别-->
          <!--</label>-->
          <!--<p-->
          <!--class="body-1 text&#45;&#45;darken-2 pl-2 pt-1 mb-0"-->
          <!--&gt;{{ notice.significance }}</p>-->
          <!--</v-flex>-->


          <v-flex
            v-if="notice.attachments.length !== 0 "
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
            v-if="notice.url.length > 0"
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
                v-for="(item,index) in notice.url"
                :key="index"
                class="list ma-2">
                <div class="hid">标题: {{ item.url_title }}</div>
                <div class="hid">链接: <a :href="item.url_path">{{ item.url_path }}</a></div>
              </div>
            </div>
          </v-flex>


        </v-flex>

        <!--其他任务设置   end-->

        <v-flex
          class="py-2">
          <label class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-tongji"/>
            查阅情况
          </label>

          <notice-tab :notice="notice"/>
        </v-flex>


        <v-flex
          v-if="tranfresbox"
          class="py-2">

          <label class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-zhuanrangzhuangtai"
            />
            转交记录
          </label>

          <!--转交详情-->
          <div>
            <v-data-table
              :headers="headers"
              :items="desserts"
              :search="search"
              :pagination.sync="pagination"
              hide-actions
              class="elevation-1"

            >
              <template
                slot="items"
                slot-scope="props"
              >
                <tr
                  v-if=" props.item.worktransfres.length !== 0"
                  @click="checkTranfresMsg( props.item.id)"
                >
                  <td >{{ props.item.worktransfres[0].from_user.name }}</td>
                  <td >{{ props.item.count }}</td>
                  <td >  <v-icon class="iconfont dudu-you1 "/> </td>
                </tr>
              </template>

              <template slot="no-data">
                <p
                  class="my-0 py-2 grey--text"
                  style="text-align:center">暂无相关数据</p>
              </template>

            </v-data-table>


            <div
              v-if="pagesums"
              class="text-xs-center pt-2"
            >
              <v-pagination
                v-model="pagination.page"
                :length="pages"/>
            </div>
          </div>
        </v-flex>

        <v-layout class="mt-2">
          <v-flex xs6>
            <v-btn
              :disabled="sucessbtn"
              class="pa-0 ma-0"
              style="width: 100%"
              color="info"
              @click="setTaskStatusDialogs(0)"
            >办结</v-btn>
          </v-flex>
          <v-flex xs6>
            <v-btn
              class="pa-0 ma-0"
              style="width: 100%"
              color="info"
              @click="setTaskStatusDialogs(1)">废止</v-btn>
          </v-flex>
        </v-layout>


      </v-layout>
    </v-container>

    <!--底部弹窗-->
    <Dialogs
      :show.sync ="dialogs_bottom"
      :title="dialogs_title"
    >

      <template v-slot:dialogs-content>

        <v-card
          class="mx-auto"
          max-width="600"
        >
          <v-card-title
            class="brown lighten-1  accent-2-grey white--text"
          >
            转交记录
          </v-card-title>
          <v-card-text class="py-0">

            <v-timeline dense>

              <v-slide-x-reverse-transition
                group
                hide-on-leave
              >
                <v-timeline-item
                  v-for="item in transfres_list"
                  :key="item.id"
                  :color="item.color"
                  :icon="item.icon"
                  small
                  fill-dot
                >
                  <v-alert
                    :value="true"
                    :color="item.color"
                  >
                    <div>
                      <p class="mb-1">{{ item.updated_at }}</p>
                      <p class="mb-1">发起人：{{ item.from_user.name }}</p>
                      <p class="mb-1">接收人：{{ item.to_user.name }}</p>


                      <p class="mb-1">转交说明：</p>

                      <div
                        v-if="item.remark"
                        class="details-text white"
                        style="color:#000;"
                        v-html="item.remark"
                      />

                      <div
                        v-else
                        class="details-text white"
                        v-text="'无转交说明'"
                      />
                    </div>
                  </v-alert>
                </v-timeline-item>
              </v-slide-x-reverse-transition>
            </v-timeline>
          </v-card-text>
        </v-card>

      </template>

    </Dialogs>

    <!-- 讨论区 -->
    <MsgBoard
      v-if="!isLoading && permission"
      :msg_list="notice.msg_boards"
      :user_list="notice.notification_items"
      :msg_sender="user_info.id"
      prefix="通知"
      @submit="submitDiscuss"/>

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
import NoticeTab from "./NoticeTab";
import Loading from "../../Commons/Loading";
import MsgBoard from "../MsgBoard";
import {mapState} from "vuex";
import Dialogs from  "../../Commons/Dialogs";
import FilesDownloader from "../FilesDownloader";


export default {
	name: "NoticeDetailSelf",
	inject: ["reload"],
	components: {
		NoticeTab,
		Loading,
		MsgBoard,
		Dialogs,
		FilesDownloader
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
			// -------------
			id: null,
			notice: {},
			isLoading: true,
			cView: "Loading",
			permission: false,
			show_text: false,
			audit: {},
			// 转交表格数据格式
			search: "",
			pagination: {},
			selected: [],
			headers: [
				{
					text: "初始接收人",
					sortable: false,
					value: "name"
				},
				{ text: "转交次数",
					value: "count"
				},
				{
					text: "详情",
					sortable: false,
				},
			],
			desserts: [],
			pagesums:true,
			dialogs_bottom:false,
			dialogs_title:"",
			transfres_list:[],
			pages:null,
			tranfresbox:false,
			can_transfer:false,
			is_other:false,
			// 其他任务设置详情
			attach_pic: [],
			attach_file: [],
			sucessbtn:false,

		};
	},
	computed: {
		...mapState(["user_info"]),
	},
	// 回退修复
	beforeRouteLeave(to, from, next){
		if(to.path.indexOf("create_notice") !== -1){
			this.$router.push({name:"messages"});
		}else{
			next();
		}
	},
	mounted() {
		this.initData();
	},
	methods: {

		initData(){
			this.id = this.$route.params.id;
			this.getInfo(this.id);

			if(this.desserts.length > 5){
				if (this.pagination.rowsPerPage == null || this.pagination.totalItems == null){
					return this.pages = 0;
				}
				Math.ceil(this.pagination.totalItems / this.pagination.rowsPerPage);
			}else{
				this.pagesums = false;
			}

			let params = {
				notice_id :   this.id,
			};

			this.axios.get("/api/notification/notificationtransfres",{params}).then((res) => {
				this.desserts = res.data.data;

				this.desserts.forEach((v,i)=>{
					if(v.worktransfres.length !== 0){
						this.tranfresbox = true;
						return;
					}
				});
			}).catch((err) => {

			});
		},


		// 确认弹窗
		setTaskStatusDialogs(type){
			this.tips_type = type;

			if (type === 0){
				this.tips_show = true;
				this.tips_text = "确认将通知设为已办结?";
				this.tips_titile = "设置通知为已办结";
				this.tips_fn = this.setNoticeStatus;
				return;
			}

			this.tips_show = true;
			this.tips_text = "确认将通知废止?";
			this.tips_titile = "废止";
			this.tips_fn = this.setNoticeStatus;

		},

		// 废除通知 办结通知
		setNoticeStatus(type){

			let data  = {
				notice_id:this.notice.id,
				org_id:this.notice.org_id,
				group_id:this.notice.group_id,
				type:type
			};



			this.axios.post("/api/notification/postnotice",data).then((res) => {

				if (res.data.errcode == 1){
					this.$toast(res.data.errmsg, "error");
					return;
				}

				if(type == 0){
					this.$toast("通知已办结", "success");
				}
				if (type == 1){
					this.$toast("废止通知成功", "success");
				}

				this.$router.push({path:"/messages"});


			});

		},

		// 流转审批
		flowAudit() {
			this.$router.push({
				path: "/audit_task",
				query: {org_id: this.notice.org_id, id: this.notice.id, work_type: 1}
			});
		},

		checkTranfresMsg(id){
			this.dialogs_bottom = !this.dialogs_bottom;
			this.dialogs_title = "用户转交记录详情";


			// 拿到work_item_id,进行数据查询
			let  msg_list = this.desserts;
			msg_list.forEach((value,index)=>{
				if(value.id === id){
					value.worktransfres.forEach((v,i)=>{
						v.color = "#75615f";
					});
					this.transfres_list = value.worktransfres;
					return;
				}
			});

		},

		// 查看流转审批详情
		checkDetails(){
			this.$router.push({
				path:"/allrecord",
				query: {
					type:0,
					sid:this.notice.id
				}
			});
		},
		getInfo(id) {
			this.axios.get(`/api/notification/detail/${id}`).then((res) => {
				this.notice = res.data.data;

				// 检查通知是否已经完成
				let work_user =  this.notice.notification_items.filter(n => {
					return n.item_type == 0 && n.status != 2 ;
				});

				if(work_user.length < 1){
					this.sucessbtn = true;
				}


				let significance = ["普通","重要","非常重要"];
				this.notice.significance = significance[this.notice.significance];


				let build_attachments = this.structureAttachment(this.notice.attachments, this.notice.id);
				this.attach_pic = build_attachments.attach_pic;
				this.attach_file = build_attachments.attach_file;



				this.isLoading = false;
				// 获取任务创建人id
				this.getNoticeCreater();
			}).catch((Err) => {

			});
		},
		getNoticeCreater() {
			this.notice.notification_items.forEach(val => {

				if (val.item_type === 1 && val.user_id == this.user_info.id) {
					this.permission = true;

					if (val.status === 1) {
						this.audit.result = "审核中";
						this.show_text = true;
						this.audit.text = val.audit_text;
						this.can_transfer = true;
					}
					// 根据 status 来进行 audit 的 赋值   2 : 通过  3 ： 不通过
					if (val.status === 2) {

						// notification_items
						let arr = this.notice.notification_items.filter(function (v,i) {
							if (v.item_type == 2 && v.status == 2){
								return v;
							}
						});

						this.audit.result =`经${arr[0].user.name}批准下发`;
						this.show_text = true;
						this.audit.text = val.audit_text;
					}
					if (val.status === 3) {
						this.show_text = true;
						this.audit.result = "审核未通过";
						this.audit.text = val.audit_text;
					}

					this.notice.transfres_status = val.status;


				}
			});

			// 判断是否在审批中
			if (this.show_text &&  this.notice.transfres_status != 2){
				this.sucessbtn = true;
			}

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
.btn_task{
height: 30px;
border-color: #fff;
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
.details-text{
color:darkolivegreen;
overflow: hidden;
font-size: .9rem;
padding: 4px;
word-break: break-word;
word-break: break-all;
}
</style>
