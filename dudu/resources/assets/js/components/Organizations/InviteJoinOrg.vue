<template>
  <div>
    <v-container class="pa-0">
      <v-flex class="py-4 px-3 mb-4 top-title">
        <p 
          class="subheading font-weight-bold mb-0"
          style="text-align: center">可通过两种方式邀请同事加入您所在机构</p>
      </v-flex>

      <v-flex class="px-3 mb-4 mt-1">
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


      <ShareTips :dialog_flag.sync="dialog"/>
    </v-container>
  </div>
</template>

<script>
import QRCode from "qrcode";
import ShareTips from "../Commons/ShareTips";
import {mapState} from "vuex";


export default {
	name: "InviteJoinOrg",
	inject: ["reload"],
	components: {
		QRCode,
		ShareTips
	},

	data() {
		return {
			dialog: false,
		};
	},
	computed: {
		...mapState(["selected_org","user_info","selected_org_user_info"])
	},
	mounted() {
		this.qrcode();
		this.initWxShare();

	},
	methods: {
		qrcode() {
			let canvas = document.getElementById("qrcode");
			let code = this.$route.params.code;

			/*global host*/
			QRCode.toCanvas(canvas, `${window.host}/apply_join_org?org_code=${code}`, {"width": 200}, (error) => {
				if (error) alert(error);
			});
		},

		// 初始化微信分享
		initWxShare() {
			let code = this.$route.params.code;

			/*global host*/

			let url = `${window.host}/apply_join_org?org_code=${code}`; //用户要分享的网址
			let title = this.user_info.name +  "邀请您加入都督"; //分享的标题
			let share_img = window.host+"/images/logo.jpeg"; //分享的图片
			let desc = "您即将加入〔"+ this.selected_org.name +"〕，使用＂都督＂高效工作！"; //分享的描述信息
			let cb = () => {
				this.$toast("分享成功", "success");
			};

			this.wxShare(url, title, share_img, desc, cb);
		}
	}
};
</script>

<style scoped>

  .qrcode{
    width:85%!important;
    height:85%!important;
  }
</style>