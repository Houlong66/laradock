<template>
  <div 
    style="background-color: #fff;" 
    class="pb-2">
    <div 
      v-if="showYear" 
      class="year_str">{{ yearMonthStr }}</div>
    <div 
      v-if="reset" 
      :class="{ weekSilder100: isWexin }"
      class="week-slider">
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
      <v-icon
        v-if="isWexin"
        class="iconfont dudu-zuo1"
        @click="moveLeft()"/>
      <div
        ref="sliders"
        class="sliders"
        @touchstart="touchstartHandle"
        @touchmove="touchmoveHandle"
        @touchend="touchendHandle">
        <template v-for="(item, index) in dates">
          <div
            :key="index"
            :style="getTransform(index)"
            class="slider wh_content"
            tes
            @transitionend="onTransitionEnd(index)">
            <div 
              v-for="(day, dayIndex) in getDaies(item.date)"
              :key="dayIndex"
              class="wh_content_item">
              <div
                :class="{today: day.isToday, selectDay: (current == dayIndex && day.date == defaultDate) || day.date == defaultDate}"
                class="wh_item_date"
                @click.stop="dayClickHandle(day.date, dayIndex)">
                <!-- {{day.week}} -->
                {{ day.date.split('-')[2] > 10 ? day.date.split('-')[2] : int(day.date.split('-')[2]) }}
              </div>
              <div 
                v-if="hasevents.indexOf(day.date) != -1" 
                :class="{todayhasevents: day.isToday, hasevents: true}"/>
            </div>
          </div>
        </template>
      </div>
      <v-icon
        v-if="isWexin"
        class="iconfont dudu-you1"
        @click="moveRight()"/>
    </div>
  </div>
</template>

<script>
import moment from "moment";  // moment.js可参考 http://momentjs.com/

export default {
	name: "WeekSlider",
	props: {
		defaultDate: {
			type: String,
			default: moment().format("YYYY-MM-DD")
		},
		showYear: {
			type: Boolean,
			default: false
		},
		hasevents: {
			type: Array,
			default: () => []
		}
	},
	data () {
		return {
			dates: [],
			direction: null,
			activeIndex: 1,
			isAnimation: false,
			yearMonthStr: "",
			current: null,
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
			sliderWidth: 0,
			reset: true,
			textTop: ["日", "一", "二", "三", "四", "五", "六"],
			style_flag: 3,
			active_day: "",
			intId: null,
			touchEnd: false,
			// 是否是pc 端微信浏览器
			isWexin: false
		};
	},
	computed: {},
	watch: {
		dates: {
			handler: function (newVal, oldVal) {
				this.yearMonthStr = moment(newVal[1].date).format("YYYY-MM");
			}
		},
		deep: true
	},
	created () {
		let vm = this;
		this.dates.push(
			{
				date: moment(vm.defaultDate).subtract(7, "d").format("YYYY-MM-DD"),
			},
			{
				date: vm.defaultDate,
			},
			{
				date: moment(vm.defaultDate).add(7, "d").format("YYYY-MM-DD"),
			}
		);
		this.$emit("getWeekEventList", this.dates);
		this.isWexin = this.isWxBrowser() && !this.isMobileBrowser();
	},
	mounted () {
		this.sliderWidth = this.$refs.sliders.offsetWidth;
	},
	methods: {
		int (data) {
			return parseInt(data);
		},

		/**
        *获取指定日期的当前周的日期数据
        */
		getDaies (date) {
			let vm = this,
				arr = [];
			let weekOfDate = Number(moment(date).format("E"));
			// let weeks = ["日", "一", "二", "三", "四", "五", "六"];
			let today = moment();
			let defaultDay = moment(vm.defaultDate);
			if (weekOfDate === 7) {
				weekOfDate = 0;
			}
			for (var i = 0; i < 7; i++) {
				let _theDate = moment(date).subtract(weekOfDate - i, "d");
				arr.push({
					date: _theDate.format("YYYY-MM-DD"),
					// week: weeks[i],
					isToday: _theDate.format("YYYY-MM-DD") === today.format("YYYY-MM-DD"),
					isDay: _theDate.format("E") === defaultDay.format("E")
				});
			}
			return arr;
		},

		/**
        *根据索引计算出样式
        */
		getTransform (index) {
			let angle = this.getAngle(this.distan.x, this.distan.y);
			if ((angle > 45 && angle < 135) || (angle >= -135 && angle <= -45)) {
				this.distan.x = 0;
			}

			let vm = this;
			let style = {};
			if (index === vm.activeIndex) {
				style["transform"] = "translateX("+ vm.distan.x +"px)";
			}
			if (index < vm.activeIndex) {
				style["transform"] = "translateX(-100%)";
			}
			if (index > vm.activeIndex) {
				style["transform"] = "translateX(100%)";
			}
			style["transition"] = vm.isAnimation ? "transform .5s ease-out" : "none";
			return style;	

		},


		/**
         * touchstart handle
		 * 此函数记录初始位置
         */
		touchstartHandle (event) {

			let vm = this,
				touch = event.touches[0];
			vm.start.x = touch.pageX;
			vm.start.y = touch.pageY;
			vm.touchEnd = false;
		},

		/**
         * touchmove handle
		 * 此函数中移动的是pre 或next div
         */
		touchmoveHandle (event) {
			let vm = this;
			// 如果在动画进行中，则不能继续滑动
			if(this.activeIndex === 0 || this.activeIndex === 2) {
				return;
			}
			let touch = event.touches[0];
			vm.isAnimation = true;
			vm.end.x = touch.pageX;
			vm.end.y = touch.pageY;
			vm.distan.x = vm.end.x - vm.start.x;
			vm.distan.y = vm.end.y - vm.start.y;
			let angle = this.getAngle(this.distan.x, this.distan.y);
			if ((angle > 45 && angle < 135) || (angle >= -135 && angle <= -45)) {
				return null;
			}
			let dom = vm.distan.x < 0 ? this.$refs.sliders.children[2] : this.$refs.sliders.children[0];
			if (vm.distan.x < 0) {
				dom.style["transform"] = "translateX("+ (vm.sliderWidth + vm.distan.x) +"px)";
			} else {
				dom.style["transform"] = "translateX("+ (-vm.sliderWidth + vm.distan.x) +"px)";
			}	
			
		},

		/**
         * touchend handle
		 * 此函数根据活动的左右方向更改activeIndex
		 * 若不是朝左右滑动则各回原位
         */
		touchendHandle (event) {
			if(this.activeIndex === 0 || this.activeIndex === 2) {
				return;
			}
			this.touchEnd = true;
			let vm = this;
			let touch = event.changedTouches[0];
			vm.isAnimation = true;
			vm.end.x = touch.pageX;
			vm.end.y = touch.pageY;
			vm.distan.x = vm.end.x - vm.start.x;
			vm.distan.y = vm.end.y - vm.start.y;

			vm.getTouchDirection(vm.distan.x, vm.distan.y);

			if (vm.direction === "left") {
				vm.activeIndex = 2;
			} else if (vm.direction === "right") {
				vm.activeIndex = 0;
			} else {
				for (var i = 0; i < this.$refs.sliders.children.length; i++) {
					this.$refs.sliders.children[i].style["transform"] = "translateX("+ (i * 100 - 100) +"%)";
				}
			}
			if (vm.direction === "left" || vm.direction === "right") {
				vm.getActiveDate();
			}
				
			vm.distan.x = 0;
			vm.distan.y = 0;
			vm.direction = null;

			// 去抖操作，防止发生activeIndex === 0 或activeIndex === 2 的情况
			vm.intId && clearTimeout(vm.intId);
			vm.intId = setTimeout(() => {
				if(this.activeIndex !== 1) {
					setTimeout(() => {
						if(this.activeIndex !== 1) {
							this.activeIndex = 1;
						}
					}, 200);
				}
			}, 800);

		},

		/* BUG: 因为快速滑动时，没有完成此函数但又继续滑动，所以会出现bug
		 * TODO: 解决这个问题 or 换一个插件 
		 * 函数功能：滑动结束后，根据活动方向更新dates 数组信息
		 * */
		
		onTransitionEnd (index) {
			let vm = this;
			vm.isAnimation = false;
			// 相比源代码，if 判断条件内去掉index === 1 可以防止滑到某一边划不动的情况
			if (this.activeIndex === 2) {
				vm.dates.push({
					date: moment(vm.dates[vm.activeIndex].date).add(7, "d").format("YYYY-MM-DD")
				});
				vm.dates.shift();
				vm.activeIndex = 1;
				this.$emit("getWeekEventList", this.dates);
			} else if (this.activeIndex === 0) {
				vm.dates.unshift({
					date: moment(vm.dates[vm.activeIndex].date).subtract(7, "d").format("YYYY-MM-DD")
				});
				vm.dates.pop();
				vm.activeIndex = 1;
				this.$emit("getWeekEventList", this.dates);
			}
		},

		/**
         * 计算角度
         */
		getAngle (x, y) {
			return Math.atan2(y, x) * 180 / Math.PI;
		},

		/**
         * 获取滑动方向
         */
		getTouchDirection (x, y) {
			let vm = this;
			let angle = vm.getAngle(x, y);
			if (Math.abs(x) > 20) {
				if (angle >= -45 && angle <= 45) {//向右
					vm.direction = "right";
				} else if ((angle >= 135 && angle <= 180) || (angle >= -180 && angle < -135)) {//向左
					vm.direction = "left";
				}
			}
		},

		dayClickHandle (date, dayIndex) {
			this.current = dayIndex;
			this.$emit("dateClick", date);
		},

		// 滑动后获取当前周选中日期
		getActiveDate() {
			let vm = this;
			let weekOfDate = Number(moment(vm.dates[vm.activeIndex].date).format("E"));
			if (weekOfDate == 7) {
				weekOfDate = 0;
			}
			let activeDay = moment(vm.dates[vm.activeIndex].date).subtract(weekOfDate, "d").format("YYYY-MM-DD");

			if (this.active_day == activeDay) {
				this.dates.shift();
				this.dates.shift();
				this.dates.push(
					{
						date: moment(this.active_day).add(7, "d").format("YYYY-MM-DD"),
					},
					{
						date: moment(this.active_day).add(14, "d").format("YYYY-MM-DD"),
					}
				);
				// this.$refs.sliders.children[0].style["transform"] = "translateX("+ -100 +"%)";
				// this.$refs.sliders.children[1].style["transform"] = "translateX("+ 0 +"px)";
				// this.$refs.sliders.children[2].style["transform"] = "translateX("+ 100 +"%)";
				this.$emit("weekSlide", this.dates[1].date);
			} else {
				this.active_day = activeDay;
				this.$emit("weekSlide", activeDay);
			}
		},

		// 点击按钮向左右下三个方向滑动
		moveLeft() {
			if(this.activeIndex !== 1) {
				return;
			}
			this.isAnimation = true;
			this.activeIndex = 0;
			this.getActiveDate();
		},
		moveDown() {

		},
		moveRight() {
			if(this.activeIndex !== 1) {
				return;
			}
			this.isAnimation = true;
			this.activeIndex = 2;
			this.getActiveDate();
		}

	}
};
</script>
<style scoped>
.year_str{
    height: 36px; border-bottom: #ddd solid 1px; line-height: 36px; text-align: center;
}
.week-slider{
	width: 100%; 
	height: 88px; 
	overflow: hidden;
}
.weekSilder100 {
	height: 100px;
}
.week-silder .sliders {
    position: relative;
}

.slider {
    position: absolute;
}

.slider .day {
    height: 48px; width: 48px; padding: 6px 0; margin: auto; text-align: center; line-height: 18px; font-size: 12px;
}

.day div {
    height: 48px; width: 48px; padding: 6px 0; margin: auto; text-align: center; line-height: 18px; font-size: 12px;
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

.today{
    border-radius: 50%;
    background-color: #ffeb3b;
    color: #FFF;
}

.sameDay{
    border-radius: 50%;
    background-color: #999;
    color: #FFF;
}

.selectDay {
    border-radius: 50%;
    background-color: #f44336;
    color: #FFF;
}

.hasevents {
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

.wh_content {
  display: flex;
  flex-wrap: wrap;
  padding: 0 3% 0 3%;
  width: 100%;
}

.wh_content_item {
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

.todayhasevents {
	background-color: #fff !important;
}

.dudu-zuo1, .dudu-you1 {
	position: absolute;
	margin-top: 11px;
	font-size: 18px;
	cursor: pointer;
}

.dudu-zuo1 {
	left: 1%;
	z-index: 1;
}

.dudu-you1 {
	right: 1%;
}
</style>
