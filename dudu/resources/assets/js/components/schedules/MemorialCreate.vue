<template>
  <v-container>
    <v-form
      v-if="!isLoading"
      ref="form"
      v-model="valid"
    >
      <!-- <v-layout row wrap align-center style="padding: 0 16px;"> -->

      <v-text-field
        v-model="name"
        :rules="nameRules"
        :counter="20"
        label="纪念日标题"
        required
        @blur="scrollTo"
      />

      <v-textarea
        v-model="comment"
        rows="1"
        label="纪念日备注（选填）"
        @blur="scrollTo"
      />

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
          :rules="[v => !!v || '请选择日']"
          :items="day_items"
          label="日"
          @change="selectDate()"/>
      </v-layout>

      <v-select
        v-model="remind_at"
        :items="remind_items"
        :rules="dateRules"
        :disabled="start_at === ''"
        label="提醒时间"
        chips
        multiple
        hint="以纪念日当天上午 9:00 为开始时"
        persistent-hint
        required
        @change="getRemindTime()"
      />

      <v-layout 
        v-if="!is_edit && role !== 4" 
        row 
        class="mt-4">
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
            color="primary">
            <v-btn
              icon
              dark
              @click.native="dialog = false">
              <v-icon class="iconfont dudu-guanbi1"/>
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
            <!-- <v-select
                v-if="show_workgroup"
                v-model="workgroup"
                :items="workgroup_items"
                label="选择工作组"
                @change="chooseWorkgroup()"
              /> -->
            <v-container 
              v-if="show_target_type"
              class="pa-0"
            >
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

      <!-- <v-radio-group
        v-model="public_temp"
        row>
        <label>是否公开：</label>
        <v-radio
          label="是"
          value="1"/>
        <v-radio
          label="否"
          value="0"/>
      </v-radio-group> -->

      <v-btn
        v-btn-control="submit.bind(null, 0)"
        v-if="!is_edit"
        :loading="loading"
        :disabled="loading"
        block>
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
        <v-btn v-btn-control="submit.bind(null, 1)">更新</v-btn>
      </v-layout>

      <v-dialog
        ref="dialog"
        v-model="date_dialog"
        persistent
        lazy
        full-width
        width="290px"
      >
        <v-time-picker
          v-if="show_time_picker"
          v-model="time"
          format="24hr">
          <v-spacer/>
          <v-btn
            flat
            color="primary"
            @click="date_dialog = false; show_time_picker = false">取消
          </v-btn>
          <v-btn
            flat
            color="primary"
            @click="selectTime(time)">确定
          </v-btn>
        </v-time-picker>
      </v-dialog>
      <!-- </v-layout> -->
    </v-form>

    <Loading v-if="isLoading"/>
  </v-container>
</template>

<script>
import moment from "moment";
import {mapState, mapMutations} from "vuex";
import Treeselect from "@riophae/vue-treeselect";
import Loading from "../Commons/Loading";

export default {
	name: "MemorialCreate",
	components: {
		Treeselect,
		Loading
	},
	data() {
		return {
			id: null,
			valid: false,
			nameRules: [
				v => !!v || "标题不能为空",
				v => v.length <= 20 || "不可超过20个字符"
			],
			dateRules: [
				v => !!v || "请选择时间",
			],
			// remindTime: ["开始前20分钟", "开始前15分钟", "开始前5分钟", "开始时"],
			// repeatSetting: ["每日", "每周", "每月", "每季", "每年"],
			loading: false,

			date: null,
			time: null,
			menu: false,
			date_dialog: false,
			show_time_picker: false,

			// 创建 or 编辑
			is_edit: false,
			isLoading: true,

			// type: 3,
			name: "",
			comment: "",
			public_temp: "0",

			// 1 => 我, 0 => 他人
			setting_obj: false,
			self_schedule: true,
			repeat_setting: "",

			// 默认为公历
			is_solar: "1",
			month_items: null,
			solar_lunar_obj: {
				"1": "一", "2": "二", "3": "三",
				"4": "四", "5": "五", "6": "六",
				"7": "七", "8": "八", "9": "九",
				"10": "十", "11": "十一", "12": "十二",
				"13": "十三", "14": "十四", "15": "十五",
				"16": "十六", "17": "十七", "18": "十八",
				"19": "十九", "20": "二十", "21": "二一",
				"22": "二二", "23": "二三", "24": "二四",
				"25": "二五", "26": "二六", "27": "二七",
				"28": "二八", "29": "二九", "30": "三十",
				"31": "三一"
			},
			month_items_solar: ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"],
			month_items_lunar: [
				{text: "一", value: "1"}, {text: "二", value: "2"},
				{text: "三", value: "3"}, {text: "四", value: "4"},
				{text: "五", value: "5"}, {text: "六", value: "6"},
				{text: "七", value: "7"}, {text: "八", value: "8"},
				{text: "九", value: "9"}, {text: "十", value: "10"},
				{text: "十一", value: "11"}, {text: "十二", value: "12"},
			],
			day_items: [],
			remind_month: null,
			remind_day: null,
			start_at: "",
			remind_at: null,
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

			// 发送对象
			dialog: false,
			organization_items: [],
			organization: null, // org id
			send_target_chosen: "请选择发放对象",
			show_target_type: false,
			targets: null,
			targets_items_person: [],

			// 权限相关
			role: 4,
		};
	},
	computed: {
		...mapState(["selected_org"])
	},
	watch: {
		start_at() {
			if (this.remind_at !== null && this.remind_at.length !== 0) {
				this.getRemindTime();
			}
		}
	},
	created() {
		let id = this.$route.query.id;
		this.role = this.selected_org !== null ? this.selected_org.pivot.role_id : 4;
		if (id) {
			this.id = id;
			this.is_edit = true;
			this.init();
		} else {
			this.isLoading = false;
		}
		// 默认为公历
		this.month_items = this.month_items_solar;
	},
	methods: {
		...mapMutations(["getControlledOrgsSchedule", "getGroupsByOrg"]),
		// 0 => create  1 => update
		submit(num) {
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

			let postData = {
				name: this.name,
				comment: this.comment,
				start_at: this.start_at,
				is_solar: this.is_solar,
				remind_time: this.remind_time,
				is_self: this.self_schedule,
				is_others: this.setting_obj,
				public: parseInt(this.public_temp),
			};

			// 为他人创建日程的情况下，添加发送对象
			let targetsWithComma = "";
			if (this.setting_obj === true) {
				// 解决vue-treeselect 报错问题，targets 必须要是数组
				targetsWithComma = this.targets.join(",");
				postData.targets = targetsWithComma;
				postData.org_id = this.selected_org.id;
			}

			let url = "";
			if (num == 0) {
				url = "/api/schedule/create_memorial_day";
				// 有发送对象
				if (this.setting_obj === true) {
					postData.targets = this.targets.join(",");
				}
			} else {
				url = "/api/schedule/update_memorial_day";
				postData.id = this.id;
			}

			this.axios.post(url, postData)
				.then(res => {
					if (res.data.errcode == 0) {
						this.$toast("操作成功", "success");
						this.$router.back(-1);
					} else {
						this.$toast(res.data.data.errmsg, "error");
					}
				}).catch((err) => {
					// console.log(err);
				});
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

						let remind_date = data.start_at.split("-");
						this.remind_month = parseInt(remind_date[0]).toString();
						this.changeDayNums();

						this.remind_day = parseInt(remind_date[1]).toString();
						this.is_solar = data.is_solar.toString();

						// 换算提醒时间勾选项
						let arr_remind_at = [];
						data.remind_at.forEach((value, i) => {
							arr_remind_at.push(this.getRemindAt(data.start_at, value));
						});
						this.remind_at = arr_remind_at;
						this.getRemindTime();

						this.public_temp = data.public.toString();
						this.isLoading = false;
					} else {
						// fail
					}
				}).catch((err) => {
					// console.log(err);
				});
		},
		changeDayNums() {
			// 公历每个月可能有31 天或者30 天
			// 农历每个月可能有30 天，或者29 天
			this.remind_day = null;
			if (this.is_solar == 0) {
				this.month_items = this.month_items_lunar;
				// 农历，上限30
				if (this.remind_month == 2) {
					this.resizeDayItems(29);
				} else {
					this.resizeDayItems(30);
				}
			} else {
				this.month_items = this.month_items_solar;
				// 可能是30 天或者31 天
				if (this.isInArr(this.remind_month, ["1", "3", "5", "7", "8", "10", "12"])) {
					this.resizeDayItems(31);
				} else {
					this.resizeDayItems(30);
				}
			}
		},
		resizeDayItems(num) {
			this.day_items = [];
			// 公历
			if (this.is_solar == 1) {
				for (let i = 0; i < num; i++) {
					this.day_items.push((i + 1).toString());
				}
			} else {
				// 农历
				for (let i = 0; i < num; i++) {
					let item = {};
					item.text = this.solar_lunar_obj[(i + 1).toString()];
					item.value = (i + 1).toString();
					this.day_items.push(item);
				}
			}
		},
		getRemindTime() {
			if (!this.start_at) {
				this.$toast("请先选择纪念日期", "warning");
				return;
			}

			let current_year = new Date().getFullYear();
			let temp_start_at = `${current_year}/${this.start_at.replace(/-/g, "/")}`;
			let start_at_format = "";

			// 如果是全天事件，默认从开始那天的 9:00 开始计算提醒时间
			temp_start_at = moment(new Date(temp_start_at)).format("YYYY/MM/DD") + " " + "09:00";
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
				// 只获取时间部分字符串
				this.remind_time += `${temp_remind_time.split(" ")[1]},`;
			});
			this.remind_time = this.remind_time.substring(0, this.remind_time.length - 1);
		},
		// 根据remind_at 时间获取select 选项
		getRemindAt(start, remind) {
			let start_str = new Date(`2019-${start} 09:00:00`).getTime();

			let temp_remind = `2019-${start} `+remind.split(" ")[1];
			let remind_str = new Date(temp_remind).getTime();
			let sub = Math.abs(start_str - remind_str) / (60 * 1000);

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

			return "";
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
		selectDate() {
			this.start_at = "";
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
		selectTime(time) {
			if (time == null) {
				time = "00:00";
			}
			this.remind_at = time;

			this.date_dialog = false;
			this.show_time_picker = false;
			this.time = null;
		}
	}
};
</script>

<style scoped>
  .font-16 {
    display: inline-block;
    font-size: 16px;
    word-break: keep-all;
  }
</style>