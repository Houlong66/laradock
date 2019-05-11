<template>
  <div>
    <v-container
      v-if="!isLoading"
      class="pa-0"
    >
      <v-layout column>
        <v-flex class="px-3 py-4 mb-2 top-title">
          <p
            class="mb-0"
            style="text-align: center">可通过下面两种方式，发送审批申请</p>
        </v-flex>

        <v-flex class="px-4">
          <v-radio-group
            v-model="target_type"
            class="mt-2 mb-2"
            hide-details
            row>
            <v-radio
              label="微信转发"
              value="0"/>
            <v-radio
              label="系统内发送"
              value="1"/>
          </v-radio-group>
        </v-flex>

        <div class="px-4">
          <v-flex v-if="target_type==1">
            <treeselect
              v-model="targets"
              :multiple="true"
              :searchable="false"
              :options="targets_items"
              :max-height="200"
              :always-open="true"
              :default-expand-level="0"
              :z-index="0"
              class="mt-1 mb-2"
              no-options-text="机构中暂无可选成员"
              open-direction="below"
              placeholder="选择要发送的对象"
              value-consists-of="LEAF_PRIORITY"/>
          </v-flex>

          <div
            class="body-1 work-text grey--text text--darken-2 pa-2">
            <p
              v-if="target_type==0"
              class="ma-0">此方式将给微信好友发送审批消息，用户在微信聊天中点开消息即可进行审批</p>
            <p
              v-if="target_type==1"
              class="ma-0">此方式将给选定用户发送审批消息（用户会同时收到微信公众号消息提醒）</p>
          </div>
        </div>

        <div
          v-if="need_remark"
          class="py-3 mt-4 mb-5"
          style="border-top:solid 1px #eee; border-bottom:solid 1px #eee;">
          <v-flex
            class="px-4">
            <v-textarea
              v-model="notetext"
              :label="label"
              name="input-7-4"
              hide-details
              rows="2"
              @blur="scrollTo"
            />
          </v-flex>
        </div>
      </v-layout>

      <ShareTips :dialog_flag.sync="share_tips_dialog"/>
    </v-container>

    <component
      v-if="isLoading"
      :is="cView"/>

    <v-layout
      style="background:#fff; width:100%; position:fixed; bottom:0; left:0; z-index:999"
      justin-center>
      <v-flex
        xs12>
        <v-btn
          v-if="target_type==0"
          class="mb-1"
          block
          @click.prevent="share_tips_dialog = true">点击转发给他人审批
        </v-btn>
        <v-btn
          v-if="target_type==1"
          class="mb-1"
          block
          @click.prevent="submit"
        >确认发送
        </v-btn>
      </v-flex>
    </v-layout>
  </div>
</template>

<script>
// import the component
import Treeselect from "@riophae/vue-treeselect";
// import the styles
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import ShareTips from "../../Commons/ShareTips";

import {mapState} from "vuex";
import Loading from "../../Commons/Loading";

export default {
	components: {
		Treeselect,
		Loading,
		ShareTips
	},
	data() {
		return {
			target_type: "0",
			targets: [],
			targets_items: [],
			org_id: null,
			dialog: false,
			notetext:"",
			sumbit_nottext:"",
			isask:false,
			need_remark: true,
			isLoading:true,
			cView: "Loading",
			share_tips_dialog: false,
			label: "请填写备注说明（必填）",
		};
	},
	computed:{
		...mapState(["user_info"]),
		reports_text: {
			get () {
				let reg = new RegExp("<br/>","g");
				if (this.notetext){
					return this.notetext.replace(reg, "\n");
				}
			},
			set (newValue) {
				this.notetext = newValue;
			}
		}
	},
	watch:{
		notetext(n, o) {
			if(this.notetext){
				let reg = new RegExp("\n","g");
				let copy_report_text = this.notetext;
				this.sumbit_nottext = copy_report_text.replace(reg, "<br/>");
			}
		},
		sumbit_nottext(n,o){
			this.initWxShare(n);
		}
	},
	mounted() {
		this.initData();
	},
	methods: {
		initData(){
			// 微信分享配置
			this.initWxShare();
			this.org_id = this.$route.query.org_id;
			this.need_remark = !this.$route.query.remark_flag;
			this.getTargets();

			if (this.$route.query.work_type == 2){
				this.label = "转发说明";
			}
		},

		// 初始化微信分享
		initWxShare(n) {
			/*global host*/
			let url = null;
			// work_type = 0 => work, 1 => notice, 2 => ask
			if (this.$route.query.work_type == 0 || this.$route.query.work_type == 1) {

				url = `${window.host}/to_audit_task/${this.$route.query.id}?type=${this.$route.query.work_type}&note_text=${n}&work_send_id=${this.user_info.id}`;

			} else if (this.$route.query.work_type == 2) {
				this.isask = true;
				url = `${window.host}/ask_detail/${this.$route.query.id}?self_send=0&note_text=${n}&work_send_id=${this.user_info.id}`;
			}

			let title = "新的审批"; //分享的标题
			let share_img = `${window.host}/images/logo.jpeg`; //分享的图片
			let desc = this.notetext !== "" ? this.notetext : "请及时处理！"; //分享的描述信息

			let cb = () => {
				this.$toast("已成功发送给微信好友", "success");
			};
			this.wxShare(url, title, share_img, desc, cb);
		},

		getTargets() {

			let params = {
				ask:  this.isask,
				ask_id: this.$route.query.id
			};


			this.axios.get(`/api/org/users_by_depts/${this.org_id}`,{params}).then((res) => {
				let data = res.data.data;
				if(data.is_oneask){
					this.$delete(data,"is_oneask");
				}


				for (let index in res.data.data) {

					var temp_data = {
						id: index,
						label: index,
						children: []
					};

					for (let i = data[index].length - 1; i >= 0; i--) {

						if (data[index][i].id == this.$store.state.user_info.id) {

							data[index].splice(i, 1);

							continue;
						}

						data[index][i].label = data[index][i].name;
					}
					temp_data.children = data[index];
					if (temp_data.children.length == 0) {
						continue;
					}
					this.targets_items.push(temp_data);
				}
				this.isLoading = false;
			}).catch((Err) => {

			});
		},

		// 提交按钮
		submit() {
			// 解决vue-treeselect 报错问题，targets 必须要是数组
			// this.targets = this.targets.join(",");

			if (this.targets.length === 0) {
				this.$toast("请先选择用户！", "warning");
				return;
			}

			if(this.need_remark){

				if(this.isValidate(this.notetext,"check_html")){
					this.$toast("备注说明异常,请重新输入！", "warning");
					return;
				}

				if(this.notetext.trim().length === 0){
					this.$toast("请填写备注说明！", "warning");
					return;
				}

			}

			let targetsWithComma = this.targets.join(",");

			let temp_data = {
				id: this.$route.query.id,
				send_to_objs: targetsWithComma,
				if_send_wx_message: 1,
				notetext: this.sumbit_nottext,
			};


			let url = "";
			if (this.$route.query.work_type == 0) {

				url = "/api/task/add_flow_auditors";


			} else if (this.$route.query.work_type == 1) {

				url = "/api/notification/add_flow_auditors";

			} else if (this.$route.query.work_type == 2) {

				url = "/api/ask/add_flow_auditors";
			}

			return this.axios.post(url, temp_data).then((res) => {
				if (res.data.errcode === 0) {
					this.$toast("发送成功!", "success");
					this.$router.push("/works/0");
				} else {
					this.$toast(res.data.errmsg, "error");
				}
			});

		}
	}
};
</script>

<style scoped>

</style>
