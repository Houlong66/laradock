<template>
  <div>
    <v-list-tile
      class="py-2"
      @click="showItemDetail(item.id)">
      <v-list-tile-content >
        <v-list-tile-title :class="pass_due">
          {{ item.name }}
        </v-list-tile-title>

        <v-list-tile-sub-title class="pt-1 grey--text">
          <v-layout>
            <v-flex 
              xs1 
              class="align-self-center">
              <v-icon 
                :class="pass_due" 
                small 
                class="grey--text iconfont dudu-jiezhishijian"/>
            </v-flex>
            <v-flex 
              xs11 
              class="text-truncate align-self-center">
              <span 
                :class="pass_due" 
                class="grey--text">{{ schedule_time }}</span>
            </v-flex>
          </v-layout>
        </v-list-tile-sub-title>
      </v-list-tile-content>

      <v-list-tile-action>
        <v-btn
          icon
          ripple>
          <v-icon
            small
            color="grey lighten-1"
            class="iconfont dudu-you1"/>
        </v-btn>
      </v-list-tile-action>
    </v-list-tile>
    <v-divider class="grey lighten-3"/>
  </div>

</template>

<script>
export default {
	name: "ScheduleItem",
	props: {
		item: {
			type: Object,
			default: ()=>{},
		},
		selectedDate: {
			type: String,
			default: () => "0000-00-00"
		}
	},
	data() {
		return {
			pass_due_flag: false,
			schedule_time: "全天",
		};
	},
	computed: {
		pass_due() {
			return {
				"pass": this.pass_due_flag,
			};
		}
	},
	watch: {
		selectedDate() {
			this.getTime();
		}
	},
	mounted() {
		this.getTime();
	},
	methods: {
		showItemDetail(id){
			this.$router.push({path: "/schedule/detail", query: {id: id}});
		},
		getTime() {
			let start_at = this.item.start_at.split(" ");
			let end_at = this.item.end_at.split(" ");
			let time = "";
			if (start_at[0] == end_at[0]) {
				time = start_at[1].substr(0, 5) + " - " + end_at[1].substr(0, 5);
			} else if (start_at[0] < end_at[0]) {
				time = "全天";
			}
			// 获取指定结束时间时间戳
			let now_time_str = new Date().getTime();
			let temp_end_time = `${this.selectedDate} 21:00:00`;
			let end_time_str = new Date(temp_end_time).getTime();

			this.pass_due_flag = end_time_str < now_time_str ? true : false;
			this.schedule_time = time;
		}
	}
};
</script>

<style scoped>
  .pass{
    color: #ccc!important;
  }
</style>
