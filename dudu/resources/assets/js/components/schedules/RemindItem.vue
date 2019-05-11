<template>
  <div>
    <v-list-tile
      class="py-2"
      @click="showItemDetail(item.type, item.id)">
      <v-list-tile-content style="width: 50%;">
        <v-list-tile-title :class="pass_due">
          {{ typeText }} {{ item.title }}
        </v-list-tile-title>
        <v-list-tile-sub-title class="pt-1">
          <v-layout>
            <v-flex 
              xs1 
              class="align-self-center">
              <v-icon 
                :class="pass_due" 
                small 
                class="grey--text iconfont dudu-tips"/>
            </v-flex>
            <v-flex 
              xs11 
              class="text-truncate align-self-center">
              <span 
                :class="pass_due" 
                class="grey--text">{{ item.time }}</span>
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
	name: "RemindItem",
	components: {},
	props: {
		item: {
			type: Object,
			default: () => {
			},
		},
		selectedDate: {
			type: String,
			default: () => "0000-00-00"
		}
	},
	data() {
		return {
			pass_due_flag: false,
		};
	},
	computed: {
		typeText() {
			return {"task": "(任务)", "schedule": "(日程)", "remind": "(提醒)", "memorial": "(纪念日)"}[this.item.type];
		},
		pass_due() {
			return {
				"pass": this.pass_due_flag,
			};
		}
	},
	mounted() {
		this.initData();
	},
	methods: {
		initData() {
			let remind_list = this.item.time.split(",  ");
			let last_remind = `${this.selectedDate} ${remind_list[remind_list.length-1]}`;

			let last_remind_str = new Date(last_remind).getTime();
			let now_time_str = new Date().getTime();
			this.pass_due_flag =  last_remind_str < now_time_str ? true : false;
		},
		showItemDetail(type, id) {
			let url = "";
			switch (type) {
			case "memorial":
			case "remind":
			case "schedule":
				url = "/schedule/detail";
				this.$router.push({path: url, query: {id: id}});
				break;
			case "task":
				url = "/task_detail/" + id;
				this.$router.push(url);
				break;
			}
		}
	}
};
</script>

<style scoped>
  .pass{
    color:#ccc!important;
  }
</style>
