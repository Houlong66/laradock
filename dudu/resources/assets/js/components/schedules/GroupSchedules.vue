<template>
  <v-container>
    <v-flex class="pb-1">
      <div>
        <p class="title pb-2 mb-1 border-b">共享日程</p>
      </div>
    </v-flex>
    <div v-if="!isLoading">
      <v-form
        ref="form"
        v-model="valid"
        class="mt-2"
        lazy-validation>

        <v-select
          v-show="group_list.length !== 1"
          v-model="chosen_group"
          :items="group_list"
          :rules="[v => !!v || '请选择群组']"
          box
          background-color="white"
          label="群组选择"
        />

        <!-- 日期选择，日期，开始时间，结束时间 -->
        <v-text-field
          v-model="chosen_date"
          :rules="[v => !!v || '请选择时间']"
          box
          background-color="white"
          label="选择日期"
          readonly
          @blur="scrollTo"
          @click="show_dialog = true; show_date = true;"
        />

        <!--        <v-layout-->
        <!--          row-->
        <!--          wrap-->
        <!--          align-center-->
        <!--          justify-space-between>-->
        <!--          <v-flex xs5>-->
        <!--            <v-text-field-->
        <!--              v-model="start_at"-->
        <!--              :rules="dateRules"-->
        <!--              label="开始时间"-->
        <!--              required-->
        <!--              box-->
        <!--              background-color="white"-->
        <!--              readonly-->
        <!--              @blur="scrollTo"-->
        <!--              @click="getTime(0)"-->
        <!--            />-->
        <!--          </v-flex>-->
        <!--          <span>至</span>-->
        <!--          <v-flex xs5>-->
        <!--            <v-text-field-->
        <!--              v-model="end_at"-->
        <!--              :rules="dateRules"-->
        <!--              label="结束时间"-->
        <!--              required-->
        <!--              box-->
        <!--              background-color="white"-->
        <!--              readonly-->
        <!--              @blur="scrollTo"-->
        <!--              @click="getTime(1)"-->
        <!--            />-->
        <!--          </v-flex>-->
        <!--        </v-layout>-->

        <v-dialog
          ref="menu"
          v-model="show_dialog"
          persistent
          lazy
          full-width
          width="290px"
        >
          <!-- 日期选择器 -->
          <v-date-picker
            v-if="show_date"
            v-model="chosen_date"
            no-title
            locale="zh-cn"
            scrollable>
            <v-spacer/>
            <v-btn
              flat
              color="primary"
              @click="show_date = false; show_dialog = false">Cancel
            </v-btn>
            <v-btn
              flat
              color="primary"
              @click="show_date = false; show_dialog = false">OK
            </v-btn>
          </v-date-picker>
          <!-- 时间选择器 -->
          <v-time-picker
            v-if="show_time"
            v-model="chosen_time"
            :allowed-hours="allowedHours"
            format="24hr"
            @change="saveTime()"
          />
        </v-dialog>

        <v-spacer/>

        <v-layout
          justify-space-between>
          <v-flex>

            <v-btn
              v-btn-control="submit.bind(null, 0)"
              dark
              color="blue"
              class="submit-btn mx-0"
            >
              确认查看
            </v-btn>

          </v-flex>
        </v-layout>

        <v-layout>
          <v-flex>
            <span>当前选中时间段：{{ chosen_date }} {{ start_at }}-{{ end_at }}</span>
          </v-flex>
        </v-layout>

        <!-- 日程显示列表 -->
        <!-- header -->
        <v-layout
          v-if="schedule_list"
          class="list_item mt-3">
          <v-flex
            xs5
            align-self-center>
            <span>成员</span>
          </v-flex>
          <v-flex xs7>
            <span>日程</span>
          </v-flex>
        </v-layout>
        <!-- content -->
        <v-layout
          v-for="(val, key) in schedule_list"
          :key="key"
          class="list_item pb-2 pt-2">
          <v-flex
            xs5
            class="flex-center">
            <span>{{ key }}</span>
          </v-flex>
          <v-flex xs7>
            <v-flex
              v-for="(schedule, index) in schedule_list[key]"
              :class="{'pt-border': index != 0, 'pb-1': index != schedule_list[key].length - 1}"
              :key="index">
              {{ schedule.name }} ({{ schedule.start_at.split(" ")[1] }}-{{ schedule.end_at.split(" ")[1] }})
            </v-flex>
            <v-flex v-if="!schedule_list[key].length">空闲</v-flex>
          </v-flex>
        </v-layout>

      </v-form>
    </div>

    <component
      v-if="isLoading"
      :is="cView"
    />

  </v-container>
</template>

<script>
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import { mapState, mapMutations } from "vuex";
import Loading from "../Commons/Loading";
var today = new Date();
export default {
	components: {
		Loading
	},
	data: () => ({
		valid: false,
		// group 列表
		group_list: [],
		chosen_group: null,
		// 日期选择
		dateRules: [v => !!v || "请选择时间"],
		show_dialog: false,
		show_date: false,
		show_time: false,
		chosen_date: null,
		chosen_time: null,
		start_at: null,
		end_at: null,
		schedule_list: null,
		cView: "Loading",
		isLoading: false,
	}),
	computed: {
		...mapState(["user_info"])
	},
	watch: {
		"chosen_date":function(newval,oldval){
		},
		"start_at"(newval,oldval){
		}
	},
	mounted: function () {
		this.initGroupList();
		this.chosen_date = this.chosen_date_start();
		this.start_at_start();
	},

	methods: {
		...mapMutations(["getControlledOrgs", "getControlledGroupsByOrg"]),
		// 获取当前用户所在的群组
		initGroupList () {
			this.user_info.groups.forEach(val => {
				if(val.type === 1){
					let item = {
						text: val.name,
						value: val.id,
					};
					this.group_list.push(item);
				}
			});
		},

		// type == 0 => start 1 => end
		getTime (type) {
			if(type == 1 && !this.start_at) {
				this.$toast("请先选择开始时间！", "warning");
				return;
			}
			this.show_dialog = true;
			this.show_time = true;
			this.date_type = type;
		},

		saveTime () {
			if(this.date_type == 0) {
				this.start_at = this.chosen_time;
			} else {
				this.end_at = this.chosen_time;
			}
			this.chosen_time = null;
			this.show_time = false;
			this.show_dialog = false;
		},
		allowedHours (val) {
			if(this.date_type == 0) {
				return true;
			} else {
				let time = this.start_at.split(":")[0];
				return val > time;
			}
		},

		// 任务发布或流转审批
		submit(if_audit) {
			if (this.$refs.form.validate()) {

				let start = this.chosen_date + " " + this.start_at;

				let end = this.chosen_date + " " + this.end_at;

				let postData = {
					group_id: this.chosen_group,
					start_time: start,
					end_time: end,
				};

				this.axios.post("/api/schedule/group_schedule", postData).then(res => {
					if(res.data.errcode == 0) {
						this.schedule_list = res.data.data;
					} else {
						this.$toast(res.data.errmsg, "error");
					}
				}).then(err => {
				});
			}
		},
		group_active(){

		},
		chosen_date_start(){
			var year = today.getFullYear();
			var month = today.getMonth()+1;
			var day = today.getDate()+1;
			return year+"-"+ month +"-"+ day;
		},
		start_at_start(){
			if(this.group_list.length == 1){
				this.chosen_group = this.group_list[0].value;
			}
			this.start_at = "09:00"; // 原来为9:00,是因为默认开始时间与组件时间格式不一致所导致查找有误
			this.end_at = "18:00";
		}
	},
};
</script>

<style scoped>
.report-setting-box {
background: #fff;
}
.submit-btn {
width: 100%;
}
.audit-box {
background: #f44336;
color: #fff;
}
.list_item {
border-bottom: 1px solid #f1f1f1;
}
.pt-border {
padding-top: 4px;
border-top: 1px dashed #f2f2f2;
}
.flex-center {
display: flex;
align-items: center;
}

</style>