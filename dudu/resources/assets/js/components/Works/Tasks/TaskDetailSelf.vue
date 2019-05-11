<template>
  <div>
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
      class="px-0 pb-2  border-be">
      <v-layout
        column
        class="px-3">
        <v-flex class="pb-1">
          <div>
            <p class="title pb-2 mb-1 border-b" >{{ task.title }}</p>
          </div>
        </v-flex>

        <v-flex class="caption grey--text text--darken-1 mb-4 msg-font">
          <p class="mb-0">所属机构：{{ task.org.name }}</p>
          <p class="mb-0">工作群组：{{ task.group.name }}</p>
          <p class="mb-0">发送时间：{{ task.send_time !== null ? task.send_time : "未发放" }}</p>
        </v-flex>


        <v-flex class="mb-4">
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
              v-html="task.desc"
            />
            <p
              v-else
              class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
            >无内容
            </p>
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
          <!--&gt;{{ task.significance }}</p>-->
          <!--</v-flex>-->



          <v-flex
            v-if="task.attachments.length !== 0 "
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
            v-if="task.url.length > 0"
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
                v-for="(item,index) in task.url"
                :key="index"
                class="list ma-2">
                <div class="hid">标题: {{ item.url_title }}</div>
                <div class="hid">链接: <a :href="item.url_path">{{ item.url_path }}</a></div>
              </div>
            </div>
          </v-flex>

          <v-flex
            v-if="task.deadline"
            class="mb-2 py-2 border-b"
          >
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-jiezhishijian"/>
              完成时限
            </label>
            <p class="body-1 grey--text text--darken-2 pl-2 pt-1 mb-0">{{ task.deadline }}</p>
          </v-flex>


        </v-flex>

        <!--其他任务设置   end-->


        <v-flex
          class="py-2 mb-4">
          <label

            class="subheading"
          >
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-tongji"/>
            完成情况
          </label>

          <!--任务详情-->
          <task-tab :task="task"/>
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
                  v-if="props.item.worktransfres.length !== 0 "
                  @click="checkTranfresMsg( props.item.id)"
                >
                  <td >{{ props.item.worktransfres[0].from_user.name }}</td>
                  <td
                    v-if="props.item.dept_id !== 0"
                  >{{ props.item.dept.name }}</td>

                  <td >{{ props.item.count }}</td>
                  <td >  <v-icon class="iconfont dudu-you1 "/> </td>
                </tr>
              </template>
            </v-data-table>

            <!--分页-->
            <div
              v-if="pagesums"
              class="text-xs-center pt-2"
            >
              <v-pagination
                v-model="pagination.page"
                :length="pages()"/>
            </div>
          </div>
        </v-flex>



        <v-layout >
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
      :msg_list="task.msg_boards"
      :user_list="task.task_items"
      :msg_sender="user_info.id"
      prefix="任务"
      @submit="submitDiscuss"/>

    <component
      v-if="isLoading"
      :is="cView"
    />

    <!--提示框-->
    <Dialogs
      :dialog.sync="tips_show"
      :title="tips_title"
      :text="tips_text"
      :fn="tips_fn"
      :agreed="tips_agreed"
      :types="tips_type"
    />

  </div>
</template>

<script>
import TaskTab from "./TaskTab";
import Loading from "../../Commons/Loading";
import MsgBoard from "../MsgBoard";
import {mapState} from "vuex";
import Dialogs from  "../../Commons/Dialogs";
import DeptItem from "../../Organizations/Popmodal/DeptItem";
import FilesDownloader from "../FilesDownloader";

export default {
	name: "TaskDetailSelf",
	inject: ["reload"],
	components: {
		DeptItem,
		TaskTab,
		Loading,
		MsgBoard,
		Dialogs,
		FilesDownloader
	},
	data() {
		return {
			id: null,
			active: 0,
			task: {}, // 任务详情
			status_text: ["未发送", "未签收", "未上报", "已上报", "已办结"], // 状态文案
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
			audit: {}, // 审批人
			isLoading: true,
			cView: "Loading",
			// 只有任务发布者允许访问该页面
			permission: false,
			show_text: false,
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
				{
					text: "转交次数",
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
			transfres_list:[],
			dialogs_title:"",
			pages:null,
			tranfresbox:false,
			can_transfer:false,
			is_other:false,
			// 其他任务设置详情
			attach_pic: [],
			attach_file: [],
			// 提示框参数
			tips_show:false,
			tips_title:"",
			tips_text:"",
			tips_fn:null,
			tips_type:null,
			tips_agreed:null,
			// -------------
			sucessbtn:false,
		};
	},
	computed: {
		...mapState(["user_info"]),
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
				taskid: this.id
			};

			// 获取转交记录
			this.axios.get("/api/task/worktransfres",{params}).then((res) => {
				this.desserts = res.data.data;

				this.desserts.forEach((v,i)=>{
					if(v.worktransfres.length !== 0){
						this.tranfresbox = true;
						return;
					}
				});

				// 当是部门任务的时候
				if (this.desserts[0].dept_id !== 0){
					this.headers.splice(1,0, {text: "所属部门", sortable: false});
				}
			}).catch((err) => {

			});
		},
		// 确认弹窗
		setTaskStatusDialogs(type){
			this.tips_type = type;

			if (type === 0){
				this.tips_show = true;
				this.tips_text = "确认将任务设为已办结?";
				this.tips_title = "设置任务为已办结";
				this.tips_fn = this.setTaskStatus;
				return;
			}

			this.tips_show = true;
			this.tips_text = "确认将任务废止?";
			this.tips_title = "废止";
			this.tips_fn = this.setTaskStatus;

		},

		// 办结任务 废止
		setTaskStatus(type){

			let data  = {
				task_id:this.task.id,
				org_id:this.task.org_id,
				group_id:this.task.group_id,
				type:type
			};

			this.axios.post("/api/task/postwork",data).then((res) => {

				if (res.data.errcode == 1){
					this.$toast(res.data.errmsg, "error");
					return;
				}

				if (type === 0) {
					this.$toast("办结任务成功!", "success");
				}else{
					this.$toast("废止任务成功!", "success");
					this.$router.push({path:"/works/0"});
				}

				this.reload();
			});
		},


		// 流转审批
		flowAudit() {
			this.$router.push({
				path: "/audit_task",
				query: {org_id: this.task.org_id, id: this.task.id, work_type: 0}
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
					type:1,
					sid:this.task.id
				}
			});
		},

		getInfo(id) {
			this.axios.get(`/api/task/detail/${id}`).then((res) => {
				this.task = res.data.data;

				this.isLoading = false;

				// 检查任务是否已经完成
				let work_user =  this.task.task_items.filter(n => {
					return n.item_type == 0 && n.status != 4 ;
				});

				if(work_user.length < 1){
					this.sucessbtn = true;
				}

				let significance = ["普通","重要","非常重要"];

				this.task.significance = significance[this.task.significance];


				let build_attachments = this.structureAttachment(this.task.attachments, this.task.id);
				this.attach_pic = build_attachments.attach_pic;
				this.attach_file = build_attachments.attach_file;

				// 获取任务创建人id
				this.getTaskCreater();
			}).catch((Err) => {

			});
		},

		getTaskCreater() {
			this.task.task_items.forEach(val => {

				// 当前用户是发送人的时候
				if (val.user_id == this.user_info.id && val.item_type === 1) {

					this.permission = true;

					if (val.status === 1) {
						this.audit.result = "审核中";
						this.show_text = true;
						this.audit.text = val.audit_text;
						this.can_transfer = true;
					}

					// 根据 status 来进行 audit 的 赋值   2 : 通过  3 ： 不通过
					if (val.status === 2) {

						// 在有审批记录
						let arr = this.task.task_items.filter(function (v,i) {
							if (v.item_type == 2 && v.status == 2){
								return v;
							}
						});

						this.audit.result = `经${arr[0].user.name}批准下发`;
						this.show_text = true;
						this.audit.text = val.audit_text;


					}

					if (val.status === 3) {
						this.show_text = true;
						this.audit.result = "审核未通过";
						this.audit.text = val.audit_text;
					}


					this.task.transfres_status = val.status;
				}
			});

			if (this.show_text && this.task.transfres_status != 2){
				this.sucessbtn = true;
			}
		},
		submitDiscuss(obj) {

			// 是否已经输入了任务内容
			if(obj.content.trim().length === 0){
				this.$toast("请输入内容", "error");
				return;
			}

			let postData = {
				foreign_id: this.id,
				type: 1,
				content: obj.content.trim(),
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
