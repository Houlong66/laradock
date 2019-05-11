<template>
  <div
    v-touch="{
      up: () => touch('up'),
      down: () => touch('down')
  }">

    <div id="calendar">
      <div class="mt-2">
        <v-icon
          v-if="isWexin"
          class="grey--text iconfont dudu-arrow_down"
          @click="toggleCalendar()"/>
      </div>
      <div class="flex xs12 sm6">
        <div class="top-schedule">
          {{ year }}年{{ month }}月{{ day }}日, 农历{{ lunar_date }}
        </div>
      </div>
      <div
        v-if="!show_complete_calendar"
        class="mt-5"/>
      <week-slider
        v-if="!show_complete_calendar"
        :default-date="week_slider_date"
        :hasevents="events"
        @weekSlide="weekSlide"
        @getWeekEventList="getWeekEventList"
        @dateClick="dateClickhandler"/>
      <calendar
        v-touch="{
          left: () => touch('left'),
          right: () => touch('right'),
          up: () => touch('up'),
          down: () => touch('down')
        }"
        v-if="show_complete_calendar"
        :mark="events"
        :select="week_slider_selected_date"
        @clickMockTouch="clickMockTouch"
        @getDayList="getDayList"
        @choseDay="choseDay"/>
    </div>
    <component
      :is="cView"
      :reminders="reminders"
      :schedules="schedules"
      :memorials="memorials"
      :selected-date="week_slider_date"
    />
    <div 
      class="btn-count" 
      style="position:fixed; bottom:150px; right:5%;">
      <v-btn
        v-if="can_in_group_schedules"
        fab
        dark
        small
        color="red"
        @click="showGroupSchedules()">
        <span class="font-weight-bold">共享</span>
      </v-btn>
    </div>
    <div 
      class="btn-count" 
      style="position:fixed; bottom:100px; right:5%;">
      <v-layout>
        <v-flex xs-11>
          <div :class="{ btnBoxShow: create_btn_flag }">
            <v-btn
              fab
              dark
              small
              color="red"
              @click="showCreateSchedule()">
              <span class="font-weight-bold">日程</span>
            </v-btn>
            <v-btn
              fab
              small
              dark
              color="red"
              @click="showCreateRemind()">
              <span class="font-weight-bold">提醒</span>
            </v-btn>
            <v-btn
              fab
              small
              dark
              color="red"
              @click="showCreateMemorial()">
              <span class="font-weight-bold">纪念日</span>
            </v-btn>
          </div>
        </v-flex>
        <v-flex xs-1>
          <v-btn
            :class="{ btnRotation: !create_btn_flag }"
            fab
            small
            dark
            color="red"
            @click="showCreateBtnBox()">
            <v-icon 
              dark 
              class="iconfont dudu-tianjia1"/>
          </v-btn>
        </v-flex>
      </v-layout>
    </div>
  </div>
</template>

<script>
import ScheduleList from "../components/schedules/ScheduleList";
import Loading from "../components/Commons/Loading";
import Calendar from "../components/schedules/calendar/index";
import Lunar from "../components/schedules/calendar/lunar";
import WeekSlider from "../components/schedules/calendar/weekSlider";
import moment from "moment";

export default {
	name: "Schedules",
	components: {
		Loading,
		ScheduleList,
		Calendar,
		WeekSlider
	},
	data() {
		return {
			cView: "Loading",
			create_btn_flag: 1,
			reminders: [],
			schedules: [],

			memorials: [],

			events: [],
			year: "",
			month: "",
			day: "",
			lunar_date: "",
			ymd: moment().format("YYYYMMDD"),
			show_complete_calendar: false,
			week_slider_date: moment().format("YYYY-MM-DD"),
			week_slider_selected: "",
			week_slider_selected_date: moment().format("YYYY/MM/DD"),
			next_month: false,
			pre_month: true,
			isWexin: false,
			can_in_group_schedules: false,
		};
	},
	created() {
		// 判断当前运行环境
		this.check();
		// moment.locale('zh-cn');
		this.year = moment().format("Y");
		this.month = moment().format("M");
		this.day = moment().format("D");
		let today = moment().format("YYYY/M/D").split("/");
		let lunar_today = Lunar.toLunar(today[0], today[1], today[2]);
		this.lunar_date = lunar_today[0] + " " + lunar_today[1];
		this.getRemind(this.ymd);
		this.getSchedule(this.ymd);
		this.getMemorial(this.ymd);

		// this.getEvents();
		document.addEventListener("touchmove", function (event) {
			if (event.cancelable && document.querySelector("#calendar")) {
				if (!event.defaultPrevented && document.querySelector("#calendar").contains(event.target)) {
					event.preventDefault();
				}
			}
		}, {passive: false});
		// pa 端微信浏览器
		this.isWexin = this.isWxBrowser() && !(this.isAndroid() || this.isIos());
	},
	methods: {
		showCreateBtnBox() {
			this.create_btn_flag = this.create_btn_flag === 1 ? 0 : 1;
		},
		getWeekEventList(dateList) {
			let from = this.formatDate2(dateList[0].date);
			let to = this.formatDate2(dateList[2].date);
			this.getEvents(from, to);
		},
		weekSlide(activeDay) {
			// this.week_slider_date = activeDay;
			//    let ymd = this.formatDate2(activeDay);
			// this.getSchedule(ymd);
			// this.getRemind(ymd);
			this.dateClickhandler(activeDay);
		},
		getDayList(dateList) {
			let from = this.formatDate(dateList[0].date);
			let to = this.formatDate(dateList[dateList.length - 1].date);
			this.getEvents(from, to);
		},
		getEvents(from, to) {
			this.axios.get(`/api/schedule/eventdays/from/${from}/to/${to}`)
				.then((res) => {
					if (res.data.errcode == 0) {
						// success
						this.events = res.data.data;
					} else {
						// fail
					}
				}).catch((err) => {
					// console.log(err);
				});
		},
		getRemind(ymd) {
			this.axios.get(`/api/schedule/remind/ymd/${ymd}`)
				.then((res) => {
					if (res.data.errcode == 0) {
						// success
						this.reminders = res.data.data;
					} else {
						// fail
					}
				}).catch((err) => {
					// console.log(err);
				});
		},
		getSchedule(ymd) {
			let timeFlag = new Date().getTime();
			this.axios.get(`/api/schedule/ymd/${ymd}`)
				.then((res) => {
					if (res.data.errcode == 0) {
						// success
						this.schedules = res.data.data;
						let n_schedules = [];
						this.schedules.forEach((v, index) => {
							if (v.type === 1) {
								n_schedules.push(v);
							}
						});
						this.schedules = n_schedules;
						let vue = this;
						if (new Date().getTime() - timeFlag >= 600) {
							vue.cView = "ScheduleList";
						} else {
							setTimeout(function () {
								vue.cView = "ScheduleList";
							}, 600);
						}
					} else {
						// fail
					}
				}).catch((err) => {
					// console.log(err);
				});
		},
		getMemorial() {
			this.axios.get("/api/schedule/getmemorials")
				.then((res) => {
					// window.console.log(res.data.data);
					if (res.data.errcode == 0) {
						// success
						this.memorials = res.data.data;
						// window.console.log(this.memorials);
					} else {
						// fail
					}
				}).catch((err) => {
					// console.log(err);
				});
		},
		choseDay(data) {
			let date = data.split("/");
			let year = date[0];
			// 解决日历跨年翻页，背景消失的bug
			if (year != this.year) {
				this.year = year;
			}
			let month = date[1];
			let day = date[2];
			let lunar = Lunar.toLunar(year, month, day);
			this.week_slider_selected_date = year + "/" + month + "/" + day;

			this.lunar_date = lunar[0] + " " + lunar[1];
			this.month = month;
			this.day = day;
			if (month < 10) {
				month = "0" + month;
			}
			if (day < 10) {
				day = "0" + day;
			}
			this.week_slider_date = year + "-" + month + "-" + day;
			this.ymd = year + month + day;
			this.getRemind(this.ymd);
			this.getSchedule(this.ymd);

			this.getMemorial(this.ymd);

		},
		dateClickhandler(data) {
			let date = data.split("-");
			let year = date[0];
			let month = date[1];
			let day = date[2];
			let ymd = year + month + day;
			let lunar = Lunar.toLunar(year, parseInt(month).toString(), day);
			this.lunar_date = lunar[0] + " " + lunar[1];
			this.month = parseInt(month);
			this.day = parseInt(day);
			this.week_slider_selected_date = year + "/" + month + "/" + day;
			this.week_slider_date = year + "-" + month + "-" + day;
			this.getRemind(ymd);
			this.getSchedule(ymd);

			this.getMemorial(ymd);

		},
		clickMockTouch(direction) {
			let date = this.year + "/" + this.month + "/" + 1;
			if (direction === "left") {
				this.pre_month = true;
				date = moment(date).subtract(1, "M").format("YYYY/M/D");
				this.choseDay(date);
			} else {
				this.next_month = true;
				date = moment(date).add(1, "M").format("YYYY/M/D");
				this.choseDay(date);
			}
		},
		toggleCalendar() {
			this.show_complete_calendar = !this.show_complete_calendar;
		},
		touch(direction) {
			let date = this.year + "/" + this.month + "/" + 1;
			if (direction == "up") {
				this.show_complete_calendar = false;
			} else if (direction == "down") {
				this.show_complete_calendar = true;
			} else if (direction == "left") {
				this.next_month = true;
				date = moment(date).add(1, "M").format("YYYY/M/D");
				this.choseDay(date);
			} else if (direction == "right") {
				this.pre_month = true;
				date = moment(date).subtract(1, "M").format("YYYY/M/D");
				this.choseDay(date);
			}
		},
		showGroupSchedules() {
			this.$router.push({path: "/schedules/group"});
		},
		showCreateSchedule() {
			this.$router.push({path: "/schedules/create", query: {type: 1}});
		},
		showCreateRemind() {
			this.$router.push({path: "/schedules/create", query: {type: 2}});
		},
		showCreateMemorial() {
			this.$router.push("/schedules/memorial/create");
		},

		// (2018/09/10 or 2018/9/10) => 20180910
		formatDate(data) {
			let date = data.split("/");
			let y = date[0];
			let m = parseInt(date[1]);
			let d = parseInt(date[2]);
			if (m < 10) {
				m = "0" + m;
			}
			if (d < 10) {
				d = "0" + d;
			}
			return y + m + d;
		},

		// (2018-09-10 or 2018-9-10) => 20180910
		formatDate2(data) {
			let date = data.split("-");
			let y = date[0];
			let m = parseInt(date[1]);
			let d = parseInt(date[2]);
			if (m < 10) {
				m = "0" + m;
			}
			if (d < 10) {
				d = "0" + d;
			}
			return y + m + d;
		},
		// 检查用户是否可以进入日程群组
		check() {
			this.$store.state.selected_org_user_info.groups.forEach((val, index) => {
				// 仅日程群组有效
				if (val.type === 1) {
					this.can_in_group_schedules = true;
				}
			});
		}
	}
};
</script>

<style scoped>
  #calendar {
    position: relative
  }

  .btnBoxShow {
    display: none;
  }

  .btnRotation {
    transform: rotate(-45deg);
  }

  .top-schedule {
    position: fixed;
    top: 0;
    z-index: 4;
    width: 100%;
    height: 48px;
    background-color: #fff;
    padding: 6px 16px;
    display: flex;
    align-items: center;
    box-shadow: 1px 0px 1px rgba(3, 3, 3, 0.2);
  }

  .dudu-arrow_down {
    position: absolute;
    z-index: 1;
    font-size: 16px;
    bottom: 0px;
    margin-left: calc(50% - 8px);
  }
</style>