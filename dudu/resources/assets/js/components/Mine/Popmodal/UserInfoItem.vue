<template>
  <div>
    <v-list class="pa-0">
      <div>
        <v-list-tile
          class="pt-1 pb-1"
          @click="editPublic()">
          <v-layout>
            <v-flex xs4>
              <span style="font-size:14px;">{{ name }}</span>
            </v-flex>
            <v-flex xs8>
              <div
                v-if="name != '头像'"
                style="text-align:right; font-size:14px;">{{ value }}</div>
              <v-list-tile-title
                v-else
                style="text-align: right">
                <img :src="value">
              </v-list-tile-title>
            </v-flex>
          </v-layout>
        </v-list-tile>
        <v-divider/>
      </div>
    </v-list>

    <!--底部弹窗-->
    <v-bottom-sheet v-model="sheet">
      <v-card>

        <v-card-actions>
          <v-btn
            color="blue darken-1 "
            flat
            @click.native="sheet = false">取消</v-btn>
          <v-spacer/>
          <v-btn
            color="blue darken-1"
            flat
            @click.native="savePublic()">确定</v-btn>
        </v-card-actions>
        <v-divider/>


        <v-card-text>
          <v-container grid-list-md>
            <v-container >
              <v-switch
                :label="switchLabel"
                :disabled="true"
                v-model="isOpen"/>
            </v-container>

            <v-text-field
              v-model="infoValue"
              label="请填写"
              solo
              @focus="emailfocus"
              @blur="scrollTo"
            />

            <!--email 提示框-->
            <div
              v-if="emailtips"
              class="email-tips"
            >
              <ul
                class="pl-0"
                @click="emailtime"
              >
                <li
                  v-for="(item,index) in emaillist"
                  :key="index">{{ item }}</li>
              </ul>
            </div>

            <v-layout
              v-if="isphone"
              row
              wrap>
              <v-flex xs8>
                <v-text-field
                  v-model="sms_code"
                  :rules="code_rules"
                  type="text"
                  background-color="white"
                  box
                  auto-grow
                  label="验证码"
                  rows="1"
                  required
                  @blur="scrollTo"
                />
              </v-flex>

              <v-flex
                class="smscode-btn"
                xs4>
                <v-btn
                  v-btn-control="getCode"
                  :disabled="countNum > 0"
                  class="mt-14"
                  small
                >{{ btnText }}</v-btn>
              </v-flex>
            </v-layout>


          </v-container>
        </v-card-text>
      </v-card>
    </v-bottom-sheet>

  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
export default {
	name: "UserInfoItem",
	components: {

	},
	props: {
		name: {
			type: String,
			default: ()=>""
		},
		value: {
			type: String,
			default: ()=>"未填"
		},
		is_open: {
			type: Boolean,
			default: ()=>false
		},
		edit: {
			type: Boolean,
			default: ()=>false
		},
		is_self: {
			type: Number,
			default: ()=>false
		}
	},
	data() {
		return {
			sheet: false,
			switchLabel: "公开",
			isOpen: false,
			infoValue: "",
			isphone:false,
			sms_code:"",
			code_rules: [
				v => !!v || "请填写验证码"
			],
			btnText: "获取",
			countNum: 0,
			emailtips:false,
			is_tips:false,
			savealleamillist:[
				"@163.com","@126.com","@188.com",
				"@gmail.com","@yahoo.com",
				"@msn.com","@qq.com",
				"@sina.com","@sohu.com","@sogou.com"
			],
			emaillist:[],

		};
	},
	computed: {
		...mapState(["user_info"])
	},
	watch: {
		infoValue(n,o){
			this.likeselect(n);
		}

	},
	mounted() {
		this.isOpen = this.is_open;
		this.infoValue = this.value !== "未填" ? this.value:"";
		this.isphone = this.name === "手机号";
		this.emaillist = this.savealleamillist;
	},
	methods: {
		likeselect(str){
			if(str.length == 0){this.emaillist = this.savealleamillist;return ;}
			this.emaillist = this.emaillist.filter(function (v,i) {
				if (v.search(str) != -1){
					return v;
				}
			});
		},
		// 输入框焦点事件
		emailfocus(){
			if (this.name ==  "Email"){
				this.emailtips = true;
			}
		},
		// 替换
		emailtime(e){
			if (!this.is_tips){
				this.is_tips = true;
				this.infoValue =  this.infoValue + e.target.innerHTML;
				this.emailtips = false;
				return ;
			}

			let email_tips = this.infoValue.substring(0,this.infoValue.indexOf("@"));
			this.infoValue  = email_tips  + e.target.innerHTML;
			this.emailtips = false;
		},
		// 发送验证码,带手机号传输的时候,才会根据手机号进行验证
		getCode () {

			if (!this.isValidate(this.infoValue,"tel")){
				this.$toast("请输入正确的手机号码", "error");
				return ;
			}

			let  postData;
			if(this.$store.getters.selected_org != null){
				postData = {
					mobile_rule: "mobile_required",
					org_id: this.$store.getters.selected_org.id,
					phone:this.infoValue
				};
			}else{
				postData = {
					mobile_rule: "mobile_required",
					openid: this.$store.getters.user_info.openid,
					phone:this.infoValue
				};
			}

			return this.axios.post("/api/org/send_sms_code", postData).then((res) => {
				if(res.data.errcode === 0){
					this.$toast(res.data.data.msg,"success");
					this.setCountDown();
				}else{
					this.$toast(res.data.errmsg,"error");
				}
			}).catch((Err) => {
			});
		},
		// 60s 倒计时
		setCountDown() {
			this.countNum = 60;
			let intId = setInterval(() => {
				this.countNum--;
				this.btnText = this.countNum + "s后重试";
				if(this.countNum === 0) {
					clearInterval(intId);
					this.btnText = "获取";
				}
			}, 1000);
		},

		...mapMutations(["setUserInfo"]),
		bindTel: function() {
			let url = "/mine/bindTel";
			this.$router.push({path: url});
		},
		editPublic: function() {
			if (this.is_self == 0) return;
			this.sheet = true;
		},
		savePublic: function() {
			let temp_data = {
				value: this.infoValue
			};

			// 深拷贝，获取 user_info 对象，防止不合规方式修改 vuex
			let user_info_clone = this.deepClone(this.user_info);
			switch (this.name) {
			case "姓名":
				temp_data.key = "name";
				user_info_clone.name = this.infoValue;
				break;
			case "手机号":
				temp_data.key = "tel";
				user_info_clone.tel = this.infoValue;
				break;
			case "Email":
				temp_data.key = "email";
				user_info_clone.email = this.infoValue;
				break;
			case "通讯地址":
				temp_data.key = "address";
				user_info_clone.address = this.infoValue;
				break;
			case "固定电话":
				temp_data.key = "fixed_tel";
				user_info_clone.fixed_tel = this.infoValue;
				break;
			case "QQ":
				temp_data.key = "qq";
				user_info_clone.qq = this.infoValue;
				break;
			case "微信号":
				temp_data.key = "wechat";
				user_info_clone.wechat = this.infoValue;
				break;
			case "单位职务":
				temp_data.key = "identity";
				user_info_clone.identity = this.infoValue;
				break;
			}

			if (this.name == "QQ" && !this.isValidate(this.infoValue,"number")  ){
				this.$toast("请输入正确的qq号码", "error");
				return false ;
			}

			if (this.name == "QQ" && this.infoValue.length > 11){
				this.$toast("请输入正确的qq号码", "error");
				return false ;
			}

			if (this.name == "QQ" && this.infoValue.length < 7) {
				this.$toast("请输入正确的qq号码", "error");
				return false;
			}


			if (this.name == "微信号" && this.isValidate(this.infoValue,"string")){
				this.$toast("请输入正确的微信号", "error");
				return false;
			}


			if(this.isphone){

				if (!this.isValidate(this.infoValue,"tel")){
					this.$toast("请输入正确的手机号码", "error");
					return ;
				}

				let postData;
				if (this.$store.getters.selected_org != null){
					postData= {
						sms_code: this.sms_code,
						phone: this.infoValue,
						org_id: this.$store.getters.selected_org.id,
					};
				} else{
					postData = {
						sms_code: this.sms_code,
						phone: this.infoValue,
						openid: this.$store.getters.user_info.openid,
					};
				}
				return this.axios.post("/api/org/check_sms_code",postData).then((res) => {
					if(res.data.errcode === 0){
						this.setUserInfo(user_info_clone);
						this.sms_code = null;
						this.$toast("修改手机号码成功", "success");
						this.sheet = false;
					}else{
						this.$toast(res.data.errmsg,"error");
					}
				}).catch((Err) => {

				});
			}


			this.axios.post("/api/my/user/update_info", temp_data).then((res) => {
				if (res.data.errcode === 0) {
					this.setUserInfo(user_info_clone);
					this.$toast("修改成功", "success");
					this.sheet = false;
				} else {
					this.$toast(res.data.errmsg, "warning");
				}
			});



		}
	}
};
</script>

<style scoped>
  .smscode-btn{
    margin: 22px 0 0 0;
  }
  .email-tips{
    width: 100%;
    max-height: 300px;
    overflow:auto;
    margin-bottom: 10px;
    -webkit-box-shadow: 1px 1px  1px  1px #ccc;
    -moz-box-shadow: 1px 1px  1px  1px #ccc;
    box-shadow: 1px 1px  1px  1px #ccc;
  }

  .email-tips ul li{
    padding: 10px;
  }
  .email-tips ul li:hover{
    background-color: #ccc;
    color:#ffffff;
  }
</style>
