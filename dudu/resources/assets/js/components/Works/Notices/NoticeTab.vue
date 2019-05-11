<template>
  <div>
    <v-tabs
      v-model="active"
      fixed-tabs>
      <v-tab
        v-for="(tab,index) in tabs"
        :key="index"
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
        slot-scope="props">
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
          v-if="active!=2"
          :style="props.item.status != '未读' ? {'min-width':'240px'} : {'min-width':'70px'}"
          class="text-xs-left">{{ props.item.status }}
        <span v-if="props.item.status != '未读'">（{{ props.item.time }}）</span>  </td>




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
	name: "NoticeTab",
	props: {
		notice: {
			type: Object,
			default: ()=>{},
		}
	},
	data () {
		return {
			active: 0,
			status_text: ["未发送", "未读", "已读"], // 状态文案
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
					text: "通知状态",
					align: "left",
					sortable: false,
					width: "110px",
					value: "status"
				},

			],
			work_datas: [[], []], // 2个状态的数据
			work_data: [],
		};
	},
	watch: {
		active: function (newV, oldV) {
			this.changeTabsData(newV);
		},
		notice: function (newV, oldV) {
			if (JSON.stringify(newV) != "{}") {
				this.init();
			}
		}
	},
	mounted () {
		this.init();
	},
	methods: {
		changeTabsData (status) {
			this.work_data = this.work_datas[status];
		},
		init () {
			this.work_datas = [[],[]];
			this.work_data = [];
			let temp_status_num = [0,0];
			this.notice.notification_items.forEach((value, index) => {
				if (value.item_type == 0) {
					if (value.status == 0) {
						temp_status_num[0]++;
					} else {
						temp_status_num[value.status - 1]++;
					}
					let time = null;
					switch(value.status){
					case 1: time = this.notice.send_time;break;
					case 2: time = value.check_time;break;
					default: break;
					}
					let temp_data = {
						dept: `${value.user.depts[0].name}`,
						name: `${value.user.name}`,
						time: time,
						status: this.status_text[value.status],
						id: value.user.id
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
					text: `未读 ${temp_status_num[0]}`
				},
				{
					text: `已读 ${temp_status_num[1]}`
				}
			];
			this.changeTabsData(0);
		}
	}
};
</script>

<style scoped>

</style>
