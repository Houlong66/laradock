<template>
  <div style="background-color: #f5f5f5;">

    <v-text-field
      v-model="search_key"
      class="mx-3 pt-3"
      flat
      placeholder="搜索"
      background-color="white"
      append-icon="search"
      solo
      single-line
      hide-details
      @blur="scrollTo"
    />

    <WorksList :list="show_list"/>

  </div>
</template>

<script>
import WorksList from "./WorkLists";
import {mapState} from "vuex";

export default {
	components: {
		WorksList
	},
	data() {
		return {
			search_key: null,
			timeout: null,
			list: {},
			show_list: {} // 展示的列表
		};
	},
	computed: {
		...mapState(["finished_list"]),
	},
	watch: {
		search_key: function (newV, oldV) {
			this.search();
		}
	},
	mounted: function () {
		this.initData();
		this.show_list = this.list;
	},
	methods: {
		initData() {
			this.list = this.finished_list;
			if (this.$route.params.type == 1){
				document.title = "已完成通知";
			}
			// this.list = this.descSort(this.finished_list,);
		},
		search() {
			if (!this.search_key) {
				this.show_list = this.list;
				return;
			}
			this.show_list = {};
			for (var index in this.list) {
				this.list[index].list.forEach((value, i) => {
					if (value.title.indexOf(this.search_key) != -1) {
						if (this.show_list[index] == undefined) {
							this.show_list[index] = {
								flag: this.list[index].flag,
								list: []
							};
						}
						this.show_list[index].list.push(value);
					}
				});
			}
		}
	}
};
</script>

<style scoped>

</style>