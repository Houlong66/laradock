<template>
  <div>

    <v-btn
      fab
      small
      dark
      color="blue"
      class="work-btn"
      @click="dialog = true">
      <span class="font-weight-bold">客服</span>

    </v-btn>


    <!--下弹窗-->
    <v-layout 
      row 
      justify-center>
      <v-dialog 
        v-model="dialog" 
        fullscreen 
        hide-overlay 
        transition="dialog-bottom-transition">
        <v-card>
          <v-toolbar 
            dark 
            color="primary">
            <v-btn 
              icon 
              dark 
              @click="dialog = false">
              <v-icon class="iconfont dudu-guanbi1"/>
            </v-btn>
            <v-toolbar-title class="subheading">问题反馈</v-toolbar-title>
            <v-toolbar-items style="position: absolute; right:0; top:0;">
              <v-btn
                dark
                flat
                class="font-weight-bold"
                @click="submitFeedback()">确定
              </v-btn>
            </v-toolbar-items>
          </v-toolbar>

          <v-card-text>

            <v-form
              ref="form"
              lazy-validation>
              <v-text-field
                v-model="title"
                :rules="title_rules"
                :counter="20"
                background-color="white"
                box
                label="反馈标题"
                required
                @blur="scrollTo"
              />
              <v-textarea
                v-model="content"
                :rules="body_rules"
                box
                background-color="white"
                auto-grow
                label="反馈内容"
                rows="1"
                required
                @blur="scrollTo"
              />
            </v-form>

            <div class="report-setting-box px-2 pt-2 pb-2">
              <v-layout>
                <v-flex
                  xs5
                  class="align-self-center">
                  <v-switch
                    v-model="is_upload"
                    label="上传截图"
                    color="primary"
                    value="1"
                    hide-details
                    height="36"
                    class="mt-0"
                  />
                </v-flex>
                <v-flex
                  v-if="is_upload == 1"
                  xs7
                  class="align-self-center">
                  <span>单个文件建议小于5M</span>
                </v-flex>
              </v-layout>
            </div>
            <div
              v-if="is_upload == 1"
              class="pa-2"
              style="background:white;">
              <files-uploader
                :empty-file-list.sync="emptyFileList"
                :file-ready.sync="fileReady"
                :uploaded-files.sync="uploadedFiles"
                :upload-query="uploadQuery"
              />
            </div>
          </v-card-text>
        </v-card>
      </v-dialog>
    </v-layout>


  </div>
</template>


<script>
import FilesUploader from "../Works/FilesUploader";

export default {
	name: "Feedback",
	components: {
		FilesUploader
	},
	data() {
		return {
			dialog: false,
			title: "",
			title_rules: [
				v => !!v || "请填写任务标题",
				v => (v && v.length <= 20) || "任务标题必须不超过二十个字"
			],
			content: "",
			body_rules: [
				v => !!v || "请填写任务内容"
			],
			// 文件上传相关
			// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
			is_upload: "0",
			uploadedFiles: [],
			emptyFileList: true,
			fileReady: false,
			uploadQuery: {works_type: 4, works_item_id: 0}, // works_type工作的类型，1任务，2通知，3附件，4反馈;  type,0任务附件，1任务上报附件
		};
	},
	methods: {
		submitFeedback() {
			if (this.$refs.form.validate()) {
				if (this.title.trim().length === 0){
					this.$toast("请输入反馈标题", "error");
					return;
				}

				if (this.content.trim().length === 0){
					this.$toast("请输入内容", "error");
					return;
				}



				// 判断是否有待上传的附件
				let attachment = 0;
				if (this.is_upload === "1") {
					// 判断附件是否全部上传成功
					if (this.fileReady) {
						attachment = this.getAttachmentIdStr(this.uploadedFiles);
					} else {
						// 有未完成上传的附件
						this.$toast("请先上传附件", "error");
						return;
					}
				}

				const feedbackData = {
					"title": this.title,
					"content": this.content,
					"attachments": attachment
				};

				this.axios.post("/api/feedback/create", feedbackData).then((res) => {
					if (res.data.errcode === 0) {
						this.$toast("提交成功，感谢您的反馈", "success");
						this.dialog = false;
					}
				}).catch((Err) => {
					// console.log(Err.errmsg);
				});
			}
		}
	}
};
</script>

<style scoped>
  .work-btn{

  }

</style>
