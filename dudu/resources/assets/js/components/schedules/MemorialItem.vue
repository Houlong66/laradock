<template>
  <div>
    <v-list-tile 
      class="py-2"
      @click="showItemDetail(item.id)">
      <v-list-tile-content>
        <v-list-tile-title>
          {{ item.name }}
        </v-list-tile-title>
        <!-- 纪念日 -->
        <v-list-tile-sub-title class="pt-1">
          <v-layout>
            <v-flex 
              xs1 
              class="align-self-center">
              <v-icon 
                small 
                class="grey--text iconfont dudu-rili"/>
            </v-flex>
            <v-flex 
              xs11 
              class="text-truncate align-self-center">
              <span class="grey--text">{{ item.is_solar ? '公历' : '农历' }}</span>
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
import moment from "moment";

export default {
	name: "MemorialItem",
	props: {
		item: {
			type: Object,
			default: ()=>{},
		}
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
				time = start_at[1] + " - " + end_at[1];
			} else if (start_at[0] < end_at[0]) {
				let today = moment().format("YYYY-MM-DD");
				if (today < end_at[0]) {
					time = start_at[0] + " - " + end_at[0];
				} else if (today == end_at[0]) {
					time = start_at[0] + " - " + "今天" + end_at[1];
				} else {
					time = start_at[0] + " - " + end_at[0];
				}
			}
			return time;
		}
	}
};
</script>

<style scoped>

</style>

