<template>
  <!--组织机构-->
  <div>
    <v-layout 
      v-if="!isLoading "
      column>
      <!--顶部信息按钮栏-->
      <header-text
        :header_text="header_text"
        entry_type="dept_list"/>
      <!--部门列表-->
      <v-card
        flat
        class="u-list"
      >
        <!--部门显示-->
        <dept-item
          v-if="selected_org.depts.length !== 0"
          :dept_list="selected_org.depts"
          :org_msg="selected_org"
        />

        <p
          v-else
          style="text-align: center"
          class="py-3 mb-0 grey--text">
          机构暂无部门或下级单位
        </p>
      </v-card>

      <!--创建部门按钮-->
      <div
        v-if="show_insertdepts"
        style="position: fixed; bottom: 0; width: 100%;">
        <v-layout 
          row 
          style="background: white;">
          <v-flex class="py-0">
            <v-btn
              block
              class="white mt-0 mb-1 py-0"
              @click="createDeptPop()">添加部门/单位
            </v-btn>
          </v-flex>
        </v-layout>
      </div>


    </v-layout>

    <v-bottom-sheet
      v-model="dialog_create_dept"
    >
      <v-card>
        <v-card-text>
          <v-layout
            column
            align-center>
            <v-radio-group
              v-model="dept_type"
              style="width: 100%"
              row>
              <v-radio
                :value="0"
                label="本级部门"/>
              <v-radio
                :value="1"
                label="下级单位"/>
            </v-radio-group>

            <!--输入框-->
            <v-text-field
              v-model="dept_name"
              style="width: 100%"
              label="部门名称"
              @blur="scrollTo"
            />


            <v-checkbox
              v-model="of_adddept"
              style="width: 100%"
              class="ma-0"
              label="是否同时加入此部门/单位"/>

            <v-flex>
              <v-btn
                flat
                color="primary"
                @click="createDeptPopClose()">取消
              </v-btn>
              <v-btn
                :disabled="!dept_name"
                flat
                color="primary"
                @click="createDeptSubmit()">确定
              </v-btn>
            </v-flex>
          </v-layout>
        </v-card-text>
      </v-card>
    </v-bottom-sheet>

    <!--loading...-->
    <component
      v-if="isLoading"
      :is="cView"
    />
  </div>
</template>


<script>
import Loading from "../Commons/Loading";
import UserItem from "../Organizations/Popmodal/UserItem";
import DeptItem from "./Popmodal/DeptItem";
import HeaderText from "../Organizations/Popmodal/HeaderText";
import {mapState, mapActions} from "vuex";

export default {
	name: "DeptList",
	inject: ["reload"],
	components: {
		Loading,
		UserItem,
		HeaderText,
		DeptItem
	},
	data() {
		return {
			isLoading: true,
			cView: "Loading",
			header_text: "",
			dialog_create_dept: false, // 显示创建部门
			dept_type: 0, // 创建部门类型
			dept_name: null, // 创建部门名称
			of_adddept: false,
			show_insertdepts:false,
		};
	},
	computed: {
		...mapState(["selected_org"]),
	},
	mounted() {
		this.initData();
	},
	methods: {
		...mapActions(["initUser"]),
		// 初始化数据
		initData() {
			if (this.selected_org) {
				this.header_text = this.selected_org.name;
				this.isLoading = false;
			} else {
				this.$toast("未加入机构，非法进入", "error");
			}


			if (this.selected_org.role.id == 1 || this.selected_org.role.id == 2 ){
				this.show_insertdepts = !this.show_insertdepts;
			}

		},
		// 创建部门弹框
		createDeptPop() {
			this.dialog_create_dept = true; // 当点击添加部门按钮时改变弹窗的布尔值
		},
		// 创建部门
		createDeptSubmit() {
			this.axios.post("/api/dept/store", {
				org_id: this.selected_org.id,
				name: this.dept_name,
				level: this.dept_type,
				adddept:  this.of_adddept
			}).then((res) => {
				if (res.data.errcode === 0) {
					this.$toast("创建部门成功！", "success");
					this.dialog_create_dept = false;
					this.dept_name = "";
					this.initUser().then(() => {
						this.reload();
					});
				}
			}).catch((Err) => {
				// console.log(Err);
			});
		},
		// 创建部门弹框关闭
		createDeptPopClose() {
			this.dialog_create_dept = false;
			this.dept_name = "";
		},
	},
};
</script>

<style scoped>
</style>