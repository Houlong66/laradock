<template>
  <div>
    <!--四个状态标题-->
    <v-tabs
      v-model="active"
      fixed-tabs>
      <v-tab
        v-for="(tab,index) in tabs"
        :key="index"
        ripple
      >
        {{ tab.text }}
      </v-tab>
    </v-tabs>

    <v-data-table
      :headers="work_headers"
      :items="work_data"
      hide-actions
      class="elevation-1"
    >
      <template
        slot="items"
        slot-scope="props"
      >
        <tr
          v-if=" active!=2 && active!=3 "
        >
          <td class="text-xs-left">
            <div
              style="width:70px;"
              class="text-truncate py-2">{{ props.item.name }}</div>
          </td>

          <td class="text-xs-left">
            <div
              style="width:70px;"
              class="text-truncate py-2">{{ props.item.dept }}</div>
          </td>

          <td

            class="text-xs-left">{{ props.item.status }}
          </td>

        </tr>

        <tr
          v-else
          @click="jumpToReport(props.item.user_id, props.item.task_item_dept_id)"
        >
          <td class="text-xs-left">
            <div
              style="width:70px;"
              class="text-truncate py-2">{{ props.item.name }}</div>
          </td>

          <td class="text-xs-left">
            <div
              style="width:70px;"
              class="text-truncate py-2">{{ props.item.dept }}</div>
          </td>

          <td
            class="text-xs-left"
          >
            {{ props.item.status }}>
          </td>

        </tr>

      </template>

      <template slot="no-data">
        <p 
          class="my-0 py-2 grey--text" 
          style="text-align:center">暂无相关数据</p>
      </template>
    </v-data-table>



  </div>
</template>

<script>

export default {
	name: "TaskTab",
	props: {
		task: {
			type: Object,
			default: () => {
			},
		}
	},
	data() {
		return {
			active: 0,
			status_text: ["未发送", "未签收", "未上报", "已上报", "已办结"], // 状态文案
			tabs: [],
			work_headers: [
				{
					text: "接收人",
					align: "left",
					value: "name"
				},
				{
					text: "所属部门",
					align: "left",
					value: "dept"
				},
				{
					text: "任务状态",
					align: "left",
					sortable: false,
					width: "110px",
					value: "status"
				}
			],
			work_datas: [[], [], [], []], // 四个状态的数据
			work_data: [],
			userInfoDialog: false,
		};
	},
	watch: {
		active: function (newV, oldV) {
			this.changeTabsData(newV);
		},
		task: function (newV, oldV) {
			if (JSON.stringify(newV) != "{}") {
				this.init();
			}
		}
	},
	mounted() {
		this.init();
	},
	methods: {
		changeTabsData(status) {
			this.work_data = this.work_datas[status];
		},

		showUserInfo(item) {
			this.userInfoDialog = true;
		},
		init() {
			let temp_status_num = [0, 0, 0, 0];
			this.work_datas = [[], [], [], []]; // 四个状态的数据
			this.work_data = [];
			this.task.task_items.forEach((value, index) => {

				if (value.item_type == 0) {

					if (value.status == 0) {
						temp_status_num[0]++;
					} else {
						temp_status_num[value.status - 1]++;
					}

					let time = null;
					switch (value.status) {
					case 1:
						time = this.task.send_time;
						break;
					case 2:
						time = value.receive_time;
						break;
					case 3:
						time = value.report_time;
						break;
					case 4:
						time = value.audit_time;
						break;
					default:
						break;
					}

					// rename id to user_id
					// 判断部门是否已被删除 todo 兼容删除部门功能，需要优化！
					var dept_n = "";
					var dept_i = "";
					if(value.dept === null && value.dept_id !== 0){
						dept_n = "已删除";
						dept_i = 0;
					}else{
						dept_n = value.dept_id === 0 ? value.user.depts[0].name : value.dept.name;
						dept_i = value.dept_id === 0 ? value.user.depts[0].id : value.dept.id;
					}
					let temp_data = {
						name: value.user.name,
						dept: dept_n,
						time: time,
						status: this.status_text[value.status],
						user_id: value.user.id,
						id: value.id,
						dept_id: dept_i,
						task_item_dept_id: value.dept_id
					};

					if (value.status == 0) {
						this.work_datas[0].push(temp_data);
					} else {
						this.work_datas[value.status - 1].push(temp_data);
					}

				}
			});
			this.tabs = [
				{
					text: `未签收 ${temp_status_num[0]}`
				},
				{
					text: `已签收 ${temp_status_num[1]}`
				},
				{
					text: `已上报 ${temp_status_num[2]}`
				},
				{
					text: `已办结 ${temp_status_num[3]}`
				},
			];
			this.changeTabsData(0);
		},

		jumpToReport(user_id, dept_id) {
			this.$router.push({
				path: `/to_report_task/${this.$route.params.id}`,
				query: {user_id: user_id, dept_id: dept_id}
			});
		}
	}
};
</script>

<style scoped>
</style>