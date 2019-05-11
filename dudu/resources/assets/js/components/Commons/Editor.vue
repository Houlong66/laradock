<template>
  <div>
    <!--    <v-flex>-->
    <!--      <v-switch-->
    <!--        v-model="switchphone"-->
    <!--        label="选择手机模拟器查看效果"-->
    <!--      />-->
    <!--      <v-radio-group-->
    <!--        v-if="switchphone"-->
    <!--        v-model="row"-->
    <!--        class="mt-0 pt-0"-->
    <!--        row>-->
    <!--        <v-radio-->
    <!--          label="iPone 6/7/8 Plus"-->
    <!--          value="1"/>-->
    <!--        <v-radio-->
    <!--          label="iPone 4"-->
    <!--          value="2"/>-->
    <!--      </v-radio-group>-->
    <!--    </v-flex>-->

    <quill-editor
      ref="myQuillEditor"
      v-model="desc"
      :options="editorOption"
      :class="editorclass"
      @change="onChange"
    />

    <!--裁剪-->
    <crop-upload
      v-model="showCrop"
      :width="width"
      :height="height"
      :upload-url="uploadUrl"
      @uploadSuccess="cropuploadSuccess"
    />
  </div>
</template>

<script>
import myUpload from "vue-image-crop-upload";
import CropUpload from "./CropUpload";

var toolbarOptions = [
	["bold", "italic", "underline", "strike"],        // toggled buttons
	["blockquote", "code-block"],

	[{ "header": 1 }, { "header": 2 }],               // custom button values
	[{ "list": "ordered"}, { "list": "bullet" }],
	[{ "script": "sub"}, { "script": "super" }],      // superscript/subscript
	[{ "indent": "-1"}, { "indent": "+1" }],          // outdent/indent
	[{ "direction": "rtl" }],                         // text direction

	[{ "size": ["small", false, "large", "huge"] }],  // custom dropdown
	[{ "header": [1, 2, 3, 4, 5, 6, false] }],

	[{ "color": [] }, { "background": [] }],          // dropdown with defaults from theme
	[{ "font": [] }],
	[{ "align": [] }],

	["clean"],                               // remove formatting button
	["link", "image"]

];

export default {
	name: "Editor",
	components:{
		myUpload,
		CropUpload,
	},
	props:{
		fatherdesc:{
			type:String,
			default:null
		}
	},
	data (){
		return {
			editorOption:{
				modules: {
					toolbar: toolbarOptions,
					// 新增下面
					imageResize: {
						//调整大小组件。
						displayStyles: {
							backgroundColor: "black",
							border: "none",
							color: "white"
						},
						modules: [ "Resize","DisplaySize", "Toolbar" ]
					}
				}
			},
			desc:"",
			params: {},
			headers: {
				smail: "*_~"
			},
			showCrop:false,
			width:200,
			height:200,
			uploadUrl:"/api/file/upload",
			imgDataUrl: "", // the datebase64 url of created image
			switchphone:"",
			editorclass:"editor",
			row:null,
		};
	},
	computed: {
		editor() {
			return this.$refs.myQuillEditor.quill;
		}
	},
	watch:{
		row(n,o){
			this.selectRow(n);
		},
		// switchphone(n){
		// 	if(!n){
		// 		this.editorclass = "editor";
		// 	}
		// }
	},
	beforeMount(){
		if (this.fatherdesc){
			this.desc = this.fatherdesc;
		}
	},
	mounted (){
		if (this.$refs.myQuillEditor) {
			// 截断图片点击
			this.$refs.myQuillEditor.quill.getModule("toolbar").addHandler("image", this.imgClick);
		}
	},
	methods:{
		// selectRow(n){
		// 	switch (parseInt(n)) {
		// 	case 1:
		// 		this.editorclass = "editor1";
		// 		break;
		// 	case 2:
		// 		this.editorclass = "editor2";
		// 		break;
		// 	}
		// },
		imgClick(){
			// 获取光标位置



			if (this.showCrop){
				this.showCrop  =  true;
				return false ;
			}

			var input = document.createElement("input");
			input.type = "file";
			input.accept = "image/jpeg,image/png,image/jpg,image/gif";
			input.onchange = this.onFileChange;
			input.click();
			this.show  =  true;
		},

		// 选择图片后上传，无需截取
		onFileChange(e){

			let fileInput = e.target;

			if (fileInput.files.length == 0) {
				return;
			}

			if (fileInput.files[0].size > 1024 * 1024 ) {
				this.$toast("图片过大,请使用小于1Mb的图片", "error");
				return;
			}

			if (!this.uploadUrl) {
				this.$toast("无文件上传路径", "error");
				return;
			}

			// 添加数据,模拟表单
			let data = new FormData;
			data.append("file", fileInput.files[0], fileInput.files[0].name);
			data.append("works_type", "5");
			data.append("works_item_id", "0");

			this.axios.post(this.uploadUrl,data).then((res) => {
				let params = {
					fileid:res.data
				};
				this.axios.get("/api/file/getimg",{params}).then((res) =>{
					let img = res.data.data;
					// let imgtext = `<img id=${img.id} width="200" src=${window.host +"/" + img.total_path}   />`;
					this.editor.insertEmbed(this.editor.getSelection().index, "image", window.host +"/" + img.total_path);
				});
			});
		},

		// 监听修改事件
		onChange(value){
			this.$emit("getvalues",value);
		},

		// 这里是截取完的返回事件
		cropuploadSuccess(data){
			this.desc = this.desc + data;
		}
	}
};
</script>

<style scoped>

.editor{
  min-height: 400px !important;
  width: 100%;
  padding-bottom: 30px;
}
.editor1{
  min-height: 400px !important;
  width: 414px;
  padding-bottom: 30px;
}

.editor2{
  min-height: 400px !important;
  width: 320px;
  padding-bottom: 30px;
}


 .editor >>> .ql-container , .editor2 >>> .ql-container , .editor1 >>> .ql-container {
     min-height: 400px;
  }

</style>
