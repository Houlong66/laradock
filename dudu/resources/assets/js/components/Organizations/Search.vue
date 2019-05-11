<template>
  <div>
    <div 
      class="search-box" 
      style="background-color: #f5f5f5;">
      <v-text-field
        v-model="searchValue"
        class="mx-3 pt-3"
        flat
        placeholder="搜索（支持姓名和手机号）"
        background-color="white"
        append-icon="search"
        solo
        single-line
        @blur="scrollTo"
      />
      <!--<div class="yet-empty">-->
      <!--当前机构: <span class="color-grey">{{ nowOrg }}</span>-->
      <!--</div>-->
      <!--<div class="yet-empty">-->
      <!--当前部门: <span class="color-grey">{{ nowDept }}</span>-->
      <!--</div>-->
      <!--<div class="yet-empty pb-3">-->
      <!--当前群组: <span class="color-grey">{{ nowGroup }}</span>-->
      <!--</div>-->
    </div>

    <!--<v-tabs-->
    <!---->
    <!--centered-->
    <!--color="red"-->
    <!--dark-->
    <!--icons-and-text-->
    <!--&gt;-->
    <!--<v-tabs-slider color="yellow"/>-->
    <!--&lt;!&ndash; <v-tab href="#tab-1">-->
    <!--全部-->
    <!--</v-tab> &ndash;&gt;-->
    <!--<v-tab -->
    <!--href="#tab-2" -->
    <!--@click="touchTab(1)">-->
    <!--机构-->
    <!--</v-tab>-->
    <!--<v-tab -->
    <!--href="#tab-3" -->
    <!--@click="touchTab(2)">-->
    <!--部门-->
    <!--</v-tab>-->
    <!--<v-tab -->
    <!--href="#tab-4" -->
    <!--@click="touchTab(3)">-->
    <!--群组-->
    <!--</v-tab>-->
    <!--&lt;!&ndash; <v-tab href="#tab-5">-->
    <!--外部联系人-->
    <!--</v-tab> &ndash;&gt;-->
    <!--<v-tab-item-->
    <!--v-for="i in 5"-->
    <!--:value="'tab-' + i"-->
    <!--:key="i"-->
    <!--&gt;-->
    <div class="item-box">
      <v-subheader center>搜索结果</v-subheader>
      <v-card
        v-if="!isLoading"
        flat>
        <user-item
          v-if="items.length!=0"
          :user_list="items"
          list_type="search"/>
        <div
          v-else
          class="empty mt-0 py-5 px-3 grey--text"
          center>{{ search_text }}</div>
      </v-card>

      <component
        v-if="isLoading"
        :is="cView"
      />
    </div>
    <!--</v-tab-item>-->
    <!--</v-tabs>-->
  </div>
</template>


<script>
import UserItem from "../Organizations/Popmodal/UserItem";
import Loading from "../Commons/Loading";
import { mapState, mapMutations } from "vuex";

export default {
	name: "Search",
	components: {
		UserItem,
		Loading,
	},
	props: {

	},
	data() {
		return {
			items: [],
			isLoading: false,
			cView: "Loading",
			searchValue: "",
			// itemHeight: 0,
			// nowOrg: "",
			// nowDept: "",
			// nowGroup: "",
			// 默认选择机构
			// tabNum: 1,
			search_text: "请输入搜索关键词，姓名将进行模糊匹配，联系方式将进行精确匹配"
		};
	},
	computed: {
		...mapState(["user_info", "selected_org", "search_value"])
	},
	watch: {
		searchValue: function(nValue, oValue) {
			this.setSearchValue(nValue);
			this.debouncedGetAnswer();
		}
	},
	created() {
		this.debouncedGetAnswer = window._.debounce(this.getUser, 1000);
	},
	mounted() {
		// this.getUrlValue();
		// this.getUserLocation();
		// this.getUser();
		this.searchValue = this.search_value;
	},
	methods: {
		...mapMutations(["setSearchValue"]),
		getUser() {
			this.isLoading = true;
			this.axios.post("/api/org/search", {
				key_word: this.searchValue
			}).then((res)=>{
				if(res.data.errcode === 0){
					this.items = res.data.data;
				}else{
					this.search_text = res.data.errmsg;
				}
				this.isLoading = false;
			}).catch((err)=>{
				// console.log(err);
			});
		},
		// getUrlValue () {
		// 	var decodeUrl = decodeURI(window.location.href);
		// 	var query = decodeUrl.split("?")[1];
		// 	var vars = query.split("&");
		// 	var getUrl = [];
		// 	var pair;
		// 	for (var i = 0; i < vars.length; i++) {
		// 		pair = vars[i].split("=");
		// 		getUrl[pair[0]] = pair[1];
		// 	}
		// 	this.searchValue = getUrl[pair[0]];
		// },
		// 获取用户的机构，部门，群组
		// getUserLocation () {
		// 	let userDepts = [];
		// 	let userGroups = [];
		// 	// 获取当前用户所在的所有部门和群组
		// 	this.user_info.depts.forEach(val => {
		// 		userDepts.push(val.id);
		// 	});
		// 	this.user_info.groups.forEach(val => {
		// 		userGroups.push(val.id);
		// 	});
		//
		// 	// 找到用户当前机构名
		// 	this.user_info.orgs.forEach(val => {
		// 		if(val.id === this.selected_org.id) {
		//
		// 			this.nowOrg = val.name;
		//
		// 			// 找出用户所属的当前机构下的部门和群组
		// 			let deptArr = [];
		// 			let groupArr = [];
		// 			val.depts.forEach(val2 => {
		// 				if(this.isInArr(val2.id, userDepts)) {
		// 					deptArr.push(val2.name);
		// 				}
		// 			});
		// 			this.nowDept = deptArr.join(",");
		//
		// 			val.groups.forEach(val3 => {
		// 				if(this.isInArr(val3.id, userGroups)) {
		// 					groupArr.push(val3.name);
		// 				}
		// 			});
		// 			this.nowGroup = groupArr.join(",");
		// 		}
		// 	});
		// },
		// touchTab(num) {
		// 	this.tabNum = num;
		// }
	},
};
</script>

<style scoped>
  .yet-empty {
    padding: 4px 16px;
  }
  .color-grey {
    color:#999;
  }
  .item-box{
    overflow-y: scroll;
  }
</style>