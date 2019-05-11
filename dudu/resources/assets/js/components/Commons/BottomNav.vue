<template>
  <v-bottom-nav
    :active.sync="bottomNav"
    :value="true"
    color="red"
    shift
    fixed
    dark
  >
    <v-btn
      dark
      @click.passive="RouterTo(0)">
      <span>工作</span>
      <v-icon class="iconfont dudu-gongzuo"/>
    </v-btn>

    <v-btn
      dark
      @click.passive="RouterTo(1)">
      <span>日程</span>
      <v-icon class="iconfont dudu-richeng2"/>
    </v-btn>

    <v-btn
      dark
      @click.passive="RouterTo(2)">
      <span>消息</span>
      <v-icon class="iconfont dudu-message"/>
      <v-badge
        v-if="unread_msg"
        color="yellow">
        <span
          slot="badge"
          class="flag-text">{{ unread_msg }}</span>
      </v-badge>
    </v-btn>

    <v-btn
      dark
      @click.passive="RouterTo(3)">
      <span>机构</span>
      <v-icon class="iconfont dudu-zuzhi"/>
    </v-btn>

    <v-btn
      dark
      @click.passive="RouterTo(4)">
      <span>我的</span>
      <v-icon class="iconfont dudu-profile"/>
    </v-btn>
  </v-bottom-nav>
</template>

<script>
import { mapMutations} from "vuex";

export default {
	name: "BottomNav",
	inject: ["reload"],
	data() {
		return {
			bottomNav: 0,
			unread_msg: null,
			work_type: "message",
			type_text_array: [
				{
					index: "unread",
					text: "未读"
				},
				{
					index: "read",
					text: "已读"
				}
			],
			message_list: {}
		};
	},
	mounted: function () {
		this.getUnreadNum();
		this.setBottomNav();
	},
	methods: {
		dev() {
			alert("开发中，敬请期待");
		},
		...mapMutations(["setUserInfo"]),
		//设置一开始进来的时候
		setBottomNav() {
			let module_name = this.$route.name;
			switch(module_name){
			case "works":
				this.bottomNav = 0;
				break;
			case "schedules":
				this.bottomNav = 1;
				break;
			case "messages":
				this.bottomNav = 2;
				break;
			case "organizations":
				this.bottomNav = 3;
				break;
			case "mine":
				this.bottomNav = 4;
				break;
			}
			// console.log(this.$route.path);
			// console.log(this.$route);
			// for (let i = 0; i < this.$route.matched.length; i++) {
			// 	let value = this.$route.matched[i];
			//
			// 	if (value.name.indexOf("works") !== -1) {
			// 		this.bottomNav = 0;
			// 		return;
			// 	}
			//
			// 	if (value.name.indexOf("schedules") !== -1) {
			// 		this.bottomNav = 1;
			// 		return;
			// 	}
			//
			// 	if (value.name.indexOf("messages") !== -1) {
			// 		this.bottomNav = 2;
			// 		return;
			// 	}
			//
			// 	if (value.name.indexOf("organizations") !== -1) {
			// 		this.bottomNav = 3;
			// 		return;
			// 	}
			//
			// 	if (value.name.indexOf("mine") !== -1) {
			// 		this.bottomNav = 4;
			// 		return;
			// 	}
			//
			// }
			// this.bottomNav = -1;
		},

		// 各个路由跳转
		RouterTo(index) {
			let url = "/";
			switch (index) {
			case 0:
				url += "works/0";
				break;
			case 1:
				url += "schedules";
				break;
			case 2:
				url += "messages";
				this.reload();
				break;
			case 3:
				url += "organizations";
				break;
			case 4:
				url += "mine";
				break;
			}
			this.$router.replace(url);
		},


		// 获取的是消息列表的提示数字
		getUnreadNum() {

			this.axios.get("/api/message/unread").then((res) => {
				this.unread_msg = res.data.data.length;
			}).catch((err) => {

			});
		}
	}
};
</script>

<style scoped>
  .flag-text {
    color: red;
  }

  .v-icon-white {
    color: #fff;
  }
</style>
