<template>
  <div>
    <v-container
      v-if="!isLoading"
      class="px-0 pb-2 border-be">
      <v-layout
        column
        class="px-3">
        <v-flex class="pb-1">
          <div>
            <p class="title pb-2 mb-1 border-b">{{ name }}</p>
          </div>
        </v-flex>
        <v-flex class="caption grey--text text--darken-1 mb-4">
          <p class="mb-0">创建人：{{ create_name }}</p>
          <p class="mb-0">创建时间：{{ create_at }}</p>
        </v-flex>

        <v-flex
          v-if="comment !== null"
          class="mb-4">
          <div>
            <label class="subheading">
              <v-icon
                size="20"
                color="grey"
                class="iconfont dudu-shuoming-copy-copy"/>
              {{ type===1 ? "日程" : "提醒" }}内容
            </label>
            <p
              class="body-1 work-text grey--text text--darken-2 pa-3 mt-1 mb-0"
            >{{ comment }}</p>
          </div>
        </v-flex>

        <v-flex
          v-if="type===1"
          class="mb-4 py-2 border-b">
          <label class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-jiezhishijian"/>
            日程时间段
          </label>
          <p class="body-1 grey--text text--darken-2 pl-2 pt-1 mb-0">{{ period }}</p>
        </v-flex>

        <v-flex
          v-if="type===1"
          class="mb-4 py-2 border-b">
          <label class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-zhuangtai"/>
            日程状态
          </label>
          <p class="body-1 blue--text text--darken-2 pl-2 pt-1 mb-0">{{ public_temp ? '公开' : '非公开' }}</p>
        </v-flex>

        <v-flex
          v-if="type===3"
          class="mb-4 py-2 border-b">
          <label class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-rili"/>
            纪念日日期
          </label>
          <p class="body-1 grey--text text--darken-2 pl-2 pt-1 mb-0">{{ period }}</p>
        </v-flex>

        <v-flex
          v-if="type===3"
          class="mb-4 py-2 border-b">
          <label class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-zhuangtai"/>
            日期类型
          </label>
          <p class="body-1 blue--text text--darken-2 pl-2 pt-1 mb-0">{{ is_solar ? '公历' : '农历' }}</p>
        </v-flex>

        <v-flex class="mb-4 py-2 border-b">
          <label class="subheading">
            <v-icon
              size="20"
              color="grey"
              class="iconfont dudu-tips"/>
            提醒时间
          </label>
          <!--<p v-for="(time, index) in remind_time" :key="index" class="body-1 grey&#45;&#45;text text&#45;&#45;darken-2 pl-2 pt-1 mb-0">-->
          <!--{{ time }}</p>-->
          <v-data-table
            :headers="headers"
            :items="remind_time_arr"
            :pagination.sync="pagination"
            hide-actions
            hide-headers
            class="elevation-1 mt-2"
          >
            <template v-slot:items="props">
              <td class="text-xs-left">{{ props.item.time }}</td>
            </template>
          </v-data-table>
        </v-flex>
      </v-layout>


      <v-layout
        column
        class="px-3">
        <v-btn
          color="primary"
          block
          @click="edit()">
          编辑
        </v-btn>
        <!--<v-btn-->
        <!--color="primary"-->
        <!--block-->
        <!--@click="showDeleteDialog()">-->
        <!--删除-->
        <!--</v-btn>-->
      </v-layout>
    </v-container>

    <v-dialog
      v-model="deleteDialog"
      max-width="290"
    >
      <v-card>
        <v-card-title>确定删除此日程？</v-card-title>

        <v-card-actions>
          <v-spacer/>

          <v-btn
            v-btn-control="deleteSchedule"
            color="green darken-1"
            flat="flat"
          >
            确定
          </v-btn>

          <v-btn
            color="green darken-1"
            flat="flat"
            @click="deleteDialog = false"
          >
            取消
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <Loading v-if="isLoading"/>
  </div>
</template>

<script>
import Loading from "../Commons/Loading";
import {mapState} from "vuex";


export default {
	components: {
		Loading
	},
	data() {
		return {
			nameRules: [
				v => !!v || "标题不能为空",
				v => v.length <= 20 || "不可超过20个字符"
			],
			is_edit: true,
			public_temp: "",
			period: "",
			remind_time: "",
			deleteDialog: false,

			id: "",
			name: "",
			comment: "",
			time: "",
			remind_at: "",
			start_at: "",
			public: null,
			fullday: null,
			isLoading: true,
			// 1，2，3 => 日程 or 提醒 or 纪念日
			type: null,
			is_solar: null,
			create_name: null,
			create_at: null,

			// 提醒表格样式
			headers: [
				{
					text: "提醒时间",
					value: "time",
				}
			],
			remind_time_arr: [],
			pagination: {
				sortBy: "time",
				rowsPerPage: 10
			}
		};
	},
	computed: {
		...mapState(["selected_org"])
	},
	created() {
		this.id = this.$route.query.id;
		this.getScheduleDetail(this.id);
	},

	mounted(){

	},
	methods: {
		getScheduleDetail(id) {
			this.axios.get(`/api/schedule/detail/${id}`)
				.then((res) => {
					let data = res.data.data;
					this.is_solar = res.data.data.is_solar;

					// let orgs_userlist = this.selected_org.users;

					// orgs_userlist.forEach((v, i) => {
					// 	if (data.creater_id === v.id) {
					// 		this.create_name = v.name;
					// 		return;
					// 	}
					// });
					this.create_name = data.user.name;

					if (res.data.errcode === 0) {
						// success
						this.name = data.name;
						this.comment = data.comment;
						this.start_at = data.start_at;
						this.end_at = data.end_at;
						this.remind_time = data.remind_at;
						this.public_temp = data.public;
						this.fullday = data.fullday;
						this.type = data.type;
						this.create_at = data.created_at;    //  创建的时间

						// 1，2，3 => 日程 or 提醒 or 纪念日

						// 纪念日
						// if (this.type == 3) {
						// 	this.is_solar = data.is_solar;
						// 	this.create_title = "纪念日创建人";
						// 	this.create_time  = "纪念日创建时间";
						// }
						// if (this.type == 2){
						// 	this.create_title = "提醒创建人";
						// 	this.create_time  = "提醒创建时间";
						// }
						// if (this.type == 1){
						// 	this.create_title = "日程创建人";
						// 	this.create_time  = "日程创建时间";
						// }

						// 日程
						if (this.start_at && this.end_at) {
							this.period = this.getTime();
						}

						// 提醒时间构建数组
						let remind_time_arr = [];
						this.remind_time.forEach((v, i) => {
							let v_obj = {
								time: v,
							};
							remind_time_arr.push(v_obj);
						});
						this.remind_time_arr = remind_time_arr;

						this.isLoading = false;
					} else {
						// fail
					}
				}).catch((err) => {
					// console.log(err);
				});
		},
		edit() {
			this.is_edit = false;
			if (this.type == 3) {
				this.$router.push({path: "/schedules/memorial/create", query: {id: this.id}});
			} else {
				this.$router.push({path: "/schedules/create", query: {id: this.id}});
			}
		},
		showDeleteDialog() {
			this.deleteDialog = true;
		},
		deleteSchedule() {
			return this.axios.get(`/api/schedule/delete/${this.id}`)
				.then((res) => {
					if (res.data.errcode == 0) {
						// success
						this.deleteDialog = false;
						this.$router.push("/schedules");
					} else {
						// fail
					}
				}).catch((err) => {
					// console.log(err);
				});
		},

		getTime() {
			let start_at = this.start_at.split(" ");
			let end_at = this.end_at.split(" ");
			let time = "";
			if (start_at[0] == end_at[0]) {
				if(start_at[1] == end_at[1]){
					time = `${start_at[0]} (全天)`;
				}else{
					time = `[ ${start_at[0] } ] ` +" "+ start_at[1] + " 至 " + end_at[1];
				}
			} else if (start_at[0] < end_at[0]) {
				time = `${start_at[0]} 至 ${end_at[0]}`;
			}
			return time;
		},
	}
};

</script>

<style scoped>
.create-form {
background-color: white;
padding: 1rem;
}

.schedule-item {
align-items: center;
color: inherit;
display: flex;
font-size: 16px;
font-weight: 400;
height: 48px;
margin: 0;
position: relative;
text-decoration: none;
transition: .3s cubic-bezier(.25, .8, .5, 1);
}

.schedule-item-border {
border-bottom: 1px solid #eee;
}
</style>