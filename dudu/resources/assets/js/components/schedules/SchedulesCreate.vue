<template>
  <div>
    <v-form
      ref="form"
      v-model="valid"
      class="create-form"
    >
      <div>
        <!-- <v-layout row wrap align-center style="padding: 0 16px;"> -->

        <v-text-field
          v-model="name"
          :rules="nameRules"
          :counter="20"
          :label="pageName + '标题'"
          required
          @blur="scrollTo"
        />

        <v-textarea
          v-model="comment"
          :label="pageName + '备注（选填）'"
          rows="1"
          @blur="scrollTo"/>

        <v-radio-group
          v-if="type==1"
          v-model="fullday"
          row
          @change="isFullDay()">
          <label>全天事件：</label>
          <v-radio
            label="是"
            value="1"/>
          <v-radio
            label="否"
            value="0"/>
        </v-radio-group>


        <!-- 日程 -->
        <v-layout
          v-if="type==1"
          row
          wrap
          align-center
          justify-space-between>
          <v-flex xs5>
            <v-text-field
              v-model="start_at"
              :rules="dateRules"
              label="开始时间"
              required
              readonly
              @blur="scrollTo"
              @click="getStartTime()"
            />
          </v-flex>

          <span>至</span>
          <v-flex xs5>
            <v-text-field
              v-model="end_at"
              :rules="dateRules"
              label="结束时间"
              required
              readonly
              @blur="scrollTo"
              @click="getEndTime()"
            />
          </v-flex>
        </v-layout>

        <v-select
          v-if="type==1"
          v-model="remind_at"
          :items="remind_items"
          :rules="dateRules"
          :disabled="start_at === ''"
          label="提醒时间"
          chips
          multiple
          required
          @change="getRemindTime()"
        />

        <!-- 提醒 -->
        <v-text-field
          v-if="type==2"
          v-model="remind_at"
          :rules="[v => !!v || '请选择时间']"
          label="提醒时间"
          required
          readonly
          @blur="scrollTo"
          @click="getRemindTime()"
        />

        <!-- 纪念日 -->
        <div v-if="type==3">
          <v-radio-group
            v-model="is_solar"
            row
            @change="changeDayNums()">
            <label>日期属性：</label>
            <v-radio
              label="农历"
              value="0"/>
            <v-radio
              label="公历"
              value="1"/>
          </v-radio-group>

          <v-layout>
            <label class="font-16 width-80 mt-3">提醒日期：</label>
            <v-select
              v-model="remind_month"
              :items="month_items"
              :rules="[v => !!v || '请选择月份']"
              label="月份"
              class="mr-4"
              @change="changeDayNums()"/>
            <v-select
              v-model="remind_day"
              :rules="[v => !!v || '请选择天']"
              :items="day_items"
              label="天"
              @change="selectMemoDate()"/>
          </v-layout>


          <v-text-field
            v-model="remind_at"
            :rules="dateRules"
            label="提醒时间"
            required
            readonly
            @blur="scrollTo"
            @click="getRemindTime()"
          />

        </div>

        <v-layout
          v-if="!is_edit && role !== 4"
          row>
          <p style="font-size:16px">设置对象：</p>
          <v-checkbox
            v-model="self_schedule"
            xs3
            class="mt-0 pt-0 mr-3 flex-self-default"
            label="我"
            color="blue"
            hide-details
          />
          <v-checkbox
            v-model="setting_obj"
            :disabled="!selected_org"
            xs3
            class="mt-0 pt-0 flex-self-default"
            label="他人"
            color="blue"
            hide-details
          />
        </v-layout>

        <!-- 选择发送对象及弹框 start -->
        <v-text-field
          v-if="setting_obj === true"
          v-model="send_target_chosen"
          :rules="[v => (v && v != '请选择发放对象') || '请选择发放对象']"
          label="发放对象"
          background-color="white"
          readonly
          required
          @blur="scrollTo"
          @click="showSendTarget"
        />
        <v-dialog
          v-model="dialog"
          :fullscreen="true"
          scrollable
          transition="dialog-bottom-transition"
          max-width="100%">
          <v-card>
            <v-toolbar
              dark
              color="red">
              <v-btn
                icon
                dark
                @click.native="dialog = false">
                <v-icon 
                  small 
                  class="iconfont dudu-guanbi1"/>
              </v-btn>
              <v-toolbar-title class="subheading">选择发放对象</v-toolbar-title>
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
            <v-card-text style="height: 100vh;">
              <v-select
                v-if="organization_items"
                v-model="organization"
                :disabled="organization_items.length === 1"
                :items="organization_items"
                label="选择机构"
                @change="chooseOrganization()"
              />

              <v-container
                v-if="show_target_type"
                class="pa-0">
                <treeselect
                  v-model="targets"
                  :multiple="true"
                  :searchable="false"
                  :options="targets_items_person"
                  :max-height="200"
                  :always-open="true"
                  :default-expand-level="0"
                  :z-index="0"
                  no-options-text="机构中暂无可选成员"
                  open-direction="below"
                  value-consists-of="LEAF_PRIORITY"/>
              </v-container>
            </v-card-text>
            <v-divider/>
          </v-card>
        </v-dialog>
        <!-- 选择发送对象及弹框 end -->

        <v-radio-group
          v-if="type === 1"
          v-model="public_temp"
          row>
          <label>是否公开：</label>
          <v-radio
            label="是"
            value="1"/>
          <v-radio
            label="否"
            value="0"/>
        </v-radio-group>

        <v-btn
          v-btn-control="create"
          v-if="!is_edit"
          :loading="loading"
          :disabled="loading"
          block
          @click.native="loader = 'loading'">
          创建
        </v-btn>
        <v-layout
          v-if="is_edit"
          justify-center
        >
          <v-btn
            @click="cancleUpdate()"
          >
            取消
          </v-btn>
          <v-btn v-btn-control="update">更新</v-btn>
        </v-layout>


        <!--时间选择框-->
        <v-dialog
          ref="dialog"
          v-model="date_dialog"
          persistent
          lazy
          full-width
          width="290px"
        >
          <!--  日期弹框  -->
          <v-date-picker
            v-if="show_date_picker"
            v-model="date"
            :allowed-dates="allowedDates"
            locale="zh-cn"
            scrollable>

            <v-spacer/>
            <v-btn
              flat
              color="primary"
              @click="date_dialog = false; show_time_picker = false">取消
            </v-btn>
            <v-btn
              flat
              color="primary"
              @click="selectDate(date_type, date)">确定
            </v-btn>
          </v-date-picker>


          <v-time-picker
            v-if="show_time_picker"
            :allowed-hours="(start_at == now_date && date_type == 0) || (remind_at == now_date && date_type == 2) ? allowedHours_next : allowedHours"
            v-model="time"
            format="24hr">
            <v-spacer/>

            <v-btn
              flat
              color="primary"
              @click="selectTime(date_type, time)">确定
            </v-btn>
          </v-time-picker>
        </v-dialog>

        <!-- </v-layout> -->
      </div>
    </v-form>
  </div>

</template>

<script>
import moment from "moment";
import {mapState, mapMutations} from "vuex";
import Treeselect from "@riophae/vue-treeselect";

export default {
	name: "SchedulesCreate",
	components: {
		Treeselect
	},
	data() {
		return {
			valid: false,

			nameRules: [
				v => !!v || "标题不能为空",
				v => v.length <= 20 || "不可超过20个字符"
			],
			dateRules: [v => !!v || "请选择时间"],
			loading: false,

			date: null,
			time: null,
			menu: false,
			date_dialog: false,
			show_time_picker: false,
			show_date_picker: false,
			date_type: null,

			now_date: null,   // 获取当前的日期
			now_times: null,  // 获取当前的时间
			now_hours: null,  // 获取当前时间的小时数
			save_hours: null, // 获取选择时间的小时数

			is_edit: false,

			id: null,

			// 1 => 日程, 2 => 提醒, 3 => 纪念日
			type: 1,
			pageName: "日程",
			name: "",
			comment: "",
			start_at: null,
			end_at: "",
			remind_at: "",
			remind_time: "",
			remind_items: [
				{
					value: 1,
					text: "开始时"
				},
				{
					value: 5,
					text: "开始前5分钟"
				},
				{
					value: 10,
					text: "开始前10分钟"
				},
				{
					value: 15,
					text: "开始前15分钟"
				},
				{
					value: 30,
					text: "开始前30分钟"
				},
				{
					value: 60,
					text: "开始前1小时"
				},
				{
					value: 24 * 60,
					text: "开始前1天"
				},
				{
					value: 48 * 60,
					text: "开始前2天"
				},
				{
					value: 7 * 24 * 60,
					text: "开始前1周"
				},
			],
			public_temp: "0",
			fullday: "0",

			// 纪念日变量
			is_solar: "1",
			month_items: ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"],
			day_items: [],
			remind_month: null,
			remind_day: null,

			// 发送对象
			dialog: false,
			organization_items: [],
			organization: null, // org id
			workgroup_items: [], // groups of selected org
			show_workgroup: false,
			send_target_chosen: "请选择发放对象",
			show_target_type: false,
			targets: null,
			targets_items_person: [],

			end_repeat: "",
			setting_obj: false,
			self_schedule: true,
			repeat_setting: "",

			// 权限相关
			role: 4,
		};
	},
	computed: {
		...mapState(["selected_org"])
	},
	watch: {
		start_at() {
			if (this.remind_at.length !== 0) {
				this.getRemindTime();
			}
		},
	},

	created() {
		let id = this.$route.query.id;
		let type = parseInt(this.$route.query.type);
		this.role = this.selected_org !== null ? this.selected_org.pivot.role_id : 4;
		if (id) {
			this.id = id;
			this.is_edit = true;
			this.init();
		}

		// 判断是日程还是提醒
		if (type) {
			this.type = type;
			this.pageName = this.type === 1 ? "日程" : this.type === 2 ? "提醒" : "纪念日";
		}
	},
	mounted () {
		this.now_date = this.formatDate(new Date(), "yyyy-MM-dd hh:mm").split(" ")[0];  // 在加载时获取到当前的日期
		this.now_times = this.formatDate(new Date(), "yyyy-MM-dd hh:mm").split(" ")[1]; // 加载当前的时间

	},
	methods: {
		...mapMutations(["getControlledOrgsSchedule", "getGroupsByOrg"]),
		submit(url) {
			if (!this.$refs.form.validate()) return;

			if (this.name.trim().length === 0){
				this.$toast("请输入标题","error");
				return ;
			}

			// if ((this.comment.trim().length === 0)){
			// 	this.$toast("请输入内容","error");
			// 	return;
			// }

			if (this.self_schedule === false && this.setting_obj === false){
				this.$toast("请设置对象","error");
				return;
			}
			// 日程中remind_at 是一个数字，提醒中是一个时间点
			let remindTime = this.type == 1 ? this.remind_time : this.remind_at;
			let data = {
				type: this.type,
				name: this.name,
				comment: this.comment,
				start_at: this.start_at,
				end_at: this.end_at,
				remind_at: remindTime,
				is_self: this.self_schedule,
				is_others: this.setting_obj,
				public: parseInt(this.public_temp),
				fullday: parseInt(this.fullday)
			};

			// 为他人创建日程的情况下，添加发送对象
			let targetsWithComma = "";
			if (this.setting_obj === true) {
				// 解决vue-treeselect 报错问题，targets 必须要是数组 todo
				targetsWithComma = this.targets.join(",");
				data.targets = targetsWithComma;
				data.org_id = this.selected_org.id;
			}

			return this.axios.post(url, data)
				.then((res) => {
					if (res.data.errcode == 0) {
						this.$router.push("/schedules");
					} else {
						alert("新增失败，请稍后重试");
					}
				}).catch((err) => {
					// console.log(err);
				});
		},

		create() {
			let url = "/api/schedule/create";
			return this.submit(url);
		},
		update() {
			let url = `/api/schedule/update/${this.id}`;
			return this.submit(url);
		},
		cancleUpdate() {
			this.$router.push({path: "/schedule/detail", query: {id: this.id}});
		},
		init() {
			this.axios.get(`/api/schedule/detail/${this.id}`)
				.then((res) => {
					if (res.data.errcode == 0) {
						let data = res.data.data;
						this.type = data.type;
						this.name = data.name;
						this.comment = data.comment;
						this.start_at = data.start_at;
						this.end_at = data.end_at;
						this.fullday = data.fullday.toString();
						// 日程
						if (this.type == 1) {
							let startAt = data.start_at;
							if (this.fullday == 1) {
								// 全天事项，开始时间默认为早上九点
								startAt = moment(new Date(data.start_at.replace(/-/g, "/"))).format("YYYY/MM/DD") + " " + "09:00";
							}

							let arr_remind_at = [];
							data.remind_at.forEach((value, i) => {
								arr_remind_at.push(this.getRemindAt(startAt, value));
							});
							this.remind_at = arr_remind_at;
							this.getRemindTime();
						}
						// 提醒
						if (this.type == 2) {
							this.remind_at = data.remind_at[0];
						}

						this.public_temp = data.public.toString();
						this.isLoading = false;
					} else {
						// fail
					}
				}).catch((err) => {
					// console.log(err);
				});
		},
		getStartTime() {
			this.show_date_picker = true;
			this.date_dialog = true;
			this.date_type = 0;
		},


		getEndTime() {
			if (!this.start_at) {
				this.$toast("请先选择开始时间！", "warning");
				return;
			}
			this.show_date_picker = true;
			this.date_dialog = true;
			this.date_type = 1;
		},
		getRemindTime() {
			// type 1 => 日程, 2 => 提醒, 3 => 纪念日
			if (this.type == 1) {
				if (!this.start_at) {
					this.$toast("请先选择开始时间！", "warning");
					return;
				}

				let temp_start_at = this.start_at.replace(/-/g, "/");

				let start_at_format = "";

				// 如果是全天事件，默认从开始那天的 9:00 开始计算提醒时间
				if (this.fullday == "1") {
					temp_start_at = moment(new Date(temp_start_at)).format("YYYY/MM/DD") + " " + "09:00";
				}

				start_at_format = new Date(temp_start_at);

				this.remind_time = "";
				this.remind_at.forEach((value, i) => {
					let temp_remind_time = "";
					switch (value) {
					case 1:
						temp_remind_time = moment(start_at_format).subtract(0, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 5:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 10:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 15:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 30:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 60:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 24 * 60:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 48 * 60:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					case 7 * 24 * 60:
						temp_remind_time = moment(start_at_format).subtract(value, "m").format("YYYY-MM-DD HH:mm");
						break;
					}
					this.remind_time += `${temp_remind_time},`;
				});
				this.remind_time = this.remind_time.substring(0, this.remind_time.length - 1);
			}

			// 如果是提醒，提醒时间选择的形式是date picker
			if (this.type == 2) {
				this.show_date_picker = true;
				this.date_dialog = true;
				this.date_type = 2;
			}

			if (this.type == 3) {
				// this.date_dialog = true;
				this.show_time_picker = true;
			}
		},

		allowedDates(val) {
			// 鹏杰之前这里改错了，导致逻辑判断错误 这种写法会导致结束时间有问题
			// 	// 如果是创建提醒，时间选择上小于当前时间
			// 	if (this.type == 2 || this.type == 1 ) {
			// 		let start_at_date =  new Date(+new Date()+8*3600*1000).toISOString().replace(/T/g," ").replace(/\.[\d]{3}Z/,"").substr(0, 10); // 获取选择的日期
			// 		return val >= start_at_date; // 返回包含当天之后的日期
			// 	}

			// 给开始时间增加限制,限制不允许小于当前时间
			if (this.date_type == 0) {
				let start_at_date = new Date(+new Date()+8*3600*1000).toISOString().replace(/T/g," ").replace(/\.[\d]{3}Z/,"").substr(0, 10);
				return val >= start_at_date; // 返回包含当天之后的日期
			}

			// 选择结束日期
			if (this.date_type == 1) {
				let start_at = this.start_at.split(" ")[0];
				return val >= start_at;
			}

			// 选择提醒日期
			if (this.date_type == 2) {
				let start_at_date =  new Date(+new Date()+8*3600*1000).toISOString().replace(/T/g," ").replace(/\.[\d]{3}Z/,"").substr(0, 10); // 获取选择的日期
				return val >= start_at_date; // 返回包含当天之后的日期
				// let start_at = this.start_at.split(" ")[0];
				// return val <= start_at;
			}
			return true;
		},
		allowedHours(val) {
			// 如果是创建提醒，时间选择上没有限制
			if (this.type == 2) {
				return true;
			}
			// 如果结束时间跟开始时间同一天，结束的hour 必须在开始之后
			if (this.date_type == 1 && this.end_at == this.start_at.split(" ")[0]) {
				let time = this.start_at.split(" ")[1].split(":")[0];
				return val > time;
			}
			if (this.date_type == 2 && this.remind_at == this.start_at.split(" ")[0]) {
				let time = this.start_at.split(" ")[1].split(":")[0];
				return val < time;
			}
			return true;
		},

		allowedHours_next(val) { // 返回当前小时
			if (this.date_type == 0) {
				let start_at_hours = new Date().getHours();
				return val >= start_at_hours;
			}

			if (this.date_type == 2) {
				let start_at_hours = new Date().getHours();
				return val >= start_at_hours;
			}
		},

		// 发送对象选择
		showSendTarget() {
			this.dialog = true;
			let obj = {
				// 1 => 超级管理员 2 => 系统管理员
				arr: [1, 2],
				res: this.organization_items
			};
			// 直接从VueX 中获取当前用户可以发送通知的机构
			this.getControlledOrgsSchedule(obj);
			this.organization_items.forEach(function (value, index) {
				value.text = value.name;
				value.value = value.id;
			});

			if (this.organization_items.length == 1) {
				this.organization = this.organization_items[0].value;
				this.chooseOrganization();
				return;
			}
		},

		// 选择机构后无需选择群组直接显示部门
		chooseOrganization() {
			this.targets_items_person = [];
			this.axios.get(`/api/org/users_by_depts/${this.organization}`).then((res) => {
				let data = res.data.data;
				for (let index in res.data.data) {
					var temp_data = {
						id: index,
						label: index,
						children: []
					};
					// 把除自己外的所有人添加到 部门 => 人员 数组中
					for (let i = data[index].length - 1; i >= 0; i--) {
						if (data[index][i].id == this.$store.state.user_info.id) {
							data[index].splice(i, 1);
							continue;
						}
						data[index][i].label = data[index][i].name;
					}
					temp_data.children = data[index];
					if (temp_data.children.length == 0) {
						continue;
					}
					this.targets_items_person.push(temp_data);
				}
			}).catch((Err) => {

			});
			this.show_target_type = true;
		},

		finishChooseTargets() {
			if (!this.targets || this.targets.length == 0) {
				this.$toast("请选择对象", "warning");
				return;
			}
			this.dialog = false;
			this.send_target_chosen = "已选择";
		},

		// 选择时间 年月份
		selectDate(date_type, date) {
			if (date == null) {
				// date = moment().format("YYYY-MM-DD");
				this.$toast("请先选择日期", "warning");
				return;
			}
			switch (date_type) {
			case 0:
				this.start_at = date;
				if (this.fullday == "1") {
					this.end_at = "";
				}
				break;
			case 1:
				this.end_at = date;
				break;
			case 2:
				this.remind_at = date;
				break;
			}
			if (this.fullday == "1") {
				this.show_time_picker = false;
				this.date_dialog = false;
			} else {
				this.show_time_picker = true;
			}
			this.show_date_picker = false;
			this.date = null;
		},

		// 选择时间，时分
		selectTime(date_type, time) {
			if (time == null) {
				this.$toast("请选择时间", "warning");
				return;
			}

			let selected_timeMinutes = this.time.split(":")[1];   // 选择到的时间的分钟数
			let now_timeMinutes = new Date().getMinutes();        // 当天时间的分钟数
			let now_timeHours   = new Date().getHours();          // 小时数

			// 纪念日逻辑
			if (this.type == 3) {
				this.selectMemoTime(time);
				return;
			}

			switch (date_type) {
			case 0:
				// 选择的时间的小时数
				this.save_hours = this.time.split(":")[0];

				// 判断当前所选的日期是否与当前日期一致
				if (this.start_at == this.now_date) {
					// 如果选择的小时数等于当前小时数
					if (this.save_hours == now_timeHours) {
						// 判断分钟数是否小于当前的分钟数
						if(selected_timeMinutes < now_timeMinutes){
							// 则采用当前时间
							this.start_at += ` ${this.now_times}`;
						}else{
							this.start_at += ` ${this.time}`;
						}
					}else{
						this.start_at += ` ${this.time}`;
					}

				} else {
					this.start_at += ` ${this.time}`;
				}
				// 获取选中的 开始时间
				var tmp_time_str = new Date(this.start_at);
				// 开始时间加1个钟
				var tmp_end_time = new Date(tmp_time_str.getTime() + 60*60*1000);
				// 返回格式化的时间
				this.end_at = this.formatDate(tmp_end_time, "yyyy-MM-dd hh:mm");

				break;
			case 1:
				this.end_at += ` ${time}`;
				break;

			case 2:
				// 提醒
				this.selectedTimeFresh(this.time);
				break;
			}

			this.date_dialog = false;
			this.show_time_picker = false;

			this.time = null;
		},

		// 选择时间处理
		selectedTimeFresh(selectedtime){
			let now_times  = this.formatDate(new Date(), "yyyy-MM-dd hh:mm").split(" ")[1]; // 加载当前的时间
			let selected_times = selectedtime.split(":")[0];          // 选择时间的小时数
			let selected_minutes = selectedtime.split(":")[1];        // 选择时间的分钟数
			let now_timeHours = new Date().getHours();        // 当天的小时数
			let now_minutes = new Date().getMinutes();        // 当天时间分钟数

			if(this.remind_at === this.now_date){
				// 选择到的时数也是选择的时数
				if (selected_times == now_timeHours){
					// 判断当前选择时间是否小于当前时间
					now_minutes > selected_minutes ?  this.remind_at += ` ${now_times}`:this.remind_at += ` ${this.time}`;
				}else{
					this.remind_at += ` ${this.time}`;
				}
			}else{
				this.remind_at += ` ${this.time}`;
			}
		},

		// 纪念日选择日期和时间
		selectMemoDate() {
			let month = this.remind_month;
			let day = this.remind_day;
			if (this.remind_month < 10) {
				month = "0" + this.remind_month;
			}
			if (this.remind_day < 10) {
				day = "0" + this.remind_day;
			}
			this.start_at = month + "-" + day;
		},
		selectMemoTime(time) {
			if (time == null) {
				time = "00:00";
			}
			this.remind_at = time;

			this.date_dialog = false;
			this.show_time_picker = false;
			this.time = null;
		},
		isFullDay() {
			this.start_at = "";
			this.end_at = "";
			this.remind_at = "";
		},
		// 根据remind_at 时间获取select 选项
		getRemindAt(start, remind) {
			let sub = moment(start).diff(moment(remind), "m");
			switch (sub) {
			case 0:
				return this.remind_items[0].value;
			case 5:
				return this.remind_items[1].value;
			case 10:
				return this.remind_items[2].value;
			case 15:
				return this.remind_items[3].value;
			case 30:
				return this.remind_items[4].value;
			case 60:
				return this.remind_items[5].value;
			case 24 * 60:
				return this.remind_items[6].value;
			case 48 * 60:
				return this.remind_items[7].value;
			case 7 * 24 * 60:
				return this.remind_items[8].value;
			}
		}
	}
};
</script>

<style scoped>
.create-form {
background-color: white;
padding: 1rem;
}

.font-16 {
display: inline-block;
font-size: 16px;
word-break: keep-all;
}
</style>