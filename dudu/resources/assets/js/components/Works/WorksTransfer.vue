<template>
  <div>
    <v-dialog
      v-model="dialog"
      :fullscreen="true"
      scrollable
      transition="dialog-bottom-transition"
      max-width="100%">
      <v-card>
        <v-toolbar
          dark
          color="primary">
          <v-btn
            icon
            dark
            @click="close">
            <v-icon 
              small 
              class="iconfont dudu-guanbi1"/>
          </v-btn>
          <v-toolbar-title class="subheading">选择发放对象</v-toolbar-title>
          <v-spacer/>
        </v-toolbar>


        <v-card-text 
          class="pa-0" 
          style="height: 100vh;">
          <v-container class="mb-0 py-0">
            <v-switch
              v-model="containSons"
              class="mb-0 pb-0"
              label="包含子机构成员"
              hide-details
              @change="changeFlag"/>
          </v-container>

          <v-container
            fluid>
            <treeselect
              v-model="targets"
              :disable-branch-nodes="true"
              :options="targets_items"
              :searchable="false"
              :show-count="true"
              :max-height="200"
              :always-open="true"
              :default-expand-level="0"
              :z-index="0"
              no-options-text="机构中暂无可选成员"
              placeholder="请选择转发对象"/>
          </v-container>

          <v-container class="pt-0">
            <v-textarea
              v-model="remark"
              label="转交说明（必填）"
              rows="1"
              @blur="scrollTo"
            />
          </v-container>

          <v-layout style="width:100%; position:fixed; bottom:0; left;">
            <v-flex>
              <v-btn
                :disabled="btnFlag"
                class="white mb-1"
                block
                @click="confirmTransfer('sys')">确认转交
              </v-btn>
            </v-flex>
            <v-flex>
              <v-btn
                v-btn-control="wxTransfer"
                :disabled="btnFlag"
                class="white mb-1"
                block>微信转交
              </v-btn>
            </v-flex>
          </v-layout>
        </v-card-text>
      </v-card>
    </v-dialog>

    <ShareTips :dialog_flag.sync="qrDialog"/>
  </div>
</template>

<script>
// import the component
import { mapState } from "vuex";
import Treeselect from "@riophae/vue-treeselect";
import ShareTips from "../Commons/ShareTips";
// import the styles
import "@riophae/vue-treeselect/dist/vue-treeselect.css";

export default {
	name: "WorksTransfer",
	components: {
		Treeselect,
		ShareTips
	},
	props: {
		dialog: {
			type: Boolean,
			default: false
		},
		work: {
			type: Object,
			default: () => {
			}
		},
		work_item_id: {
			type: Number,
			default: 0
		},
		work_type: {
			type: String,
			default: "task"
		},
		dept_id: {
			type: Number,
			default: 0
		}
	},
	data() {
		return {
			remark: "",
			isDisabled: true,
			targets: null, // 邀请对象
			targets_items: [], // 候选邀请对象
			filter_array: [],
			containSons: false,
			qrDialog: false
		};
	},
	computed: {
		...mapState(["selected_org"]),
		btnFlag() {
			if(this.targets){
				if(this.remark.trim().length !== 0){
					return false;
				}
			}
			return true;
		}
	},
	mounted() {
		// 将当前任务或通知的发送人、接收人、审核人加入过滤数组，转交对象将排除这些用户
		this.initFilter();
		// 获取可转交的用户
		this.getOrgUser();
		// 微信分享配置
		this.initWxShare();
	},
	methods: {
		close() {
			this.$emit("update:dialog", false);
		},
		initFilter() {
			let list = null;
			if (this.work_type === "task") {
				list = this.work.task_items;
			} else {
				list = this.work.notification_items;
			}
			list.forEach((value, i) => {
				if ([0, 1].indexOf(value.item_type) !== -1) {
					this.filter_array.push(value.user_id);
				}
			});
		},
		getOrgUser() {
			let data = {
				org_id: this.selected_org.id
			};
			this.axios.get(`/api/user/org/${this.work.org_id}/flag/${this.containSons}`,{params:data}).then((res) => {
				let data = res.data.data;
				for (let index in res.data.data) {
					var temp_data = {
						id: index,
						label: index,
						children: []
					};
					var temp_array = [];
					// 判断该用户是否已存在或者是否已接收到了任务或通知
					data[index].forEach((value, i) => {
						if (this.filter_array.indexOf(value.id) === -1) {
							value.label = value.name;
							temp_array.push(value);
							this.filter_array.push(value.id);
						}
					});

					if (temp_array.length !== 0) {
						temp_data.children = temp_array;
						this.targets_items.push(temp_data);
					}
				}
			}).catch((err) => {

			});
		},
		changeFlag() {
			this.targets = null;
			this.targets_items = [];
			this.filter_array = [];
			this.initFilter();
			this.getOrgUser();
		},
		// 确认转交
		confirmTransfer(way) {
			if (this.targets === null || this.targets === undefined) {
				this.$toast("请先选择转交对象", "error");
				return false;
			}

			// 检查是否存在危险行为
			if(this.isValidate(this.remark,"check_html")){
				this.$toast("转交说明不合法，请重新输入","error");
				return ;
			}

			// 对内容进行转义
			let reg = new RegExp("\n","g");
			let remarks = this.remark.replace(reg,"<br/>");




			let data = {
				work_id: this.work.id,
				work_item_id:  this.work_item_id ,
				dept_id: this.dept_id,
				to_user_id: this.targets,
				remark: remarks,
				way: way,
			};


			this.axios.post(`/api/${this.work_type}/transfer`, data).then((res) => {
				if (res.data.errcode === 0) {
					this.$toast(res.data.data, "success");
					this.$emit("update:dialog", false);
					let flag = this.work_type === "task" ? 0 : 1;
					this.$router.push(`/works/${flag}`);
				} else {
					this.$toast(res.data.errmsg, "error");
				}
			}).catch((err) => {
				this.$toast(err.data.errmsg, "error");
			});

		},
		wxTransfer() {
			if (this.targets !== null && this.targets !== undefined) {
				this.qrDialog = true;
			} else {
				this.$toast("请先选择转交对象", "error");
			}
		},
		// 初始化微信分享
		initWxShare() {
			/*global host*/
			let url = null;
			if (this.work_type === "task") {
				url = `${window.host}/task_detail/${this.work.id}?dept_id=${this.dept_id}`;
			} else {
				url = `${window.host}/notice_detail/${this.work.id}`;
			}

			let title = "您收到一条新的任务"; //分享的标题
			let share_img =  window.host+"/images/logo.jpeg"; //分享的图片
			let desc = "请及时处理！"; //分享的描述信息
			let cb = () => {
				if (this.targets !== null && this.targets !== undefined) {
					this.confirmTransfer("wx");
				} else {
					this.$toast("请先选择转交对象", "error");
					this.$emit("update:dialog", true);
				}
			};
			this.wxShare(url, title, share_img, desc, cb);
		},
	}
};
</script>

<style scoped>

</style>