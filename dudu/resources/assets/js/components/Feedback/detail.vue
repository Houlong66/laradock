<template>
  <div>

    <v-layout
      v-if="feedback !== {}"
      column
      class="px-3 pt-4">
      <v-btn
        class="mb-4"
        @click="toList()">返回反馈列表</v-btn>
      <v-flex class="pb-1">
        <div>
          <p class="title pb-2 mb-1 border-b">{{ feedback.title }}</p>
        </div>
      </v-flex>
      <v-flex class="caption grey--text text--darken-1 mb-4">
        <p class="mb-0">反馈人： {{ feedback.user.name }}</p>
        <p class="mb-0">目前状态： {{ feedback.status }}</p>
        <p class="mb-0">联系方式： {{ feedback.user.tel===null?"未填写":feedback.user.tel }}</p>
        <p class="mb-0">反馈时间： {{ feedback.created_at }}</p>
      </v-flex>
      <v-flex class="mb-4">
        <div>
          <label class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-shuoming-copy-copy"/>
            反馈内容
          </label>
          <p class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0">{{ feedback.content }}</p>
        </div>
      </v-flex>


      <v-flex
        v-if="feedback.attachments.length !== 0"
        class="mb-4">
        <label class="subheading">
          <v-icon
            size="20"
            color="grey"
            class="iconfont dudu-fujian"/>
          反馈图片
        </label>

        <files-downloader
          :pic.sync="attach_pic"
          :file.sync="attach_file"
          :downloader-type="1"
          work-type="feedback"
        />
      </v-flex>

      <!--反馈按钮-->
      <v-btn
        :disabled="disabled"
        color="success"
        @click="openDialogs"
      >{{ btnText }}</v-btn>

    </v-layout>


    <!--模態框-->
    <Dialogs
      :title="title"
      :text="text"
      :dialog.sync="dialog"
      :fn="repairSuccess"
    />

  </div>
</template>


<script>
import FilesDownloader from "../Works/FilesDownloader";
import Dialogs from "../Commons/Dialogs";


export default {
	name: "FeedbackDetail",
	inject: ["reload"],
	components: {
		FilesDownloader,
		Dialogs
	},
	data() {
		return {
			feedback: {
				user: {
					name: ""
				},
				attachments: []
			},
			// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
			attach_pic: [],
			attach_file: [],
			disabled:false,
			btnText:"",
			dialog:false,
			title:"",
			text:"",
		};
	},
	watch:{
	},
	mounted() {
		this.init();
	},

	update () {
	},

	methods: {
		init() {
			this.axios.get(`/api/feedback/detail/${this.$route.params.id}`).then((res) => {
				this.feedback = res.data.data;
				this.feedback.status == 0 ? (this.feedback.status = "未修复",this.btnText = "已修复"): (this.feedback.status = "已修复",this.btnText = "已修复完成",this.disabled = !this.disabled);
				// 处理关联的附件，构建变量
				let build_attachments = this.structureAttachment(this.feedback.attachments);
				this.attach_pic = build_attachments.attach_pic;
				this.attach_file = build_attachments.attach_file;
			}).catch((err) => {

			});
		},
		toList() {
			this.$router.push("/feedback/list");
		},
		//消息弹窗
		openDialogs (){
			this.title = "确认已完成此次修复？";
			this.text = "确认完成修复之后会更改任务状态，并且向提交用户发送微信信息，回复此次反馈已经完成！";
			this.dialog = true;
		},

		//修复成功按钮
		repairSuccess (){

			let id = this.feedback.id;
			let postData = {
				user_id : this.feedback.user_id,
				feedback_title : this.feedback.title
			};
			this.axios.post(`/api/feedback/update/${id}`,postData).then((res)=>{


				if(res.data.errcode === 0){
					this.$toast("更改状态成功","success");
					this.reload();
					return ;
				}
				this.$toast(res.data.errmsg,"warning");
			}).catch((err)=>{
			});
		}
	}
};
</script>

<style scoped>
</style>
