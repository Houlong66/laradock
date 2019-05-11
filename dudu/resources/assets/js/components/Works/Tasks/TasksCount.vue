<template>
  <div>
    <!--任务统计-->
    <v-container fluid>

      <!--选择部门或个人-->
      <v-radio-group
        v-model="row"
        class="mt-0 mb-0 radio-btn"
        style="height:100%"
        row
      >
        <v-radio
          label="按部门"
          color="red"
          value="部门"
        />
        <v-radio
          label="按个人"
          color="red"
          value="个人"/>
      </v-radio-group>

      <hr>

      <!--大范围时间选择-->
      <div
        class="datepick-btn"
      >
        <v-icon
          class="iconfont dudu-rili1 "
        />
        <v-btn-toggle
          class="ml-2 btnlist"
          light
        >
          <v-btn
            small
            @click="setSearchDate(0)"
          >今日</v-btn>

          <v-btn
            small
            @click="setSearchDate(1)"
          >
            本周</v-btn>

          <v-btn
            small
            @click="setSearchDate(2)"
          >本月</v-btn>
        </v-btn-toggle>

      </div>

      <!--自定义时间选择-->
      <v-layout
        row
        wrap
        align-center
        justify-space-between>
        <v-flex xs5>
          <v-text-field
            v-model="start_at"
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
            label="结束时间"
            required
            readonly
            @blur="scrollTo"
            @click="getEndTime()"
          />
        </v-flex>
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
            @click="selectDate (date)">确定
          </v-btn>
        </v-date-picker>


      </v-dialog>

      <!--全选按钮-->
      <v-layout>

        <v-flex xs4>
          <v-icon class="iconfont dudu-qunzu"/>
          <span>选择群组</span>
        </v-flex>

        <v-flex
          xs8
          class="checkxs-btn"
        >
          <v-layout
            @click="allCheckbox()"
          >
            <v-flex xs2>
              <v-checkbox
                v-model="checkbox"
                color="primary"
              />
            </v-flex>

            <v-flex
              style="line-height: 36px;"
              xs10>
              选择全部群组
            </v-flex>
          </v-layout>

        </v-flex>

      </v-layout>

      <!--群组选择框-->
      <treeselect
        ref="checkbtn"
        v-model="targets"
        :options="options"
        :value="value"
        :multiple="multiple"
        :max-height="200"
        :always-open="true"
        :default-expand-level="0"
        :z-index="0"
        no-options-text="暂无可选工作群组">
        <div
          slot="value-label"
          slot-scope="{ node }"
        >{{ node.raw.customLabel }}</div>

      </treeselect>


      <!--搜索按钮跟提示-->
      <div>
        <v-btn
          :loading="loading"
          :disabled="loading"
          class="mt-3"
          block
          @click="searchMsg()"
        >
          开始搜索
        </v-btn>
        <p
          class="count-tips"
        ><span style="color:red;">*  </span>点击开始搜索进行查询</p>
      </div>

      <!--详情图-->
      <div
        v-if="deltali_img"
      >
        <p
          class="details-tu">
          <v-icon class="iconfont dudu-tiaoxingtu"/>
          <span>详情图</span>
        </p>

        <!--条状图-->
        <ve-bar
          :data="chartData"
        />
      </div>

      <!--无数据时候-->
      <p
        v-if="allmsg_null"
        class="detail-tips">暂时没有任何数据哦</p>

    </v-container>


  </div>
</template>

<script>
import {mapState} from "vuex";
import Treeselect from "@riophae/vue-treeselect";
export default {
	name: "TasksCount",
	components:{
		Treeselect
	},
	data (){
		return{
			row:null,
			checkbox:false,

			multiple: true,
			value: null,
			options: [],
			targets:[],
			loading:false,
			selected_time:null,
			deltali_img:false,
			allmsg_null:false,

			// 时间 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
			start_at: null,
			date: null,           // 日期选择框比对时间
			end_at: "",
			date_dialog:null,
			show_date_picker:false,
			time:null,
			date_type:null,
			// 时间 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

			// 图表配置 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>


			chartData: {
				columns: ["name", "接收任务数", "未完成任务数","已完成任务数", "逾期完成任务数"],
				rows: []
			}
			// 图表配置 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

		};
	},
	computed:{
		...mapState(["selected_org"])
	},
	watch:{
		targets(){
			this.checkTarGets();
		}
	},
	mounted (){
		this.initData();
	},
	methods:{
		initData(){
			// 当前机构下所有群组
			let arr = this.selected_org.groups;
			this.row = "个人";
			arr.forEach((v,i)=>{
				v.label = v.name;
				v.customLabel =  v.name;
			});
			this.options = arr;
		},
		checkTarGets(){

			if(this.targets.length < this.selected_org.groups.length){
				this.checkbox = false;
				return;
			}

			if(this.targets.length ===  this.selected_org.groups.length){
				this.checkbox = true;
				return;
			}

		},
		//  搜索信息
		searchMsg(){

			// 判断是否选中了部门或者个人
			if(!this.row){
				this.$toast("请先选择按部门或个人搜索", "error");
				return;
			}

			// 判断是否选择了日期
			if(!this.start_at && !this.end_at ){
				this.$toast("请先选择时间", "error");
				return;
			}

			let strat = new Date(this.start_at).getTime()/1000;
			let end   = new Date(this.end_at).getTime()/1000;

			// 判断开始时间是否在结束时间之后
			if(strat > end){
				this.$toast("请选择开始时间之后的结束时间", "error");
				return;
			}

			// 判断是否选择了群组
			if(this.targets.length === 0){
				this.$toast("请先选择查询群组", "error");
				return ;
			}

			let data = {
				row         :  this.row,
				groups_list :  this.targets,
				start_time  :  this.start_at,
				end_time    :  this.end_at
			};

			this.axios.post("/api/task/check_work",data).then((res) => {

				this.chartData.rows = res.data.data;

				if(this.chartData.rows.length !== 0){
					this.deltali_img = true;
					return ;
				}
				this.deltali_img = false;
				this.allmsg_null = true;

			}).catch((err) => {

			});
		},

		// 设置选择到的时间
		setSearchDate(type){

			// 直接清空其他数据
			this.selected_time = null;
			this.start_at = null;
			this.end_at = null;

			let isdate = new Date(); // 当前时间

			let reset_time = isdate.getFullYear() + "/" + ( isdate.getMonth() + 1 )  +  "/"  + isdate.getDate();

			let time_list = reset_time.split("/");

			time_list[1] < 10 ?  time_list[1] = 0 + time_list[1]  : time_list[1];

			time_list[2] < 10 ?  time_list[2] = 0 + time_list[2] : time_list[2];


			let lastDay = new Date(time_list[0],time_list[1],0);   // 本月的最后一天

			switch(type){
			//今天
			case 0 :
				this.start_at = time_list[0] + "-" + time_list[1] + "-" + time_list[2] ;
				this.end_at = time_list[0] + "-" + time_list[1] + "-" + time_list[2] ;
				break;
				// 本周
			case 1:

				var times   =  time_list;

				var now_date = isdate.getDate(); // 今天日期

				var now_day  = isdate.getDay();  // 今天星期几

				if (times[2] < 10){
					times[2] = 0 + times[2];
				}

				// 星期1
				times[2] =   now_date - now_day + 1;

				times[2] < 10 ? times[2] =  "0" + times[2]  :times[2];

				this.start_at = times[0]+ "-" +  times[1] + "-" + times[2] ;

				times[2] = (7 - now_day) + now_date ;

				times[2] < 10 ? times[2] =  "0" + times[2]  :times[2];

				//   大于 本月最后一天的时候
				if (times[2] > lastDay.getDate()){
					times[2]  =  7 - (lastDay.getDate() - now_date);
				}

				// 星期天
				this.end_at   = times[0]+ "-" + times[1] + "-" + times[2] ;

				break;

				// 本月
			case 2:

				var year = lastDay.getFullYear();

				var months = lastDay.getMonth() + 1;

				months = months < 10 ? "0"+ months : months;

				var day = lastDay.getDate();

				day =  day < 10 ? "0"+ day : day;

				this.start_at =  year + "-" + months + "-" + "01" ;

				this.end_at =  year + "-" + months + "-" + day ;

				break;

			}
		},

		// 全选事件
		allCheckbox(){
			let check  =  this.checkbox;
			let arr = [];
			if(check){
				this.selected_org.groups.forEach((v,i) => {
					arr.push(v.id);
				});

				this.targets = arr;
				return ;
			}
			this.targets = [];
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
			this.allowedDates();
			this.show_date_picker = true;
			this.date_dialog = true;
			this.date_type = 1;
		},

		allowedDates(val){
			if(this.date_type === 1){
				let start_at = this.start_at.split(" ")[0];
				return val >= start_at;
			}
			return val;
		},

		// 选择时间 年月份
		selectDate(date) {
			this.date_type === 0 ? this.start_at = date :  this.end_at = date;
			this.show_date_picker = false;
			this.date_dialog = false;
		},
	}
};
</script>

<style scoped>
.radio-btn >>> .v-input__slot{
margin-bottom: 0;
padding-bottom:0;
}

.datepick-btn{
display: flex;
margin-top: .7rem;
}

.datepick-btn >>> .v-btn{
margin-left: .5rem;
min-width: 25%;
}
.details-tu{
margin-top: 1rem;
}
.checkxs-btn >>> .v-input--selection-controls{
margin-top: 0;
padding-top: 5px;
}
.btnlist{
width:100%;
}
.btnlist button{
width:30%;
}

.treeselect-count  >>>.vue-treeselect__value-container{
overflow: hidden;
}
.treeselect-count  >>> .vue-treeselect__multi-value{
height: 28px;
overflow: hidden;
}
.detail-tips{
text-align: center;
background: #039be5;
padding: 3px;
color: #fff;
}
</style>