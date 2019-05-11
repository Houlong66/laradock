<template>
  <v-form
    ref="form"
    v-model="valid"
    class="create-form"
    style="background-color: white;height: 100%;"
    lazy-validation>

    <v-select
      v-if="organization_items && organization_items.length !== 1 "
      v-model="organization"
      :items="organization_items"
      box
      background-color="white"
      label="选择机构"
    />

    <v-text-field
      v-model="title"
      :rules="title_rules"
      :counter="20"
      background-color="white"
      box
      label="请示标题"
      required
      @blur="scrollTo"
    />
    <v-textarea
      v-model="body"
      box
      background-color="white"
      auto-grow
      label="请示内容（选填）"
      rows="3"
      @blur="scrollTo"
    />
    <v-select
      v-model="ask_type"
      :items="ask_type_items"
      :rules="[v => !!v || '请选择请示类型']"
      box
      background-color="white"
      label="请示类型"
      required
    />


    <!--  是否其他请示详情 -->
    <div class="report-setting-box pt-2 pb-2 mb-2">
      <v-layout>
        <v-flex
          xs6
          class="align-self-center">
          <v-switch
            v-model="is_other"
            label="其他设置"
            value="1"
            hide-details
            height="36"
            class="mt-0"
          />
        </v-flex>
      </v-layout>
    </div>


    <!--其他请示详情-->
    <div
      v-if="is_other"
    >
      <div class="white report-setting-box py-2 mb-4">
        <v-layout class="px-2">
          <v-flex
            xs6
            class="align-self-center">
            <v-switch
              v-model="is_upload"
              label="上传附件"
              color="red"
              value="1"
              hide-details
              height="36"
              class="mt-0"
            />
          </v-flex>
          <v-flex
            v-if="is_upload == 1"
            xs6
            class="align-self-center">
            <span>单个文件建议小于5M</span>
          </v-flex>
        </v-layout>

        <files-uploader
          v-if="is_upload == 1"
          :empty-file-list.sync="emptyFileList"
          :file-ready.sync="fileReady"
          :uploaded-files.sync="uploadedFiles"
          :upload-query="uploadQuery"
        />
      </div>

      <!--上传url地址-->
      <div
        class="white report-setting-box px-2 py-2">
        <v-layout>
          <v-flex
            xs12
            class="align-self-center">
            <v-switch
              v-model="is_uploadUrl"
              label="附加外部网址"
              color="red"
              value="1"
              hide-details
              height="36"
              class="mt-0"
            />
          </v-flex>
        </v-layout>
      </div>

      <div v-if="is_uploadUrl == 1">
        <UploadUrl
          :url_id_list.sync="urlIdList"
          :works_id="null"
          works_type="3"
        />
      </div>
    </div>


    <v-layout
      class="pt-3"
      justify-space-between>
      <v-flex xs12>
        <v-btn
          class="submit-btn mx-0"
          @click="submit"
        >
          确认创建
        </v-btn>
      </v-flex>
    </v-layout>
  </v-form>
</template>

<script>
// import the component
import Treeselect from "@riophae/vue-treeselect";
// import the styles
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import FilesUploader from "../FilesUploader";
import UploadUrl from "../UploadUrl";
import {mapState} from "vuex";


export default {
	components: {
		Treeselect,
		FilesUploader,
		UploadUrl
	},
	data: () => ({
		valid: false,
		title: "", // 标题
		title_rules: [
			v => !!v || "请填写请示标题",
			v => (v && v.length <= 20) || "请示标题必须不超过二十个字"
		],
		body: "", // 内容
		body_rules: [
			v => !!v || "请填写请示内容"
		],
		organization: null,
		organization_items: [],

		// 请示类型的>>>>>>>>>>>>>>>>>
		ask_type: 1,
		ask_type_items: [
			{
				value: 1,
				text: "工作"
			},
			{
				value: 2,
				text: "请假"
			},
			{
				value: 3,
				text: "用车"
			},

		],
		// >>>>>>>>>>>>>>>>>>>>>>>

		is_uploadUrl: "0",
		urlIdList: [],

		// 附件上传相关
		// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
		is_upload: "0",
		uploadedFiles: [],
		emptyFileList: true,
		fileReady: false,
		uploadQuery: { works_type: 3, works_item_id: 0 }, // works_type工作的类型，1任务，2通知，3附件;  type,0任务附件，1任务上报附件
		is_other:false,
	}),
	computed:{
		...mapState(["selected_org"]),
	},
	mounted: function () {
		this.getOrganizations();
	},

	methods: {
		submit () {
			if (this.$refs.form.validate()) {
				// 是否已经输入了通知内容
				// if(this.body.trim().length === 0){
				// 	this.$toast("请输入请示内容", "error");
				// 	return;
				// }

				if(this.title.trim().length === 0){
					this.$toast("请输入请示标题", "error");
					return;
				}

				// 判断是否有待上传的附件
				let attachment = 0;
				if(this.is_upload==="1"){
					// 判断附件是否全部上传成功
					if(this.fileReady){
						attachment = this.getAttachmentIdStr(this.uploadedFiles);
					}else{
						// 有未完成上传的附件
						this.$toast("请先上传附件", "error");
						return;
					}
				}

				// 检查是否存在危险行为
				let  strContent = "";
				if(this.body){
					if(this.isValidate(this.body,"check_html")){
						this.$toast("您输入的请示内容不合法,请重新输入","error");
						return ;
					}

					// 对内容进行转义
					let reg = new RegExp("\n","g");
					strContent = this.body.replace(reg, "<br/>");
				}


				var temp_data = {
					org_id: this.organization,
					title: this.title,
					desc: strContent.trim(),
					ask_type: this.ask_type,
					attachment: attachment
				};

				this.axios.post("/api/ask/store", temp_data).then((res) => {
					this.updateWorksId(res.data.data.id);
					if(res.data.errcode===0){
						this.$toast(res.data.data.text,"success",2000);
						this.$router.push({path:"/audit_task", query:{
							org_id: this.organization,
							id: res.data.data.id,
							work_type: 2,
							remark_flag: true }
						});
					}else{
						this.$toast(res.data.errmsg,"error",2000);
					}
				}).catch((Err) => {
				});
			}
		},

		updateWorksId(works_id){
			let data = {
				works_id: works_id,
				id: this.urlIdList
			};
			this.axios.post("/api/task/updateTaskId", data).then((res) =>{

			});
		},

		getOrganizations () {
			this.axios.get("/api/my/org").then((res) => {
				this.organization_items = res.data.data;

				// 默认选中当前的机构,在多机构情况下
				this.selected_org_name  = this.selected_org.name;

				this.organization_items.forEach(function(value, index){
					value.text = value.name;
					value.value = value.id;
				});

				// 选中当前机构
				this.organization = this.selected_org.id;
			});
		}
	},
};
</script>

<style scoped>
.create-form {
background-color: #f6f6f6!important;
padding: 1rem;
}
.submit-btn{
width:100%;
}
</style>
