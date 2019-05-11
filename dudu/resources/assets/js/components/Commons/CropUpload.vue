<template>
  <vue-crop
    v-model="show"
    :width="width"
    :height="height"
    :params="params"
    :url="uploadUrl"
    img-format="png"
    field="file"
    @crop-success="cropSuccess"
    @crop-upload-success="uploadSuccess"/>
</template>

<script>
import VueCrop from "vue-image-crop-upload/upload-2.vue";
export default{
	name:"GropUpload",
	components: {
		VueCrop
	},
	props:{
		width:{
			type:Number,
			default:200
		},
		height:{
			type:Number,
			default:200
		},
		/*上传图片的地址*/
		uploadUrl: {
			type: String,
			default:null
		},
		value:{
			type:Boolean,
			default:false
		},
	},
	data(){
		return {
			show: false,
			params:{}
		};
	},
	watch:{
		value(newv){
			this.show=newv;
		},
		show(n,o){
			if(!this.show)
				this.$emit("input",false);
		}
	},
	mounted(){
		this.show=this.value;
	},
	methods:{
		// 图片截取完成事件
		cropSuccess(imgDataUrl, field){
			this.params.works_type = 5;
			this.params.works_item_id = 0;
			this.imgDataUrl = imgDataUrl;
		},

		// 上传成功
		uploadSuccess(field){
			let params = {
				fileid:field
			};

			// 拿到文件ID
			this.axios.get("/api/file/getimg",{params}).then((res) =>{
				let img = res.data.data;
				let imgtext = `<img id=${ img.id} src=${window.host +"/" + img.total_path}  />`;
				this.$emit("uploadSuccess",imgtext);
			});

		},
	},
};
</script>

<style>
</style>
