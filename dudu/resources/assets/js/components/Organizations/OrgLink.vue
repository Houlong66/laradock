<template>
  <v-container>
    <v-flex class="pb-1">
      <div>
        <p class="title pb-2 mb-1 border-b">机构对接</p>
      </div>
    </v-flex>

    <v-form
      ref="form"
      v-model="valid"
      lazy-validation>
	
      <!-- <v-select
      v-if="organization_items"
      v-model="organization"
      :items="organization_items"
     :rules="[v => !!v || '请选择对接机构']" 
      box
      background-color="white"
      label="选择机构"
    /> -->

      <v-text-field
        v-model="code"
        :rules="[v => !!v || '请输入机构编号']"
        box
        background-color="white"
        label="机构编号"
        rows="1"
        required
        class="mt-3"
        @blur="getOrgByCode"
      />

      <v-text-field
        v-model="org_name"
        box
        disabled
        background-color="white"
        auto-grow
        label="机构名称"
        rows="1"
        required
        @blur="scrollTo"
      />

      <v-select
        v-model="department"
        :items="dept_items"
        :rules="[v => !!v || '请选择对接部门']"
        box
        background-color="white"
        label="对接部门"
        required
        @blur="scrollTo"
      />

      <v-text-field
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
    
      <!-- <label class="subheading">
      <v-icon
        size="20"
        color="grey"
        class="iconfont dudu-shuoming-copy-copy"/>
      对接申请说明
    </label>
    <v-textarea
      v-model="audit_text"
      solo
      placeholder="申请说明"
      single-line
      class="mt-2"
    /> -->

      <v-layout
        class="pt-3"
        justify-space-between>
        <v-flex xs12>
          <v-btn
            v-btn-control="submit"
            dark
            color="blue"
            class="submit-btn mx-0"
          >
            发起对接
          </v-btn>
        </v-flex>
      </v-layout>
    </v-form>
  </v-container>
  
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import { mapState } from "vuex";

export default {
	components: {
		Treeselect,
	},
	data: () => ({
		valid: false,
		code: null,  // 机构编号 
		password: "", // 密码
		pwd_rules: [
			v => !!v || "请填写超级管理员密码"
		],
		organization: null,
		org_name: "请输入",

		// 部门选项
		department: null,
		dept_items: [],
	}),
	computed: {
		...mapState(["selected_org"]),
	},
	watch: {

	},
	mounted () {
	},
	methods: {
		getOrgByCode () {
			this.scrollTo();

			this.axios.get("/api/org/getByCodeMergeOrg/" + this.code).then(res => {

				if(res.data.errcode === 0) {
					if(res.data.data) {
						this.organization = res.data.data.id;
						if(this.organization == this.selected_org.id) {
							this.$toast("不能对接当前机构", "error");
							this.code = null;
						} else {
							this.org_name = res.data.data.name;
							this.getDepartments();
						}
					}
				} else {
					this.$toase(res.data.errmsg, "error");
				}
			}).then(err => {

			});
		},
		getDepartments () {
			this.axios.get("/api/dept/org/" + this.organization).then(res => {
				if(res.data.errcode === 0) {
					this.dept_items = res.data.data;
					this.dept_items.forEach((val, index) => {
						val.text = val.name;
						val.value = val.id;
					}); 
				} 
			}).then(err => {

			});
		},
		// 提交对接机构
		submit () {
			if (this.$refs.form.validate()) {


				var temp_data = {
					org_id: this.selected_org.id,
					merge_org_id: this.organization,
					dept_id: this.department,
					password: this.password,
				};
				this.axios.post("/api/org/merge_org", temp_data).then(res => {
					if(res.data.errcode === 0){
						this.$toast("操作成功", "success");
						this.$router.push({path: "/organizations"});
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
.submit-btn{
    width:100%;
  }
</style>