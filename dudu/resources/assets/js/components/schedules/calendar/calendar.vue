<template>
  <section 
    style="background-color: #fff;" 
    class="wh_container pb-2 mb-2">
    <!-- 展示出来的左右滑动 strat-->
    <v-icon
      v-if="isWeixin"
      class="iconfont dudu-zuo1"
      @click="showOtherMonth('left')"/>
    <v-icon
      v-if="isWeixin"
      class="iconfont dudu-you1"
      @click="showOtherMonth('right')"/>
    <!-- 展示出来的左右滑动 end -->
    <div class="wh_content_all">
      <!-- 位于顶部的左右滑动和年月显示，正常页面下看不到 start-->
      <div class="wh_top_changge">
        <li @click="PreMonth(myDate,false)">
          <div class="wh_jiantou1"/>
        </li>
        <li class="wh_content_li">{{ dateTop }}</li>
        <li @click="NextMonth(myDate,false)">
          <div class="wh_jiantou2"/>
        </li>
      </div>
      <!-- 位于顶部的左右滑动和年月显示，正常页面下看不到 end-->
      <div class="wh_content">
        <div 
          v-for="(tag, index) in textTop"
          :key="index"
          class="wh_content_item">
          <div class="wh_top_tag">
            {{ tag }}
          </div>
        </div>
      </div>
      <div
        class="wh_content"
        @touchstart="touchstartHandle"
        @touchmove="touchmoveHandle"
        @touchend="touchendHandle">
        <div 
          v-for="(item,index) in list" 
          :key="index"
          class="wh_content_item" 
          @click="clickDay(item,index)">
          <div
            :class="[{wh_other_dayhide:item.otherMonth!=='nowMonth'},{wh_want_dayhide:item.dayHide},
                     {wh_isToday:item.isToday},{wh_chose_day:item.chooseDay},setClass(item)]"
            class="wh_item_date">
            {{ item.id }}
          </div>
          <div 
            v-if="item.isMark" 
            class="hasEvents"/>
        </div>
      </div>
    </div>
  </section>
</template>
<script>
import timeUtil from "./calendar";

export default {
	props: {
		select: {
			type: String,
			default: ""
		},
		mark: {
			type: Array,
			default: () => []
		},
		markDateMore: {
			type: Array,
			default: () => []
		},
		textTop: {
			type: Array,
			default: () => ["日", "一", "二", "三", "四", "五", "六"]
		},
		sundayStart: {
			type: Boolean,
			default: () => true
		},
		agoDayHide: { type: String, default: "0" },
		futureDayHide: { type: String, default: "2554387200" }
	},
	data() {
		return {
			myDate: [],
			newMyDate: false,
			list: [],
			historyChose: [],
			dateTop: "",
			start: {
				x: null,
				y: null
			},
			end: {
				x: null,
				y: null
			},
			distan: {
				x: 0,
				y: 0
			},
			direction: null,
			getListFlag: false,
			isWeixin: false
		};
	},
	watch: {
		select: {
			handler (val, oldVal) {
				this.getList(this.myDate, this.select);
			},
			deep: true
		},
		mark: {
			handler(val, oldVal) {
				this.getList(this.myDate);
			},
			deep: true
		},
		markDateMore: {
			handler(val, oldVal) {
				this.getList(this.myDate);
			},
			deep: true
		},
		agoDayHide: {
			handler(val, oldVal) {
				this.agoDayHide = parseInt(val);
				this.getList(this.myDate);
			},
			deep: true
		},
		futureDayHide: {
			handler(val, oldVal) {
				this.futureDayHide = parseInt(val);
				this.getList(this.myDate);
			},
			deep: true
		},
		sundayStart: {
			handler(val, oldVal) {
				this.intStart();
				this.getList(this.myDate);
			}, deep: true
		}
	},
	created() {
		this.intStart();
		this.myDate = new Date();
		this.isWeixin = this.isWxBrowser() && !(this.isAndroid() || this.isIos());
	},
	mounted() {
		this.newMyDate = true;
		this.getListFlag = true;
		this.getList(this.myDate);
	},
	methods: {
		intStart() {
			timeUtil.sundayStart = this.sundayStart;
		},
		setClass(data) {
			let obj = {};
			obj[data.markClassName] = data.markClassName;
			return obj;
		},
		clickDay: function (item, index) {
			if (item.otherMonth === "nowMonth" && !item.dayHide) {
				this.getList(this.myDate, item.date);
			}
			if (item.otherMonth !== "nowMonth") {
				item.otherMonth === "preMonth"
					? this.PreMonth(item.date)
					: this.NextMonth(item.date);
			}
		},
		showOtherMonth(direction) {
			if(direction === "left") {
				this.PreMonth(this.myDate, false);
			} else {
				this.NextMonth(this.myDate, false);
			}
			this.$emit("clickMockTouch", direction);
		}, 
		ChoseMonth: function (date, isChosedDay = true) {
			date = timeUtil.dateFormat(date);
			this.myDate = new Date(date);
			this.$emit("changeMonth", timeUtil.dateFormat(this.myDate));
			if (isChosedDay) {
				this.getList(this.myDate, date, isChosedDay);
			} else {
				this.getList(this.myDate);
			}
		},
		PreMonth: function (date, isChosedDay = true) {
			this.getListFlag = true;
			date = timeUtil.dateFormat(date);
			this.myDate = timeUtil.getOtherMonth(this.myDate, "preMonth");
			this.$emit("changeMonth", timeUtil.dateFormat(this.myDate));
			if (isChosedDay) {
				this.getList(this.myDate, date, isChosedDay);
			} else {
				this.getList(this.myDate);
			}
		},
		NextMonth: function (date, isChosedDay = true) {
			this.getListFlag = true;
			date = timeUtil.dateFormat(date);
			this.myDate = timeUtil.getOtherMonth(this.myDate, "nextMonth");
			this.$emit("changeMonth", timeUtil.dateFormat(this.myDate));
			if (isChosedDay) {
				this.getList(this.myDate, date, isChosedDay);
			} else {
				this.getList(this.myDate);
			}
		},
		forMatArgs: function () {
			let markDate = this.mark;
			let markDateMore = this.markDateMore;
			markDate = markDate.map((k) => {
				return timeUtil.dateFormat(k);
			});
			markDateMore = markDateMore.map((k) => {
				k.date = timeUtil.dateFormat(k.date);
				return k;
			});
			return [markDate, markDateMore];
		},
		getList: function (date, chooseDay, isChosedDay = true) {
			const [markDate, markDateMore] = this.forMatArgs();
			this.dateTop = `${date.getFullYear()}年${date.getMonth() + 1}月`;
			if (this.newMyDate && this.select) {

				chooseDay = this.select;
				let tempDate = chooseDay.split("/");
				let tempYear = parseInt(tempDate[0]);
				let tempMonth = parseInt(tempDate[1] - 1);
				let tempDay = parseInt(tempDate[2]);
				chooseDay = tempYear + "/" + (tempMonth + 1) + "/" + tempDay;
				this.myDate= new Date(tempYear, tempMonth, tempDay);
				this.newMyDate = false;
			}

			let arr = timeUtil.getMonthList(this.myDate);
			for (let i = 0; i < arr.length; i++) {
				let markClassName = "";
				let k = arr[i];
				k.chooseDay = false;
				const nowTime = k.date;
				const t = new Date(nowTime).getTime() / 1000;
				//看每一天的class
				for (const c of markDateMore) {
					if (c.date === nowTime) {
						markClassName = c.className || "";
					}
				}
				//标记选中某些天 设置class
				k.markClassName = markClassName;
				k.isMark = markDate.indexOf(nowTime) > -1;
				//无法选中某天
				k.dayHide = t < this.agoDayHide || t > this.futureDayHide;
				if (k.isToday) {
					this.$emit("isToday", nowTime);
				}
				// flag 表示日期可显示并属于当前月
				let flag = !k.dayHide && k.otherMonth === "nowMonth";
				if (chooseDay && chooseDay === nowTime && flag) {
					this.$emit("choseDay", nowTime);
					this.historyChose.push(nowTime);
					k.chooseDay = true;
				} else if (
					this.historyChose[this.historyChose.length - 1] === nowTime && !chooseDay && flag
				) {
					k.chooseDay = true;
				}
			}
			this.list = arr;
			if (this.getListFlag) {
				this.$emit("getDayList", this.list);
				this.getListFlag = false;
			}
		},

		touchstartHandle (event) {
			let vm = this,
				touch = event.touches[0];
			vm.start.x = touch.pageX;
			vm.start.y = touch.pageY;
		},

		touchmoveHandle (event) {
			let vm = this,
				touch = event.touches[0];
			vm.isAnimation = true;
			vm.end.x = touch.pageX;
			vm.end.y = touch.pageY;
			vm.distan.x = vm.end.x - vm.start.x;
			vm.distan.y = vm.end.y - vm.start.y;
		},

		touchendHandle (event) {
			let vm = this,
				touch = event.changedTouches[0];
			vm.isAnimation = true;
			vm.end.x = touch.pageX;
			vm.end.y = touch.pageY;
			vm.distan.x = vm.end.x - vm.start.x;
			vm.distan.y = vm.end.y - vm.start.y;

			vm.getTouchDirection(vm.distan.x, vm.distan.y);
			if (vm.direction === "left") {
				this.NextMonth(this.myDate, false);
			} else if (vm.direction === "right") {
				this.PreMonth(this.myDate, false);
			}
			vm.distan.x = 0;
			vm.distan.y = 0;
			vm.direction = null;
		},
		getAngle (x, y) {
			return Math.atan2(y, x) * 180 / Math.PI;
		},
		getTouchDirection (x, y) {
			let vm = this;
			let angle = vm.getAngle(x, y);
			if (Math.abs(x) > 20) {
				if (angle >= -45 && angle <= 45) {//向右
					vm.direction = "right";
				} else if ((angle >= 135 && angle <= 180) || (angle >= -180 && angle < -135)) {//向左
					vm.direction = "left";
				} else if (angle > 45 && angle < 135) {
					vm.direction = "down";
				} else if (angle >= -135 && angle < -45) {
					vm.direction = "up";
				}
			}
		},
	},
};
</script>

<style scoped>
@media screen and (min-width: 460px) {
  .wh_item_date:hover {
    background: #71c7a5;
    cursor: pointer;
  }
}
* {
  margin: 0;
  padding: 0;
}

.wh_container {
	position: relative;
  max-width: 410px;
  margin: auto;
}

li {
  list-style-type: none;
}
.wh_top_changge {
  display: flex;
}

.wh_top_changge li {
  cursor: pointer;
  display: flex;
  /*color: #fff;*/
  font-size: 18px;
  flex: 1;
  justify-content: center;
  align-items: center;
  height: 47px;
}

.wh_top_changge .wh_content_li {
  cursor: auto;
  flex: 2.5;
}
.wh_content_all {
  font-family: -apple-system, BlinkMacSystemFont, "PingFang SC",
    "Helvetica Neue", STHeiti, "Microsoft Yahei", Tahoma, Simsun, sans-serif;
  background-color: #fff;
  width: 100%;
  overflow: hidden;
  padding-bottom: 10px;
}

.wh_content {
  display: flex;
  flex-wrap: wrap;
  padding: 0 3% 0 3%;
  width: 100%;
}

.wh_content:first-child .wh_content_item_tag,
.wh_content:first-child .wh_content_item {
  color: #ddd;
  font-size: 16px;
}

.wh_content_item,
wh_content_item_tag {
  font-size: 15px;
  width: 13.4%;
  text-align: center;
  /*color: #fff;*/
  position: relative;
}
.wh_content_item {
  height: 40px;
}

.wh_top_tag {
  width: 40px;
  height: 40px;
  line-height: 40px;
  margin: auto;
  display: flex;
  justify-content: center;
  align-items: center;
}

.wh_item_date {
  width: 40px;
  height: 40px;
  line-height: 40px;
  margin: auto;
  display: flex;
  justify-content: center;
  align-items: center;
}

.wh_jiantou1 {
  width: 12px;
  height: 12px;
  border-top: 2px solid #ffffff;
  border-left: 2px solid #ffffff;
  transform: rotate(-45deg);
}

.wh_jiantou1:active,
.wh_jiantou2:active {
  border-color: #ddd;
}

.wh_jiantou2 {
  width: 12px;
  height: 12px;
  border-top: 2px solid #ffffff;
  border-right: 2px solid #ffffff;
  transform: rotate(45deg);
}
.wh_content_item > .wh_isMark {
  margin: auto;
  border-radius: 100px;
  background: blue;
  z-index: 2;
}
.wh_content_item .wh_other_dayhide {
  color: #bfbfbf;
}
.wh_content_item .wh_want_dayhide {
  color: #bfbfbf;
}
.wh_content_item .wh_isToday {
  background: #ffeb3b;
  color: #fff;
  border-radius: 100px;
}
.wh_content_item .wh_chose_day {
  background: #f44336;
  color: #fff;
  border-radius: 100px;
}
.hasEvents {
  border-radius: 50%;
  bottom: 2px;
  display: block;
  height: 6px;
  left: 50%;
  position: absolute;
  transform: translateX(-4px);
  width: 6px;
  background-color: #ffeb3b
}
.dudu-zuo1, .dudu-you1 {
	position: absolute;
	margin-top: 200px;
	font-size: 18px;
}
.dudu-zuo1 {
	left: 1%;
}
.dudu-you1 {
	right: 1%;
}
</style>
