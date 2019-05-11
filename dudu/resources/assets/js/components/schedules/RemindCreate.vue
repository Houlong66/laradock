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
          label="提醒标题"
          required
          @blur="scrollTo"
        />

        <v-textarea
          v-model="comment"
          rows="2"
          label="提醒备注"
          hint="提醒备注"
          @blur="scrollTo"/>
          
        <v-text-field
          v-model="start_at"
          :rules="dateRules"
          label="日期设置"
          required
          readonly
          @blur="scrollTo"
          @click="getRemindTime()"
        />

        <v-select
          v-model="remind_at"
          :items="remindTime"
          required
          label="提醒时间"
        />

        <v-select
          v-model="repeat_setting"
          :items="repeatSetting"
          label="重复设置"
        />

        <v-select
          v-model="end_repeat"
          :items="endRepeat"
          label="结束重复"
        />

        <v-radio-group
          v-model="setting_obj"
          row>
          <label>设置对象：</label>
          <v-radio 
            label="我" 
            value="1"/>
          <v-radio 
            label="他人" 
            value="0"/>
        </v-radio-group>

        <v-radio-group
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
          :loading="loading"
          :disabled="loading"
          color="primary"
          block
          @click="submit"
          @click.native="loader = 'loading'">
          创建
        </v-btn>

        <v-dialog
          ref="dialog"
          v-model="date_dialog"
          persistent
          lazy
          full-width
          width="290px"
        >
          <v-date-picker
            v-if="show_date_picker"
            v-model="date"
            locale="zh-cn"
            scrollable>
            <v-spacer/>
            <v-btn 
              flat 
              color="primary" 
              @click="date_dialog = false; show_time_picker = false">取消</v-btn>
            <v-btn 
              flat 
              color="primary" 
              @click="selectDate(date)">确定</v-btn>
          </v-date-picker>
          <v-time-picker
            v-if="show_time_picker"
            v-model="time"
            format="24hr">
            <v-spacer/>
            <v-btn 
              flat 
              color="primary" 
              @click="date_dialog = false; show_time_picker = false">取消</v-btn>
            <v-btn 
              flat 
              color="primary" 
              @click="selectTime(time)">确定</v-btn>
          </v-time-picker>
        </v-dialog>
        <!-- </v-layout> -->
      </div>
    </v-form>
  </div>
</template>

<script>
import moment from "moment";

export default {
	name: "SchedulesCreate",
	data () {
		return {
			valid: false,
      
			nameRules: [
				v => !!v || "标题不能为空",
				v => v.length <= 20 || "不可超过20个字符"
			],
			dateRules: [
				v => !!v || "请选择时间",
			],
			remindTime: ["开始前20分钟", "开始前15分钟", "开始前5分钟", "开始时"],
			repeatSetting: ["每日", "每周", "每月", "每季", "每年"],
			endRepeat: ["永不", "次数", "日期"],
			loading: false,

			date: null,
			time: null,
			menu: false,
			date_dialog: false,
			show_time_picker: false,
			show_date_picker: false,
			date_type: null,

			type: 2,
			name: "",
			comment: "",
			start_at: "",
			end_at: "",
			remind_at: "",
			public_temp: "0",
			fullday: "0",
			end_repeat: "",
			setting_obj: "1",
			repeat_setting: "",
		};
	},
	created () {

	},
	methods: {
		submit () {
			if (!this.$refs.form.validate()) return;

			// 是否选提醒时间
			if (this.remind_at) {
				let temp_start_at = this.start_at.replace(/-/g, "/");
				let start_at_format = "";
				// 如果是全天事件，默认从开始那天的 9:00 开始计算提醒时间 
				if (this.fullday == "1") {
					temp_start_at = moment(new Date(temp_start_at)).format("YYYY/MM/DD") + " " + "09:00";
				}
				start_at_format = new Date(temp_start_at);
				switch (this.remindTime.indexOf(this.remind_at)) {
				case 0:
					this.remind_at = moment(start_at_format).subtract(20, "m").format("YYYY-MM-DD HH:mm"); break;
				case 1:
					this.remind_at = moment(start_at_format).subtract(15, "m").format("YYYY-MM-DD HH:mm"); break;
				case 2:
					this.remind_at = moment(start_at_format).subtract(5, "m").format("YYYY-MM-DD HH:mm"); break;
				case 3:
					this.remind_at = moment(start_at_format).subtract(0, "m").format("YYYY-MM-DD HH:mm"); break;
				}
			}

			let data = {
				type: this.type,
				name: this.name,
				comment: this.comment,
				start_at: this.start_at,
				end_at: this.start_at,
				remind_at: this.remind_at,
				public: parseInt(this.public_temp),
				fullday: parseInt(this.fullday)
			};

			this.axios.post("/api/schedule/create", data)
				.then((res) => {
					if (res.data.errcode == 0) {
						this.$toast("创建成功", "success");
						this.$router.push("/schedules");
					} else {
						this.$toast("创建失败", "error");
					}
				}).catch((err) => {
					// console.log(err);
				});
		},
		getRemindTime() {
			this.date_dialog = true;
			this.show_date_picker = true;
		},

		/**
     * return date
     * @param  {int} date_type 0: start_at, 1: end_at
     * @param  {String} date      date
     */
		selectDate (date_type, date) {
			if (date == null) {
				// date = moment().format("YYYY-MM-DD");
				this.$toast("请先选择日期", "warning");
				return;
			}

			this.start_at = date;

			this.show_date_picker = false;
			this.show_time_picker = true;
			this.date = null;
		},
		selectTime (date_type, time) {
			if (time == null) {
				time = "00:00";
			}
			this.start_at += ` ${time}`;

			this.date_dialog = false;
			this.show_time_picker = false;
			this.time = null;
		}
	}
};
</script>

<style scoped>
  .create-form {
    background-color: white;
    padding: 1rem;
  }
</style>