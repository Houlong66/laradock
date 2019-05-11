<template>
  <div ref="div">
    <!--展示已加入或注册机构-->
    <div
      v-if="user_orgs_all"
    >
      <div
        class="orgs_title pl-3 pt-3 grey lighten-4"
      >您已加入 :
      </div>

      <v-card
        v-for="(item,index) of all_orgs"
        :key="index"
        flat
      >
        <!--展示已加入机构-->
        <v-card-text
          class="grey lighten-4 pb-0"
        >
          <span class="body-1">[{{ item.name }}]</span>
        </v-card-text>
      </v-card>
      <div class="grey lighten-4 body-1 pl-3 pt-3 pb-3 grey--text text--darken-1">您可以创建新的机构</div>
    </div>


    <v-list two-line>
      <v-layout wrap>
        <v-flex
          xs12>
          <v-layout>
            <v-flex
              xs3
              class="align-self-center">
              <v-list-tile-title class="mb-0 pl-3">机构名称</v-list-tile-title>
            </v-flex>
            <v-flex
              xs9
              class="align-self-center">
              <v-layout>
                <v-flex xs10>
                  <v-text-field
                    v-model="name"
                    class="body-1"
                    placeholder="请填写机构名称"
                    required
                    @blur="scrollTo"/>
                </v-flex>
                <v-flex xs2>
                  <br>
                </v-flex>
              </v-layout>
            </v-flex>
          </v-layout>
        </v-flex>
        <v-flex
          xs12>
          <v-layout class="pb-3">
            <v-flex
              xs3
              class="align-self-center">
              <v-list-tile-title class="mb-0 pl-3">选择地区</v-list-tile-title>
            </v-flex>

            <v-flex
              xs9
              class="align-self-center"
              @click="chooseDist()">
              <v-layout>
                <v-flex xs10>
                  <v-list-tile-title
                    class="my-1"> {{ address_str }}
                  </v-list-tile-title>
                  <v-divider/>
                </v-flex>
                <v-flex xs2>
                  <v-icon
                    class="iconfont dudu-arrow grey--text lighten-3 mt-2"
                  />
                </v-flex>
              </v-layout>
            </v-flex>

          </v-layout>
        </v-flex>
      </v-layout>
    </v-list>

    <v-layout
      class="pl-3"
      row
      wrap>
      <v-flex
        xs1
        class="align-self-center"
        center>
        <v-checkbox
          v-model="has_read"
          small
        />
      </v-flex>
      <v-flex
        xs11
        class="align-self-center subheading">
        <span
          class="grey--text text--darken-5"
          @click="statement_dialog = true">《都督微办公注册协议》</span>
      </v-flex>
    </v-layout>

    <Statement :open.sync="statement_dialog"/>

    <v-btn
      :disabled="!has_read || !region || !name"
      block
      class="white mb-3"
      style="display: block; width:90%; margin:0 auto;"

      @click="submit()">确认创建</v-btn>
    <div
      class="distPicker"
      @blur="selected">
      <v-distpicker
        v-show="showAddressPicker"
        style="height:63vh;width: 100%"
        type="mobile"
        @province="selectProvince"
        @city="selectCity"
        @area="selectDist"/>
    </div>

    <!--蒙版弹窗-->
    <!--新用户注册蒙版-->
    <v-layout
      row
      justify-center>
      <v-dialog
        v-model="tel_dialog"
        persistent
        max-width="600px">
        <v-card>
          <v-card-title>
            <span style="font-size: 1.2rem;line-height: 32px">创建机构后，您即为机构管理员</span>
          </v-card-title>
          <v-card-text class="py-0">
            <v-container
              grid-list-md
              class="pa-0">
              <v-layout wrap>

                <v-flex xs12>
                  <v-text-field
                    v-model="user_name"
                    :rules="name_rules"
                    :counter="10"
                    label="请输入您的姓名或昵称"
                    type="text"
                    required
                    @blur="scrollTo"
                  />
                </v-flex>

                <v-flex xs12>
                  <v-text-field
                    v-if="false"
                    v-model="identity"
                    :rules="identity_rules"
                    label="请输入您的单位职务"
                    type="text"
                    required
                    @blur="scrollTo"
                  />
                </v-flex>

                <v-flex xs12>
                  <v-text-field
                    :rules="tel_rules"
                    v-model="tel"
                    type="int"
                    label="请输入手机号码"
                    hint="请输入真实有效的手机号码"
                    @blur="scrollTo"
                  />
                </v-flex>

                <v-layout
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

                  <v-flex xs4>
                    <v-btn
                      v-btn-control="getCode"
                      :disabled="countNum > 0"
                      class="code-btn"
                      small
                    >{{ btnText }}
                    </v-btn>
                  </v-flex>

                </v-layout>
              </v-layout>
            </v-container>
          </v-card-text>

          <v-card-actions>

            <v-btn
              class="blue white--text"
              block
              @click="checksmsCode"
            >提交
            </v-btn>

          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-layout>
  </div>
</template>

<script>
import VDistpicker from "v-distpicker";
import Statement from "../Commons/Statement";
import {mapState, mapMutations, mapActions} from "vuex";

export default {
	name: "JoinOrg",
	inject: ["reload"],
	components: {
		VDistpicker,
		Statement,
	},
	data: function () {
		return {
			address: {
				province: "",
				city: "",
				dist: "",
			},
			region: "", // 地区
			showAddressPicker: false,
			has_read: 1, // 是否阅读条款
			name: "", // 机构名称
			all_orgs: [], //所有机构
			identity_rules: [
				v => !!v || "请填写单位职务",
			],
			tel_rules: [
				v => !!v || "请填手机号",
				v => (v && v.length == 11) || "手机号必须为11位",
			],
			sms_code: null,
			tel: null,
			countNum: 0,
			btnText: "获取",
			code_rules: [
				v => !!v || "请填写验证码"
			],
			tel_dialog: false,

			identity:"未填",
			user_orgs_all:false,
			user_name:null,
			name_rules:[
				v => !!v || "请填写姓名或昵称",
				v => (v && v.length <= 10) || "姓名或昵称长度不得超过10个字"
			],
			statement_dialog: false,
		};
	},
	computed: {
		...mapState(["selected_org", "user_info"]),
		address_str() {
			return `${this.address.province} ${this.address.city} ${this.address.dist}`;
		}
	},
	mounted() {
		// 初始化数据
		this.initData();
	},
	methods: {
		...mapMutations(["setSelectedOrg"]),
		...mapActions(["initUser"]),

		initData(){
			//获取所有机构
			this.getAllorgs();
			this.checkTel();
			if (this.user_info.orgs.length != 0){
				this.user_orgs_all = !this.user_orgs_all;
			}
			if(this.user_info.name) this.user_name = this.user_info.name ;

		},

		checksmsCode (){
			// if (!this.user_name){
			// 	this.$toast("请填写姓名或昵称","warning");
			// 	return;
			// }

			if(this.user_name.trim().length === 0 || this.user_name.trim().length > 10){
				this.$toast("请按要求填写姓名或昵称","warning");
				return;
			}

			if(this.identity.length != 0 ){
				let postData = {
					sms_code: this.sms_code,
					name: this.user_name,
					phone: this.tel,
					identity: this.identity,
					openid: this.$store.getters.user_info.openid,
				};
				return this.axios.post("/api/org/check_sms_code", postData).then((res) => {
					if (res.data.errcode === 0) {
						this.tel_dialog = false;
						this.initUser().then(() => {
							this.reload();
						});

						this.$toast("验证成功！！","success");
					}else{
						this.$toast(res.data.errmsg,"error");
					}
				}).catch((Err) => {
				});
			}
			this.$toast("请确认填写无误！", "error");
		},

		//发送验证码
		getCode() {
			if (this.tel == null) return this.$toast("请填写手机号码", "error");

			if (this.isValidate(this.tel, "tel")) {
				let postData = {
					openid: this.$store.getters.user_info.openid,
					mobile_rule: "mobile_required",
					phone: this.tel
				};
				return this.axios.post("/api/org/send_sms_code", postData).then((res) => {
					if (res.data.errcode === 0) {
						this.$toast(res.data.data.msg, "success");
						this.setCountDown();
						this.$refs.sms_code_input.focus();
					} else {
						this.$toast(res.data.errmsg, "error");
					}
				}).catch((Err) => {

				});
			}
			return this.$toast("请确认手机号码正确", "error");
		},

		// 60s 倒计时
		setCountDown() {
			this.countNum = 60;
			let intId = setInterval(() => {
				this.countNum--;
				this.btnText = this.countNum + "s后重试";
				if (this.countNum === 0) {
					clearInterval(intId);
					this.btnText = "获取";
				}
			}, 1000);
		},


		// 检查是否进行手机号弹窗
		checkTel() {
			if (this.$store.getters.user_info.tel == null) {
				this.tel_dialog = !this.tel_dialog;
				return;
			}
		},

		getAllorgs() {
			this.axios.get("/api/org/getallorgs").then((res) => {
				this.all_orgs = res.data.data;
			}).catch((Err) => {

			});

		},
		chooseDist: function () {
			this.showAddressPicker = true;
		},
		selectProvince: function (data) {
			this.address.province = data.value;
			this.address.city = "";
			this.address.dist = "";
		},
		selectCity: function (data) {
			if (data.value == "市") {
				this.address.city = "";
			} else if (this.address.province) {
				this.address.city = "-" + data.value;
			}
			this.address.dist = "";
		},
		selectDist: function (data) {
			if (data.value == "区") {
				this.address.dist = "";
			} else if (this.address.province && this.address.city) {
				this.address.dist = "-" + data.value;
			}
			if (this.address.dist != "" && this.address.city != "" && this.address.dist != "区" && this.address.city != "市") {
				this.showAddressPicker = false;
			} else {
				this.showAddressPicker = true;
			}
			this.region = `${this.address.province}${this.address.city}${this.address.dist}`;
		},
		selected: function () {
			this.showAddressPicker = false;
		},

		scrollToTop() {
			// 兼容ios12 输入框导致页面上浮问题
			window.scrollBy(0, 0);
		},

		// 提交申请
		submit () {
			this.axios.post("/api/org/storeagree", {
				name: this.name,
				region: this.region
			}).then((res) => {
				if (res.data.errcode === 0) {
					if (this.selected_org) {
						// 设置新注册成功的机构为默认机构
						this.axios.get(`/api/my/org/${this.selected_org.id}/change_default?change_org=${res.data.data.id}`).then((res) => {
							if (res.data.errcode === 0) {
								this.initUser().then(() => {
									this.$toast("注册成功！", "success");
									this.$router.push("/organizations");
								});
							}
						});
					} else {
						this.initUser().then(() => {
							this.$toast("注册成功！", "success");
							this.$router.push("/organizations");
						});
					}
				} else {
					this.$toast(res.data.errmsg, "warning");
				}
			}).catch((Err) => {

			});
		}

		// // 提交申请
		// submit () {
		// 	this.api.joinOrg(this.name,this.region).then((res)=>{
		// 		if(res){
		// 			if (this.selected_org) {
		// 				// 设置新注册成功的机构为默认机构
		// 				this.api.setOrgDefault(this.selected_org.id,res.data.data.id).then((res) => {
		// 					if (res){
		// 						this.initUser().then(() => {
		// 							this.$toast("注册成功！", "success");
		// 							this.$router.push("/organizations");
		// 						});
		// 					}
		// 				});
		// 				return ;
		// 			}
		//
		// 			this.initUser().then(() => {
		// 				this.$toast("注册成功！", "success");
		// 				this.$router.push("/organizations");
		// 			});
		//
		// 		}
		// 	});
		//
		// }
	}
};
</script>
<style scoped>
.dist {
position: relative;
z-index: 2;
}
.distPicker {
width: 100%;
position: absolute;
right: 0;
overflow: auto;
}
.code-btn{
margin-top: 25px;
margin-left: -4px;
min-width: 100%;
}
.distpicker-address-wrapper{
  color:#333!important;
}
</style>