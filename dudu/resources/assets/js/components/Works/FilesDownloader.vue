<template>
  <div>
    <v-card class="mt-2">
      <v-container
        grid-list-sm
        fluid
        class="pa-0">
        <v-layout
          row
          wrap
          class="px-3 py-2">
          <v-flex
            v-if="pic.length!==0"
            xs12>
            <v-subheader class="pl-0 file-subheader">图片<span>（{{ pic.length }}）</span></v-subheader>
          </v-flex>
          <v-flex
            v-for="(item, index) in pic"
            :key="index"
            xs4
            d-flex
            class="mb-3"
          >
            <v-card
              flat
              tile
              class="d-flex">
              <div>
                <v-img
                  :src="item.file_path"
                  :lazy-src="item.file_path"
                  aspect-ratio="1"
                  class="grey lighten-2"
                  @click="openDialog(item)"
                >
                  <v-layout
                    slot="placeholder"
                    fill-height
                    align-center
                    justify-center
                    ma-0
                  >
                    <v-progress-circular
                      indeterminate
                      color="grey lighten-5"/>
                  </v-layout>
                </v-img>
              </div>
            </v-card>
          </v-flex>

          <v-flex
            v-if="file.length!==0"
            xs12
          >
            <v-subheader class="pl-0 file-subheader">文件<span>（{{ file.length }}）</span></v-subheader>
          </v-flex>
          <v-flex>
            <v-list
              two-line
              subheader>
              <v-list-tile
                v-for="(item, index) in file"
                :key="index"
                class="grey lighten-4 mb-1"
              >
                <v-list-tile-avatar>
                  <v-icon class="iconfont dudu-mingpian1"/>
                </v-list-tile-avatar>

                <v-list-tile-content>
                  <v-list-tile-title>{{ item.file_name }}</v-list-tile-title>
                </v-list-tile-content>

                <v-list-tile-action>
                  <v-btn
                    v-if="downloaderType === 0"
                    icon
                    ripple
                    @click="download(item.file_id,item.file_name)">
                    <v-icon
                      color="grey"
                      class="iconfont dudu-download"/>
                  </v-btn>

                  <v-btn
                    v-if="ifCanDelete"
                    icon
                    ripple
                    @click="deleteAttach(item.file_id, 'file', file)">
                    <v-icon
                      color="red"
                      class="iconfont dudu-picture-delet"/>
                  </v-btn>
                </v-list-tile-action>
              </v-list-tile>
            </v-list>
          </v-flex>
        </v-layout>
        <!-- 在移动端微信浏览器中才会起作用的clipboard 辅助ui -->
        <!-- <div style="display: none">
          <v-btn class="btn-copy" ref="clickToCopy" :data-clipboard-text="downloadRef">点击复制</v-btn>
        </div> -->

        <v-btn
          v-if="downloaderType === 0"
          class="ma-0 grey--text text--darken-1"
          style="width:100%;color:#ffffff !important;"
          @click="downloadAll(workItemId)">
          <v-icon
            class="iconfont dudu-download"/>
          下载全部附件
        </v-btn>
      </v-container>
    </v-card>


    <v-dialog
      v-model="dialog"
      width="500"
    >
      <v-card>
        <v-card-title class="pb-0">
          <v-layout>
            <v-flex
              xs8
              class="align-self-center text-truncate">
              {{ dialog_file.file_name }}
            </v-flex>
            <v-flex
              xs2
              class="align-self-center"
              style="text-align: right;">
              <v-btn
                v-if="downloaderType === 0"
                class="ma-0"
                style="background:white;"
                small
                icon
                ripple
                @click="download(dialog_file.file_id, dialog_file.file_name)">
                <v-icon
                  color="grey"
                  class="iconfont dudu-download"/>
              </v-btn>

              <v-btn
                v-if="ifCanDelete"
                class="ma-0"
                style="background:white;"
                icon
                ripple
                @click="deleteAttach(dialog_file.file_id, 'pic', pic)">
                <v-icon
                  color="red"
                  class="iconfont dudu-picture-delet"/>
              </v-btn>
            </v-flex>

            <v-flex
              xs2
              class="align-self-center"
              style="text-align: right;">
              <v-btn
                class="ma-0"
                style="background:white;"
                icon
                ripple
                @click="dialog = false">
                <v-icon
                  color="grey"
                  class="iconfont dudu-guanbi1"/>
              </v-btn>
            </v-flex>
          </v-layout>
        </v-card-title>

        <v-card-text>
          <v-img
            :src="dialog_file.file_path"
            :lazy-src="dialog_file.file_path"
          />
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
// import FileSaver from "file-saver";

export default {
	components: {},
	props: {
		// 图片数组
		pic: {
			type: Array,
			default: () => [],
		},
		// 非图片数组
		file: {
			type: Array,
			default: () => [],
		},
		// 附件渲染类型， 0为可下载附件；1为开启删除附件功能
		downloaderType: {
			type: Number,
			default: 0
		},
		// 附件关联的工作
		work: {
			type: Object,
			default: () => {
			}
		},
		// 工作关联的附件是否可删除, 仅当downloaderType为1时可用
		ifCanDelete: {
			type: Boolean,
			default: false
		},
		// 附件关联的工作项的id，0为工作母体，非0为关联工作子项
		workItemId: {
			type: Number,
			default: 0
		},
		// 工作的类型: 'task', 'notification', 'ask'
		workType: {
			type: String,
			default: "task"
		}
	},
	data() {
		return {
			search_key: null,
			timeout: null,
			dialog: false,
			dialog_file: {},
			// 是否是移动端微信浏览器
			isMobileWx: false,
			// 下载地址，只有在移动端微信浏览器中才能用到
			downloadRef: "",
		};
	},

	mounted: function () {
		this.isMobileWx = this.isWxBrowser() && (this.isAndroid() || this.isIos());
	},
	methods: {
		openDialog(item) {
			this.dialog = true;
			this.dialog_file = item;
		},
		download(id, file_name) {
			let data = {
				file_id: id,
				works_type: this.workType,
			};

			// this.axios({
			// 	method: "post",
			// 	url: "/api/file/download",
			// 	responseType: "blob",
			// 	data: data
			// }).then((res) => {
			// 	let url = window.URL.createObjectURL(res.data);
			// 	FileSaver.saveAs(url, file_name);
			// });
			this.axios({
				method: "post",
				url: "/api/file/download",
				responseType: "json",
				data: data
			}).then(res => {
				if(res.data.errcode === 0) {
					this.downloadRef = window.location.protocol + "//" + window.location.host + "/file?guide=1&token=" + res.data.data.token;
					// 如果是IOS 系统需要提醒用户跳转
					if(this.isIos()) {
						alert("请点击跳转后的页面右上角，选择在浏览器中打开，进行下载相关操作~");
					}
					window.location.href = this.downloadRef;
				} else {
					this.$toast(res.data.errmsg, "warning");
				}
			});
		},
		deleteAttach(id, type, arr) {
			let data = {
				file_id: id,
				works_type: this.workType,
			};

			this.axios({
				method: "post",
				url: "/api/file/delete_attach",
				data: data
			}).then((res) => {
				if (res.data.errcode === 0) {
					this.$toast("删除附件成功");
					// 从数组中提出已删除的附件
					arr.splice(arr.findIndex(v => v.file_id === id), 1);
					// 更新文件数组
					if (type === "pic") {
						this.dialog = false;
						this.$emit("update:pic", arr);
					} else {
						this.$emit("update:file", arr);
					}
				}
			});
		},
		downloadAll(workItemId) {
			// 根据工作类型获取工作子项数组
			// let workItem = {};
			// switch (this.workType) {
			// case "task":
			// 	workItem = this.work.task_items;
			// 	break;
			// case "notification":
			// 	workItem = this.work.notification_items;
			// 	break;
			// case "ask":
			// 	workItem = this.work.ask_items;
			// 	break;
			// }

			let data = { // todo 修改接口参数命名
				work_id: this.work.id,
				works_type: this.workType,
				works_item_id: workItemId
			};

			// this.axios({
			// 	method: "post",
			// 	url: "/api/file/download_all",
			// 	responseType: "blob",
			// 	data: data
			// }).then((res) => {
			// 	let url = window.URL.createObjectURL(res.data);

			// 	let filename = "";
			// 	// 构建附件包的文件名
			// 	if (this.workItemId === 0) {
			// 		filename = this.work.title + "的全部附件";
			// 	} else {
			// 		for (let i in workItem) {
			// 			if (workItem[i].id === workItemId) {
			// 				filename = `${this.work.title}_${workItem[i].user.name}_的上报附件`;
			// 			}
			// 		}
			// 	}
			// 	FileSaver.saveAs(url, filename);
			// });
			this.axios({
				method: "post",
				url: "/api/file/download_all",
				responseType: "json",
				data: data
			}).then(res => {
				if(res.data.errcode === 0) {
					this.downloadRef = window.location.protocol + "//" + window.location.host + "/file?guide=1&token=" + res.data.data.token;
					// 如果是IOS 系统需要提醒用户跳转
					if(this.isIos()) {
						alert("请点击跳转后的页面右上角，选择在浏览器中打开，进行下载相关操作~");
					}
					window.location.href = this.downloadRef;
				} else {
					this.$toast(res.data.errmsg, "warning");
				}
			});
		},
	}
};
</script>

<style scoped>
  .file-subheader {
    height: 36px;
    border-bottom: solid 1px #ddd;
  }

  .v-image {
    border-radius: 5px;
  }
</style>
