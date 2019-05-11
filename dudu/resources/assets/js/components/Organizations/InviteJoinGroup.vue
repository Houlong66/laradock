<template>
  <div>
    <v-flex class="py-4 px-3 top-title">
      <v-layout
        row
        justify-center>
        <p class="subheading font-weight-bold mb-0">邀请成员加入群组</p>
      </v-layout>
    </v-flex>
    <v-flex 
      class="mt-3 mx-4" 
      style="border-bottom:dashed 1px #ddd">
      <v-radio-group 
        v-model="invite_type"
        row>
        <v-radio 
          :value="0" 
          label="系统用户"/>
        <v-radio 
          :value="1" 
          label="微信好友(非系统用户)"/>
      </v-radio-group>
    </v-flex>

    <div v-show="invite_type==0">
      <v-flex class="mt-3 mx-4">
        <treeselect 
          v-model="targets" 
          :multiple="true"
          :searchable="false"
          :options="targets_items"
          :max-height="200"
          :always-open="true"
          :default-expand-level="0"
          :z-index="0"
          placeholder="选择邀请对象"
          no-options-text="机构中暂无可邀请成员"
          open-direction="below"
          value-consists-of="LEAF_PRIORITY" />
      </v-flex>

      <v-flex>
        <v-layout
          class="mt-3 px-4"
          justify-center/>
      </v-flex>

    </div>

    <div v-show="invite_type==1">
      <v-flex class="px-3 my-4">
        <p
          class="subheading mb-0"
          style="text-align: center">
          <v-icon
            size="20"
            color="red"
            class="iconfont dudu-saoyisao1"/>
          让同事用微信扫描下方二维码
        </p>
        <v-layout
          align-center
          justify-center
          row>
          <canvas
            id="qrcode"
            class="qrcode">二维码</canvas>
        </v-layout>
      </v-flex>

      <v-flex>
        <p
          class="subheading mb-2"
          style="text-align: center">
          <v-icon
            size="20"
            color="green"
            class="iconfont dudu-weixin"/>
          发送邀请链接给同事的微信号
        </p>
        <v-layout
          class="mx-4"
          style="background:#eee;border-radius:7px;"
          justify-center>
          <!--<v-btn-->
          <!--class="mx-0"-->
          <!--block-->
          <!--@click="dialog = true">-->
          <!--发送邀请链接-->
          <!--</v-btn>-->
          <p
            class="mb-0 py-2">点击右上角“···”选择“发送给朋友”</p>
        </v-layout>
      </v-flex>
    </div>

    <v-layout
      style="width:100%; position:fixed; bottom:0; left:0;"
      justify-center>
      <v-btn
        v-if="invite_type==0"
        class="mb-1"
        block
        @click="invite">
        确认邀请
      </v-btn>
      <!--<v-btn-->
      <!--v-else-->
      <!--class="mb-1"-->
      <!--block-->
      <!--@click="dialog = true">-->
      <!--发送邀请链接-->
      <!--</v-btn>-->
    </v-layout>

    <ShareTips :dialog_flag.sync="dialog"/>
  </div>
</template>

<script>
// import the component
import Treeselect from "@riophae/vue-treeselect";
// import the styles
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import ShareTips from "../Commons/ShareTips";
import { mapState } from "vuex";
import QRCode from "qrcode";

export default {
	name: "InviteJoinGroup",
	components: {
		Treeselect,
		QRCode,
		ShareTips
	},
	data () {
		return {
			targets: [], // 邀请对象
			targets_items: [], // 候选邀请对象
			invite_type: 0, // 邀请用户类型
			dialog: false,
		};
	},
	computed: {
		...mapState(["selected_org","user_info"])
	},
	mounted () {
		this.getGroupUsers();
		this.qrcode();
		this.initWxShare();
	},
	methods: {

		getGroupUsers () {
			this.axios.get(`/api/user/group/${this.$route.params.id}/without_join?org_id=${this.$route.query.org_id}`).then((res) => {
				let data = res.data.data;
				let temp_arr = [];
				for (let index in res.data.data) {
					var temp_data = {
						id: index,
						label: index,
						children: []
					};
					let temp_children = [];
					data[index].forEach((value, i) => {
						if(temp_arr.indexOf(value.id) === -1){
							temp_arr.push(value.id);
							value.label = value.name;
							temp_children.push(value);
						}
					});
					temp_data.children = temp_children;
					if(temp_children.length !== 0)
						this.targets_items.push(temp_data);
				}
			}).catch((Err) => {

			});
		},

		invite () {
			this.targets = [...new Set(this.targets)];
			let temp_targets = this.targets.join(",");
			this.axios.post("/api/my/apply/invite_join_group", {
				send_to_objs: temp_targets,
				group_id: this.$route.params.id
			}).then((res) => {
				if (res.data.errcode == 0) {
					this.$toast("已邀请！", "success");
					this.$router.back();
				}
			});
		},
		qrcode () {
			let canvas = document.getElementById("qrcode");

			/*global host*/
			QRCode.toCanvas(canvas, `http://${window.location.host}/apply_join_org?org_code=${this.$store.state.selected_org.code}&group_id=${this.$route.params.id}&group_name=${this.$route.query.group_name}&user_id=${this.$store.state.user_info.id}`, {"width":180} ,(error) => {
				if (error) alert(error);
			});
		},

		// 初始化微信分享
		initWxShare() {
			/*global host*/

			let url = `${window.host}/apply_join_org?org_code=${this.$store.state.selected_org.code}&group_id=${this.$route.params.id}&group_name=${this.$route.query.group_name}&user_id=${this.$store.state.user_info.id}`; // 用户要分享的url
			let title =  this.user_info.name + "邀请您加入都督"; //分享的标题
			let share_img =  window.host+"/images/logo.jpeg"; //分享的图片
			let desc = "您即将加入〔"+ this.selected_org.name +"〕〔"+ this.$route.query.group_name +"〕工作群组，使用＂都督＂高效工作！"; //分享的描述信息
			let cb = () => {
				this.$toast("分享成功", "success");
			};

			this.wxShare(url, title, share_img, desc, cb);
		},
	}
};
</script>

<style scoped>
  .top-title{
    background: #eee;
  }
</style>