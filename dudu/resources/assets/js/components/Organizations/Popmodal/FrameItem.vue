<template>
  <div>
    <v-list class="pa-0">
      <v-list-tile
        class="pt-1 pb-1">
        <v-layout @click="getDetail(entry_type)">
          <v-flex 
            xs8 
            class="align-self-center" 
            style="font-size:14px;">
            <span >{{ name }} ({{ number }})</span>
          </v-flex>
          <v-flex 
            xs4 
            class="align-self-center" 
            style="text-align:right;">

            <!--删除功能-->
            <v-icon
              v-if="!group"
              :disabled=" number == 0 ? false : true"
              class="iconfont dudu-picture-delet icon-blue"
              @click.stop="deleteDepts(id)"/>

            <!--编辑功能-->
            <v-icon 
              v-if="!group"
              :disabled=" name === org_name ? true : false"
              class="iconfont dudu-bianji icon-blue" 
              @click.stop="edit(depys_types)"/>

            <!--跳转图标-->
            <v-icon 
              class="iconfont dudu-arrow grey--text lighten-3" />
          </v-flex>
        </v-layout>
      </v-list-tile>
      <v-divider/>
    </v-list>


    <!--删除部门弹框-->
    <v-dialog
      v-model="dialog"
      max-width="290"
    >
      <v-card>
        <v-card-title class="headline">删除部门/单位</v-card-title>
        <v-card-text>
          您确定要删除“{{ name }}”吗？
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            color="green darken-1"
            flat="flat"
            @click="dialog = false"
          >
            取消
          </v-btn>
          <v-btn
            color="green darken-1"
            flat="flat"
            @click="confirmDelete()"
          >
            确定
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>





    <!--<v-bottom-sheet v-model="sheet">-->
    <!--<v-card>-->
    <!--<v-card-actions>-->
    <!--<v-btn -->
    <!--color="blue darken-1" -->
    <!--flat -->
    <!--@click.native="sheet = false">取消</v-btn>-->
    <!--<v-spacer/>-->
    <!--<v-btn -->
    <!--color="blue darken-1" -->
    <!--flat -->
    <!--@click.native="deleteItem()">删除</v-btn>-->
    <!--<v-btn -->
    <!--color="blue darken-1" -->
    <!--flat -->
    <!--@click.native="save()">确定</v-btn>-->
    <!--</v-card-actions>-->
    <!--<v-divider/>-->
    <!--<v-card-text>-->
    <!--<v-container grid-list-md>-->
    <!--<v-list-tile-title>部门/单位</v-list-tile-title>-->
    <!--<v-text-field-->
    <!--v-model="name"-->
    <!--solo-->
    <!--/>-->
    <!--</v-container>-->
    <!--</v-card-text>-->
    <!--</v-card>-->
    <!--</v-bottom-sheet>-->


    <!--  底部弹框  -->
    <v-bottom-sheet
      v-model="dept_dialog"
    >
      <v-card>
        <v-card-text>

          <v-layout 
            column 
            align-center>

            <v-text-field
              v-model="dept_name"
              style="width: 100%"
              label="部门名称"
              hide-details
              class="mb-4"
              @blur="scrollTo"
            />

            <p class="depts-title mb-0">选择部门/单位类型(默认为当前类型)</p>
            <!--部门编辑选项-->
            <v-select
              :items="depts_type"
              v-model="change_depts"
              hide-details
              style="width: 100%"
              class="mb-3 pt-1"
            />

            <v-flex>
              <v-btn 
                flat
                color="primary"
                @click="cancelDept()">取消</v-btn>
              <v-btn
                :disabled="!dept_name"
                flat 
                color="primary"
                @click="changeDeptName()">确定</v-btn>
            </v-flex>
          </v-layout>
        </v-card-text>
      </v-card>
    </v-bottom-sheet>
  </div>
</template>


<script>
import { mapState, mapActions } from "vuex";

export default {
	name: "FrameItem",
	inject: ["reload"],
	components: {

	},
	props: {
		name: {
			type: String,
			default: ()=>""
		},
		org_name: {
			type: String,
			default: ()=>""
		},
		number: {
			type: Number,
			default: ()=>0
		},
		id: {
			type: Number,
			default: ()=>0
		},
		depys_types:{
			type: Number,
			default: ()=>3
		},
		org_type: {
			type: Number,
			default: ()=>0
		},
		group: {
			type: Boolean,
			default: ()=>false
		},
		entry_type: {
			type: String,
			default: ""
		}
	},
	data: function() {
		return {
			sheet: false,
			dialog: false,
			dept_dialog: false,
			dept_name: "",
			depts_type:[{"text":"部门","value":0},{"text":"下级单位","value":1}],
			change_depts:null,
			deletedepts_dialog:false,
			need_delet_deptId:""
		};
	},
	computed: {
		...mapState(["varMap"])
	},
	mounted() {
		this.dialog = false;
		this.dept_name = this.name;
	},
	methods: {
		...mapActions(["initUser"]),
		//删除提醒弹框
		deleteDepts (id) {
			this.dialog = true;
			this.need_delet_deptId = id;
		},

		getDetail: function(entry_type) {
			let url = entry_type === "dept" ? `/organizations/dept_user_list?dept_id=${this.id}`: `/group_user_list?group_id=${this.id}`;
			this.$router.push({path: url});
		},
		edit: function(type) {
			this.dept_dialog = true;
			this.change_depts = type;
		},
		// deleteItem: function() {
		// 	this.dialog = true;
		// },
		save: function() {
			// 
			this.sheet = false;
		},


		//删除功能
		confirmDelete: function() {
			let temp_data = {
				orgId: this.$store.state.selected_org.id,
				deptsId:this.need_delet_deptId
			};
			this.axios.post("/api/dept/delete_depts", temp_data).then((res) => {
				if (res.data.errcode == 0){
					this.$toast("删除成功！");
					this.initUser().then((res) => {
						this.reload();
					});
					return;
				}
				this.$toast(res.data.hintmsg, "warning");
			});
		},


		cancelDept: function () {
			this.dept_name = this.name;
			this.dept_dialog = false;
		},

		changeDeptName: function () {
			let temp_data = {
				orgId: this.$store.state.selected_org.id,
				deptId: this.id,
				deptName: this.dept_name,
				level:this.change_depts
			};
			this.axios.post("/api/dept/modify_name", temp_data).then((res) => {
				if (res.data.errcode == 0) {
					this.$toast("修改成功！");
					this.reload();
				}
			});


		} 
	}
};
</script>

<style scoped>
.depts-title{
  width: 100%;
  font-size: 13px;
  text-align: left;
  height: 10px;
  color: #8a8a8a;

}
</style>