<template>
  <div>
    <v-container>
      <v-flex class="pb-1">
        <div>
          <p class="title pb-2 mb-1 border-b">设置超级管理员密码</p>
        </div>
      </v-flex>

      <v-form ref="form">
        <v-text-field
          v-model="pwd"
          :rules="pwd_rules"
          type="password"
          class="mt-3"
          box
          background-color="white"
          auto-grow
          label="新的密码"
          rows="1"
          required
          @blur="scrollTo"
        />

        <v-text-field
          v-model="pwd_confirm"
          :rules="pwd_rules"
          type="password"
          box
          background-color="white"
          auto-grow
          label="确认密码"
          rows="1"
          required
          @blur="scrollTo"
        />

        <v-layout 
          row 
          wrap>
          <v-flex xs9>
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
          <v-flex xs3>
            <v-btn
              v-btn-control="getCode"
              :disabled="countNum > 0"
              class="mt-14"
              small
            >{{ btnText }}</v-btn>
          </v-flex>
        </v-layout>



        <v-btn
          v-btn-control="submit"
          color="info"
          block >确认修改
        </v-btn>
      </v-form>
    </v-container>
		
    
  </div>
</template>


<script>
import { mapState } from "vuex";

export default {
	name: "OrgSet",
	components: {
    
	},
	data: () =>({
		pwd: null,
		pwd_confirm: null,
		mobile: null,
		sms_code: null,
		pwd_rules: [
			v => !!v || "请填写密码"
		],
		phone_rules: [
			v => !!v || "请填写手机号"
		],
		code_rules: [
			v => !!v || "请填写验证码"
		],
		countNum: 0,
		btnText: "获取"
	}),
	computed: {
		...mapState(["selected_org"])
	},
	methods: {
		getCode () {
			let postData = {
				mobile_rule: "mobile_required",
				org_id: this.selected_org.id
			};
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

		submit: function() {
			if(this.$refs.form.validate()) {

				let postData = {
					pwd: this.pwd,
					pwd_confirm: this.pwd_confirm,
					sms_code: this.sms_code,
					org_id: this.selected_org.id,
				};

				return this.axios.post("/api/org/change_password", postData).then((res) => {
					if(res.data.errcode === 0){
						this.$toast("密码修改成功", "success");
						this.$router.push({path: "/organizations/frameWork"});
					}else{
						this.$toast(res.data.errmsg,"error");
					}
				}).catch((Err) => {

				});       
			}
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
		}
	},
	
};
</script>

<style scoped>
.mt-14 {
	margin-top: 14px;
}
</style>