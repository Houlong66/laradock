<template>
  <div v-if="user">
    <router-view name="tNav"/>
    <router-view/>
    <router-view name="bNav"/>


    <!--反馈按钮-->
    <feedback
      v-if="show_flag"
      ref="feedback"
      :style="position"
      class="feedback"
      @touchstart.native="startMove($event)"
      @touchmove.native="moving($event)"/>

    <!--帮助按钮-->
    <v-btn
      v-if="show_flag"
      :style="help"
      fab
      small
      dark
      class="help-btn"
      color="blue"
      @click="showHelp()">
      <span class="font-weight-bold">帮助</span>
    </v-btn>

    <!--弹窗-->
    <dialogs
      v-if="help_dialogs"
      :show.sync="help_dialogs"
      :title="dialogs_title"
    >
      <div slot="dialogs-content">
        <Help/>
      </div>
    </dialogs>
  </div>
</template>
<script>
import {mapState, mapActions, mapMutations} from "vuex";
import Feedback from "../Feedback/Feedback";
import Dialogs from "./Dialogs";
import Help from "../../pages/Help";

import vConsole from "vconsole";

export default {
	name: "MyUser",
	components: {
		Feedback,
		Dialogs,
		Help
	},
	provide() {
		return {
			reload: this.reload
		};
	},
	data() {
		return {
			user: false,
			position: {
				bottom: "250px",
				right: "5%",
			},
			initX: null,
			initY: null,
			help_dialogs:false,
			dialogs_title:null,
			help:{
				bottom: "200px",
				right: "5%",
			},
			show_flag: true,
			show_help:false
		};
	},
	computed: {
		...mapState(["refresh_user", "user_info"])
	},
	watch: {
		$route(to, form) {
			this.btnMove();
		}
	},
	beforeMount(){

	},
	created() {

		// 若无用户信息未获取，则初始化
		if (JSON.stringify(this.user_info) === "{}") {
			this.initUser().then((res) => {
				this.user = res;
			});
		}
	},
	mounted() {
		// 记录进入 URL 用于解决 ios 端，微信 js-sdk 认证的天坑问题
		// new vConsole;
		if (window.entryUrl === undefined) {
			window.entryUrl = window.location.href;
		}

		// 全局按钮位置设定
		this.btnMove();

		// 调试工具注册
		new vConsole();

		// 设置消息为已读
		if (this.$route.query.fo_msg_id) {
			// 若从微信模板消息进入，则将消息置为已读
			this.setMessageStatus(this.$route.query.fo_msg_id);
		}
	},
	methods: {
		...mapActions(["initUser"]),
		...mapMutations(["setUserInfo", "setSelectedOrg", "toggleRefreshUser"]),
		showHelp(){
			this.help_dialogs = !this.help_dialogs;
			this.dialogs_title = "都督使用文档";
		},
		// 重载 MyUser 组件，促使子组件重新渲染(重载生命周期)
		reload() {
			this.user = false;
			this.$nextTick(() => {
				this.user = true;
			});
		},
		startMove(e) {
			this.initX = e.touches[0].clientX - this.$refs.feedback.$el.offsetLeft;
			this.initY = e.touches[0].clientY - this.$refs.feedback.$el.offsetTop;
		},
		moving(e) {
			let nowX = e.touches[0].clientX - this.initX;
			let nowY = e.touches[0].clientY - this.initY;
			let height = document.documentElement.clientHeight;
			let btn_bottom = height - nowY - 56;
			this.position.left = `${nowX}px`;
			this.position.bottom = `${btn_bottom}px`;
		},
		btnMove(){
			let path = this.$route.path;
			if (path == "/works/0" || path == "/works/1" || path == "/works/2") {
				this.position.bottom = "200px";
				this.position.right = "5%";

				this.help.bottom = "150px";
				this.help.right = "5%";

			} else if (path == "/schedules") {

				this.position.bottom = "250px";
				this.position.right = "5%";

				this.help.bottom = "200px";
				this.help.right = "5%";

			} else if (path == "/about" || path == "/help" || path == "/admin" || path == "/addlist") {
				this.show_flag = false;
			} else {
				this.position.bottom = "150px";
				this.position.right = "5%";

				this.help.bottom = "100px";
				this.help.right = "5%";
			}
		}
	}
};
</script>
n
<style scoped>
.top-nav {
padding-top: 8vh;
}

.feedback {
position: fixed;
}
.help-btn{
position: fixed;
}
.sumwork{
position: fixed;
}
.systext-box{
margin: 14px;
border: 1px solid #ccc;
padding: 15px;
}
.systext-title{
font-weight: 600;
}
.systext-text{
word-wrap: break-word;
word-break: normal;
font-size: .9rem;
color: #a29c9c;
}
.module-text{
margin: 10px;
padding: 10px;
border: 1px solid #ccc;
}
.swiper-container {
width: 100%;
height: 100%;
}
.swiper-slide {
font-size: 18px;
height: auto;
-webkit-box-sizing: border-box;
box-sizing: border-box;
padding: 30px;
}
</style>
