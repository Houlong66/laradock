<template>
  <div>
    <uploader
      ref="uploader"
      :options="options"
      class="uploader-example">
      <uploader-unsupport/>
      <uploader-drop>
        <p>Drop files here to upload or</p>
        <uploader-btn>select files</uploader-btn>
        <uploader-btn :attrs="attrs">select images</uploader-btn>
        <uploader-btn :directory="true">select folder</uploader-btn>
      </uploader-drop>
      <uploader-list/>
    </uploader>
    <a 
      class="display:block" 
      @click="download()">下载</a>
  </div>
</template>

<script>
import FileSaver from "file-saver";
export default {
	data () {
		return {
			options: {
				// https://github.com/simple-uploader/Uploader/tree/develop/samples/Node.js
				target: "/api/file/upload",
				testChunks: false
			},
			attrs: {
				accept: "image/*"
			}
		};
	},
	methods: {
		download() {
			this.axios({
				url:"/api/file/download",
				responseType: "blob",
				method: "post",
			}).then((res) => {
				// let blob = new Blob(res.data);
				let url = window.URL.createObjectURL(res.data);

				FileSaver.saveAs(url, "test.xlsx");
				// let a = document.createElement("a");
				// let url = window.URL.createObjectURL(blob);
				// let filename = "haha.jpg";
				// alert(filename);
				// let myFrame= document.createElement("iframe");
				// myFrame.src = url;
				// myFrame.style.display = "none";
				// document.body.appendChild(myFrame);
				// a.href = url;
				// a.download = filename;
				// a.click();
				// window.URL.revokeObjectURL(url);
			});
		}
	}
};
</script>

<style>
  .uploader-example {
    width: 880px;
    padding: 15px;
    margin: 40px auto 0;
    font-size: 12px;
    box-shadow: 0 0 10px rgba(0, 0, 0, .4);
  }
  .uploader-example .uploader-btn {
    margin-right: 4px;
  }
  .uploader-example .uploader-list {
    max-height: 440px;
    overflow: auto;
    overflow-x: hidden;
    overflow-y: auto;
  }
</style>