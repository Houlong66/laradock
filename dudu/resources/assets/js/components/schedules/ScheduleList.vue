<template>
  <v-layout
    column
    style="padding-bottom:56px;">
    <v-flex
      xs12
      sm6>
      <v-list subheader>
        <!--提醒事项-->
        <v-subheader class="grey lighten-4">提醒事项</v-subheader>
        <div v-if="reminders.length > 0">
          <div
            v-for="(item, index) in reminders"
            :key="index">
            <remind-item
              :selected-date="selectedDate"
              :item="item"/>
          </div>
        </div>
        <div
          v-if="reminders.length == 0"
          class="yet-empty text--lighten-1 grey--text">暂无提醒事项~
        </div>

        <!--日程事项-->
        <v-subheader class="grey lighten-4">日程安排</v-subheader>
        <div
          v-if="schedules.length > 0">
          <div
            v-for="(item, index) in schedules"
            :key="index">
            <schedule-item
              :selected-date="selectedDate"
              :item="item"/>
          </div>
        </div>
        <div
          v-if="schedules.length == 0"
          class="yet-empty text--lighten-1 grey--text">暂无日程安排~
        </div>

        <!--纪念日事项-->
        <div v-if="memorials.length !== 0">
          <v-subheader 
            class="grey lighten-4" 
            @click="memorialsList()">
            纪念日
            <!--<v-spacer></v-spacer>-->
            <span class="blue--text pl-2">查看全部</span>
          </v-subheader>
          <div
            v-show="show_memorials_list">
            <div
              v-for="(item, index) in memorials"
              :key="index">
              <memorial-item
                :selected-date="selectedDate"
                :item="item"/>
            </div>
          </div>
        </div>
      </v-list>
    </v-flex>
  </v-layout>
</template>

<script>
import RemindItem from "./RemindItem";
import ScheduleItem from "./ScheduleItem";
import MemorialItem from "./MemorialItem";

export default {
	name: "ScheduleList",
	components: {
		RemindItem,
		ScheduleItem,
		MemorialItem
	},
	props: {
		reminders: {
			type: Array,
			default: () => "reminders"
		},
		schedules: {
			type: Array,
			default: () => "schedules"
		},
		memorials: {
			type: Array,
			default: () => "memorials"
		},
		selectedDate: {
			type: String,
			default: () => "0000-00-00"
		}
	},
	data() {
		return {
			show_memorials_list: false,
		};
	},
	methods: {
		memorialsList() {
			this.show_memorials_list = !this.show_memorials_list;
		}
	}
};
</script>

<style scoped>
  .yet-empty {
    padding: 10px 16px;
  }

  .v-list {
    padding: 0;
  }
</style>