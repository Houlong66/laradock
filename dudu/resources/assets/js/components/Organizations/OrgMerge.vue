/**
 * @author shaoDong
 * @email scut_sd@163.com
 * @create date 2019-01-03 15:00:37
 * @modify date 2019-01-03 15:00:37
 * @desc 机构对接信息详情及批复结果查看页面
 */
<template>
  <div>
    <!-- 审批结果，展示条件为已经加载完，从审批结果入口进入，接口返回数据没有error -->
    <v-container
      v-if="!isLoading && !hasError && result"
      class="pb-0 audit-box">
      <!-- <v-flex>
        <v-layout row>
          <label class="font-weight-bold">审批人：</label>
          <p>{{ audit.user }}</p>
        </v-layout>
      </v-flex> -->
      <v-flex>
        <v-layout row>
          <label class="font-weight-bold">审批结果：</label>
          <p>{{ result }}</p>
        </v-layout>
      </v-flex>
      <!-- <v-flex>
        <v-layout row>
          <label class="font-weight-bold">批复内容：</label>
          <p>{{ audit.text }}</p>
        </v-layout>
      </v-flex> -->
    </v-container>

    <v-container v-if="!isLoading && !hasError">
      <v-flex class="pb-1">
        <div>
          <p class="title pb-2 mb-1 border-b">{{ title }}</p>
        </div>
      </v-flex>
      <v-flex class="apply-info grey--text text--darken-1 mb-4">
        <p class="mb-2 mt-2">申请人: {{ apply_info.apply_user }}</p>
        <p class="mb-2 mt-2">申请机构: {{ apply_info.org_name }}</p>
        <p class="mb-2 mt-2">申请对接部门: {{ apply_info.dept_name }}</p>
      </v-flex>
     
      <v-text-field
        v-if="!result"
        v-model="password"
        :rules="pwd_rules"
        type="password"
        box
        background-color="white"
        auto-grow
        label="超管密码"
        rows="1"
        required
        @blur="scrollTo"
      />
     
      <v-layout v-if="!result">
        <v-flex
          xs6
          class="mt-3">
          <v-btn
            class="white"
            block
            @click="submitAudit(0)">不同意
          </v-btn>
        </v-flex>
        <v-flex
          xs6
          class="mt-3">
          <v-btn
            class="white"
            block
            @click="submitAudit(1)">同意
          </v-btn>
        </v-flex>
      </v-layout>
    </v-container>
        
    <div 
      v-if="!isLoading && hasError" 
      class="empty">
      这里似乎什么也没有~
    </div>

    <Loading v-if="isLoading"/>
  </div>
  
</template>

<script>
import Loading from "../Commons/Loading";
import { mapState  } from "vuex";

export default {
	components: {
		Loading,
	},
	data: () => ({
		title: "对接申请信息",
		pwd_rules: [
			v => !!v || "请填写超级管理员密码"
		],
		apply_id: null,
		apply_info: null,
		isLoading: true,
		// 是否是批复结果
		result: null,
		hasError: false,
	}),
	computed: {
		...mapState(["selected_org"]),
	},
	mounted () {
		this.apply_id = parseInt(this.$route.params.fo_params.id);
		this.getMergeDetail();
	},
	methods: {

		// 获取对接信息详情，url 需优化
		getMergeDetail (url) {
			let tempUrl = url ? url : "/merge_org_msg/"+ this.$route.params.fo_params.org_id +"?apply_id="+ this.apply_id+"";
			this.axios.get("/api/org" + tempUrl).then(res => {
				if(res.data.errcode === 0) {
					this.apply_info = res.data.data;
					let result = res.data.data.result;
					this.result = result == 0 ? "已拒绝" : result == 1 ? "已同意" : null;
				} else {
					this.hasError = true;
					this.$toast(res.data.errmsg, "error");
				}
				this.isLoading = false;
			}).then(err => {
			});
		},
		//  提交对接
		submitAudit (num) {
			if (this.password) {
				var temp_data = {
					org_id: this.selected_org.id,
					password: this.password,
					apply_id: this.apply_id,
					aggrement: num,
				};
				this.axios.post("/api/org/merge_org_reply", temp_data).then(res => {
					if(res.data.errcode === 0){
						this.$toast("操作成功", "success");
						this.setMessageStatus(this.$route.params.fo_msg_id);
						this.$router.push({path: "/messages"});
					} else {
						this.$toast(res.data.errmsg, "error");
					}
				}).catch((Err) => {
				});
			}
		},
	},
};
</script>

<style scoped>
.apply-info {
    font-size: 16px;
}
.audit-box{
  background:#f44336;
  color: #fff;
}
.create-form {
  background-color: #f6f6f6!important;
  padding: 1rem;
}
.submit-btn{
    width:100%;
  }
</style>
