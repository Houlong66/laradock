<template>
  <div>
    <div
      style="background: white"
      class="pa-2" >
      <v-layout>
        <v-flex
          xs12
          md4>
          <v-text-field
            v-model="urlTitle"
            :rules="nameRules"
            :counter="1000"
            label="标题"
            placeholder="腾讯官网"
            required
            @blur="scrollTo"
          />
          <v-text-field
            v-model="urlDetail"
            :rules="nameRules"
            :counter="1000"
            label="链接"
            placeholder="http://www.qq.com"
            required
            @blur="scrollTo"
          />
        </v-flex>
      </v-layout>
    </div>

    <div style="background: white;padding-bottom: 10px;">
      <v-btn
        @click="uploadUrl">添加</v-btn>
    </div>

    <div
      v-if="urlList.length > 0"
      class="all">
      <div
        v-for="(item,index) in urlList"
        :key="index"
        class="cad">
        <div class="list">
          <div class="hid">标题: {{ item.url_title }}</div>
          <div class="hid">链接: {{ item.url_path }}</div>
        </div>
        <div @click="showDialog(item)">编辑</div>
      </div>
    </div>

    <!--url地址编辑弹框-->
    <div class="text-xs-center">
      <v-dialog
        v-model="edit_dialog"
        width="500">
        <v-card>
          <v-card-title
            class="subheading headline grey lighten-2"
            primary-title>修改附加外部网址
            <v-icon
              class="iconfont dudu-guanbi1"
              @click="edit_dialog = false"/>
          </v-card-title>
          <v-card-text>
            <v-layout>
              <v-flex
                xs12
                md4>
                <v-text-field
                  v-model="urlTitleEdit"
                  :rules="nameRules"
                  :counter="1000"
                  label="标题"
                  required
                  @blur="scrollTo"/>
                <v-text-field
                  v-model="urlDetailEdit"
                  :rules="nameRules"
                  :counter="1000"
                  label="链接"
                  required
                  @blur="scrollTo"/>
              </v-flex>
            </v-layout>
          </v-card-text>
          <v-divider/>
          <v-card-actions>
            <v-spacer/>
            <v-btn
              color="primary"
              flat
              @click="edit_dialog = false; reconfirm_dialog = true">
              删除链接
            </v-btn>
            <v-btn
              color="primary"
              flat
              @click="editUrl">
              确认修改
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>

    <!--再次确认是否删除,二次弹框-->
    <div class="text-xs-center">
      <v-dialog
        v-model="reconfirm_dialog"
        width="500">
        <v-card>
          <v-card-title
            class="subheading headline grey lighten-2"
            primary-title>确认要删除该附加外部网址吗？
            <v-icon
              class="iconfont dudu-guanbi1"
              @click="reconfirm_dialog = false,edit_dialog = true"/>
          </v-card-title>
          <v-card-text>
            <v-layout>
              删除后将无法恢复
            </v-layout>
          </v-card-text>
          <v-divider/>
          <v-card-actions>
            <v-spacer/>
            <v-btn
              color="primary"
              flat
              @click="reconfirm_dialog = false,edit_dialog = truereconfirm_dialog = false,edit_dialog = true">
              取消
            </v-btn>
            <v-btn
              color="primary"
              flat
              @click="deleteUrl">
              确认删除
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
  </div>
</template>

<script>
export default {
	name: "UploadUrl",
	components: {},
	props: {
		works_type: {
			type:String,
			default: "1"
		},
		url_id_list: {
			type: Array,
			default: () => []
		},
		works_id: {
			type: [String,Number] ,
			default:null
		}
	},
	data() {
		return {
			urlTitle: "",
			urlDetail: "",
			urlTitleEdit: "",
			urlDetailEdit: "",
			nameRules: [
				// v => !!v || '内容不能为空',
				v => v.length <= 1000 || "超过长度"
			],
			urlIdList: [],
			urlList: [],
			warning: false,
			edit_dialog: false,
			deleteId: "",
			worksType:this.$props.works_type,
			worksId: this.$props.works_id,
			reconfirm_dialog: false,
		};
	},
	watch: {
		works_id(){
		}
	},
	mounted () {
		if (this.worksId){
			this.lookupUrl();
		}
	},
	methods: {
		// // 上传网址插入数据库
		uploadUrl() {
			let reg = /(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?/;
			if (this.urlTitle === "" || this.urlDetail === "") {
				this.$toast("不可添加空内容", "error");
			}
			else if (!reg.test(this.urlDetail)){ //url正则
				this.$toast("请输入正确的网址链接", "error");
			}
			else{
				if (this.worksId){ // 流传审批返回时添加url
					let data = {
						url_title: this.urlTitle,
						url_path: this.urlDetail,
						works_id: this.worksId,
						works_type: this.worksType,
					};
					this.axios.post("/api/task/uploadUrl", data).then((res) =>{
						this.lookupUrl(this.urlIdList);
						this.urlTitle = "";
						this.urlDetail = "";
					});
				}
				else { //不是流传审批返回是添加url
					let data = {
						url_title: this.urlTitle,
						url_path: this.urlDetail,
						works_type: this.worksType,

					};
					this.axios.post("/api/task/uploadUrl", data).then((res) => {
						this.urlIdList.push(res.data);
						this.lookupUrl(this.urlIdList);
						this.$emit("update:url_id_list",this.urlIdList);
						this.urlTitle = "";
						this.urlDetail = "";
					});
				}
			}
		},
		//
		// 删除url
		deleteUrl() {
			let that = this;
			let array = [];
			let data = { id: this.deleteId };
			this.axios.post("/api/task/deleteUrl",data).then((res)=> {
				that.urlIdList.map((item) => {
					if (item !== that.deleteId) {
						array.push(item);
					}
				});
				that.urlIdList = array;
				that.lookupUrl(this.urlIdList);
				that.edit_dialog = false;
				that.reconfirm_dialog = false;
			});
		},

		// // 检索url
		lookupUrl(id) {
			if(this.worksId){
				let data = {
					works_id: this.worksId,
					works_type: this.worksType,
				};
				this.axios.post("/api/task/lookupUrl", data).then((res) => {
					this.urlList = res.data.data;

					let arr = [];
					this.urlList.forEach((v, i) => {
						arr.push(v.id);
					});
					this.$emit("update:url_id_list", arr);
				});

			} else{
				let data = { id: id };
				this.axios.post("/api/task/lookupUrl", data).then((res) => {
					this.urlList = res.data.data;
				});
			}
		},

		// 修改url
		editUrl() {
			let that = this;
			let reg = /(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?/;
			let data = {
				id: this.deleteId,
				url_title: this.urlTitleEdit,
				url_path: this.urlDetailEdit,
			};
			if (!reg.test(this.urlDetailEdit)){ // url正则
				this.$toast("请输入正确网址链接", "error");
			}
			else {
				this.axios.post("/api/task/editUrl", data).then((res) =>{
					that.lookupUrl(this.urlIdList);
					that.edit_dialog = false;
				});
			}
		},

		showDialog(event) {
			this.edit_dialog = true;
			this.urlTitleEdit = event.url_title;
			this.urlDetailEdit = event.url_path;
			this.deleteId = event.id;
		},
	}
};
</script>

<style scoped>
.all{
background: white;
padding-bottom: 8px;
}
.cad{
background:#f5f5f5;
box-shadow: 0 2px 1px -1px rgba(0,0,0,.2), 0 1px 1px 0 rgba(0,0,0,.14), 0 1px 3px 0 rgba(0,0,0,.12);
border-radius: 2px;
min-width: 0;
margin:0 0 10px 5%;
padding-bottom: 2px;
display: flex;
align-items: center;
width: 90%;
}
.hid{
overflow:hidden;
text-overflow: ellipsis;
white-space: nowrap;
margin: 2px;
}
.list{
margin: 0 10px 0px 0px;
width: 84%;
padding:3px;
color:#616161
}
.v-card__title {
justify-content: space-between
}
</style>