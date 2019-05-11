<template>
  <v-container>
    <!-- msg board -->
    <v-flex class="pb-2 border-be">
      <label class="subheading ">
        <v-icon
          size="20"
          color="grey"
          class="iconfont dudu-shuoming-copy-copy"/>
        工作交流
      </label> 
    </v-flex>
    
    <!-- msg info -->
    <v-flex 
      v-for="(item, index) in filterMsgList" 
      :key="index" 
      class="mt-3 mb-2">
      <div 
        :class="{ rightHand: (item.user.id == user_info.id) }" 
        class="clearfix" >
        <img 
          :src="item.user.avatar" 
          :alt="item.user.name" 
          class="avatar-img">
          
        <span class="name">
          {{ item.user.id == user_info.id ? "我" : item.user.name }}
          <span 
            v-if="item.user.id==msg_sender" >({{ prefix }}创建者)
          </span>
        </span> <br>
        <span class="color-grey time">{{ item.created_at }}</span><br>
        <div class="clearfix">
          <div class="sanjiao"/>
        </div>
        <span 
          class="content" 
          v-html=" item.content"/>
      </div>
    </v-flex>

    <!-- loading btn -->
    <v-flex
      v-if="msg_list"
      xs12>
      <v-btn
        v-if="!nomore_msg"
        flat
        block
        @click="checkMore()">
        <span class="grey--text text--darken-1">显示更多({{ msg_list.length - now_num }}条)</span>
      </v-btn>
      <v-btn
        v-if="nomore_msg && msg_list.length!=0"
        flat
        block>
        <span class="grey--text text--darken-1">已无更多</span>
      </v-btn>
    </v-flex>

    <!-- msg input -->
    <v-flex>
      <v-textarea
        v-model="discuss_content"
        solo
        placeholder="可在此对此项工作进行交流探讨，可输入@选择此任务相关用户，对方将收到您的留言提醒"
        single-line
        class="body-1"
        @blur="scrollTo"
      />
    </v-flex>

    <v-flex>
      <v-btn
        :disabled="submitting"
        class="white"
        block
        @click="submitDiscuss()">发送
      </v-btn>
    </v-flex>

    <!-- @ 目标选择 -->
    <v-dialog
      v-model="dialog"
      :fullscreen="true"
      scrollable
      transition="dialog-bottom-transition"
      max-width="100%">
      <v-card>
        <v-toolbar
          dark
          color="primary">
          <v-btn
            icon
            dark
            @click.native="dialog = false">
            <v-icon class="iconfont dudu-guanbi1"/>
          </v-btn>
          <v-toolbar-title class="subheading">选择@对象</v-toolbar-title>
          <v-spacer/>
          <v-toolbar-items>
            <v-btn
              dark
              flat
              class="font-weight-bold"
              @click.native="finishChooseTargets()">确定
            </v-btn>
          </v-toolbar-items>
        </v-toolbar>


        <!--  目标选择列表循环  start-->
        <v-card-text style="height: 100vh;">
          <div
            v-for="(item, index) in filterUserItem"
            :class="{ chosen: isInArr(item.user.id, chosen_user_obj.id_arr) }"
            :key="index"
            class="at-wrapper"
            @click="chooseSomeOne(item.user.id, item.user.name)">
            <img
              :src="item.user.avatar"
              :alt="item.user.name">
            <span>{{ item.user.name }}</span>
          </div>
        </v-card-text>
        <!--  目标选择列表循环  end-->



        <v-divider/>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import { mapState } from "vuex";

export default {
	name: "MsgBoard",
	props: {
		msg_list: {
			type: Array,
			default: () => [],
		},
		msg_sender: {
			type: Number,
			default: () => 0
		},
		user_list: {
			type: Array,
			default: () => []
		},
		prefix: {
			type: String,
			default: () => "消息"
		}
	},
	data () {
		return {
			discuss_content: null,
			nomore_msg: false,
			// 每页显示消息的数量
			page_num: 10,
			// 当前消息的下标
			now_num: 0,
			submitting: true,
			dialog: false,
			// 选中的目标id 及名字
			chosen_user_obj: {
				id_arr: [],
				name_obj: {}
			},
		};
	},
	computed: {
		...mapState(["user_info"]),
		filterMsgList () {
			let res = this.msg_list.filter((val, index) => {
				if(index < this.now_num) {
					return val;
				}
			});
			return res;
		},


		// @时候的用户列表
		filterUserItem () {
			let indexs = [];
			let res = this.user_list.filter((val,index) => {
				if(val.user.id != this.user_info.id) {

					let unm = indexs.indexOf(val.user.id);

					if (unm === -1){
						indexs.push(val.user.id);
						return val;
					}
				}
			});
			return res;
		}
	},
	watch: {
		// 提交了新的讨论内容更新展示条目
		msg_list: function (newV, oldV) {
			if(newV.length - oldV.length == 1) {
				// this.submitting = false;
				if(this.now_num % this.page_num > 0 || this.now_num == 0) {
					this.now_num += 1;
				} else {
					this.nomore_msg = false;
				}
			} 
		},
		discuss_content: function (newV, oldV) {
			let newLen = newV.trim().length;
			let oldLen = oldV ? oldV.trim().length : 0;

			if(newLen === 0){
				this.submitting = true;
			}else{
				this.submitting = false;
			}

			// 新增的时候检测@ 输入
			if(newLen > oldLen && newV[newLen - 1] == "@") {
				this.dialog = true;
			}
			// 删除的时候检测是否删除到了被@ 的人
			if(newLen < oldLen) {
				// 找到当前光标的位置
				let nowLoc = this.getDiffFirstLoc(newV, oldV);
				if(oldV[nowLoc] == " ") {
					let start = this.getNameStartLoc(nowLoc);
					if(start != null) {
						this.removeContent(start, nowLoc - 1);
					}
				}
			}
		}
	},
	mounted() {
		// 传入的讨论内容数据是否达到10 条
		if(this.msg_list.length <= this.page_num) {
			this.nomore_msg = true;
			this.now_num = this.msg_list.length;
		} else {
			this.now_num = this.page_num;
		}
	},
	methods: {
		// 发送消息
		submitDiscuss () {

			// 是否已经输入了任务内容
			if(this.discuss_content.trim().length === 0){
				this.$toast("请输入内容", "error");
				this.submitting = false;
				return;
			}

			// 检查是否存在危险行为
			if(this.isValidate(this.discuss_content,"check_html")){
				this.$toast("请查询输入内容","error");
				return ;
			}

			// 对内容进行转义
			let reg = new RegExp("\n","g");
			let  strContent = this.discuss_content.replace(reg,"<br/>");


			// 匹配被艾特的人，并拼接id
			let id_arr = [];
			this.user_list.forEach(val => {
				let name = val.user.name;
				if(this.discuss_content.indexOf("@" + name + " ") > -1) {
					id_arr.push(val.user.id);
				}
			});

			// 提交data
			let submitObj = {
				content: strContent.trim(),
				id: id_arr.join(",")
			};

			this.$emit("submit", submitObj);

			this.discuss_content = "";
			this.submitting = true;
		},


		checkMore (num) { 
			let len = this.msg_list.length;
			if(this.now_num + this.page_num >= len) {
				this.now_num = len;
				this.nomore_msg = true;
			} else {
				this.now_num += this.page_num;
			}
		},
		// 选择结束
		finishChooseTargets () {
			this.dialog = false;
			// 把选择的人展示在页面上
			if(this.chosen_user_obj) {
				this.discuss_content = this.discuss_content.substring(0, this.discuss_content.length - 1);
				for (let index in this.chosen_user_obj.name_obj) {
					this.discuss_content += `@${this.chosen_user_obj.name_obj[index]} `;
				}
			}
			this.chosen_user_obj.id_arr = [];
			this.chosen_user_obj.name_obj = {};
		},
		// 选择要艾特的人
		chooseSomeOne (id, name) {
			if(this.chosen_user_obj.name_obj[id]) {
				this.chosen_user_obj.id_arr.splice(this.chosen_user_obj.id_arr.indexOf(id), 1);
				delete this.chosen_user_obj.name_obj[id];
			} else {
				this.chosen_user_obj.id_arr.push(id);
				this.chosen_user_obj.name_obj[id] = name;
			}
		},
		// 找到光标的位置
		getDiffFirstLoc (a, b) {
			let len = a.length < b.length ? a.length : b.length;
			for(let i = 0; i < len; i++) {
				if(a[i] != b[i]) {
					return i;
				}
			}
			return len;
		},
		removeContent (start, end) {
			let len = this.discuss_content.length;
			let str = "";
			for(let i = 0; i < len; i++) {
				if(i >= start && i <= end) {
					continue;
				} else {
					str += this.discuss_content[i];
				}
			}
			this.discuss_content = str;
		},
		// 找到空格前第一个@ 的位置
		getNameStartLoc (loc) {
			for(let i = loc - 1; i >= 0; i--) {
				if(this.discuss_content[i] == " ") {
					return null;
				}
				if(this.discuss_content[i] == "@") {
					return i;
				}
			}
			return null;
		}
	}
};
</script>

<style scoped>
  .avatar-img {
    float: left;
    width: 20px;
    height: 20px;
    margin-left: 0;
    margin-right: 8px;
    border-radius: 50%;
  }
  .rightHand .avatar-img {
    margin-left: 8px;
    margin-right: 0;
  }
  .rightHand .avatar-img,
  .rightHand .name,
  .rightHand .time,
  .rightHand .content {
    float: right;
  }
  .rightHand .content {
    border-color: #ffeded;
    background: #ffeded;
  }
  .content {
    display: inline-block;
    /* width: 100%; */
    color:#454545;
    border: 1px solid #fff2dd;
    border-radius: 5px;
    padding: 8px 12px;
    background: #fff2dd;
  }
  .sanjiao {
    width: 0;
    height: 0;
    border-width: 6px;
    border-style: solid;
    border-color: transparent transparent #fff2dd transparent;
    margin-left: 5px;
    margin-top: -5px;
  }
  .rightHand .sanjiao {
    float: right;
    margin-right: 5px;
    border-color: transparent transparent #ffeded transparent;
  }
  .at-wrapper {
    height: 40px;
    border-radius: 1px solid grey;
  }
  .at-wrapper.chosen {
    background: antiquewhite;
  }
  .at-wrapper img {
    float: left;
    width: 28px;
    margin-top: 6px;
    margin-right: 10px;
    border-radius: 6px;
  }
  .at-wrapper span {
    display: block;
    line-height: 40px;
    margin-left: 38px;
    border-bottom: 1px solid #f1f1f1;
  }
</style>