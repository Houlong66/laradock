<template>
  <div
    :class="itemImportantType"
    @click="showItemDetail()">
    <v-card flat>
      <v-card-title justify-space-between>
        <v-layout row>
          <v-flex xs11 >
            <h3 class="pb-2 font-weight-light">
              {{ item.title }}
              <i
                v-if="item.self_send == 1"
                class="iconfont dudu-send red--text"/>
            </h3>
            <ul class="pl-0">
              <li
                v-if="item.send_time && !item.ask_time"
                class="pt-1 grey--text text--darken-1">
                <span class="pr-2 grey--text text--darken-3">发送时间</span>
                {{ item.send_time }}
              </li>
              <li
                v-if="item.deadline"
                class="pt-1 grey--text text--darken-1">
                <span class="pr-2 grey--text text--darken-3">截止时间</span>
                {{ item.deadline }}
              </li>
              <li
                v-if="item.ask_time"
                class="pt-1 grey--text text--darken-1">
                <span class="pr-2 grey--text text--darken-3">请示时间</span>
                {{ item.ask_time }}
              </li>
              <li
                v-if="item.t_type"
                class="pt-1 grey--text text--darken-1">
                <span class="pr-2 grey--text text--darken-3">任务类型</span>
                {{ item.t_type }}
              </li>
              <li
                v-if="item.t_receive_num!==undefined"
                class="pt-1 grey--text text--darken-1">
                <span class="pr-2 grey--text text--darken-3">完成情况</span>
                {{ item.t_accomplish_num }} / {{ item.t_receive_num }}
              </li>

              <li
                v-if="item.n_receive_num!==undefined"
                class="pt-1 grey--text text--darken-1">
                <span class="pr-2 grey--text text--darken-3">阅读情况</span>
                {{ item.n_read_num }} / {{ item.n_receive_num }}
              </li>

              <li
                v-if="auditStatus != null"
                class="pt-1 grey--text text--darken-1">
                <span class="pr-2 grey--text text--darken-3">审批状态</span>
                {{ auditStatus }}
              </li>

              <li
                v-if="item.a_type"
                class="pt-1 grey--text text--darken-1">
                <span class="pr-2 grey--text text--darken-3">请示类型</span>
                {{ item.a_type }}
              </li>
              <li
                v-if="item.ask_from"
                class="pt-1 grey--text text--darken-1">
                <span class="pr-2 grey--text text--darken-3">请示人</span>
                {{ item.ask_from }}
              </li>
            </ul>
          </v-flex>
          <v-flex
            xs1
            class="align-self-center">
            <div class="text-xs-right">
              <v-icon
                class="iconfont dudu-arrow grey--text lighten-3"
                size="24"/>
            </div>
          </v-flex>
        </v-layout>
      </v-card-title>
    </v-card>
    <v-divider class="grey lighten-3"/>
  </div>
</template>

<script>
export default {
	name: "WorkItems",
	props: {
		item: {
			type: Object,
			default: ()=>{},
		}
	},
	data(){
		return{
			auditStatus:null
		};
	},
	computed: {
		itemImportantType() {
			return ["item-normal","item-important","item-extra-important"][this.item.important];
		},
	},
	mounted(){
		this.initData();
	},

	methods: {
		// 初始化
		initData(){
			this.auditStatus = 	["无需审批", "待审批", "审批通过", "审批不通过","已批复"][this.item.audit_status];
		},
		showItemDetail(){
			let type = this.$route.params.type;
			if (type == 0) {
				if (this.item.self_send == 1) {
					if (this.item.audit_status == 3) {
						this.$router.push({path: "/create_task", query: {task_id: this.item.task_id}});
					} else {
						this.$router.push({path: `/task_detail_self/${this.item.task_id}`});
					}
				} else {
					this.$router.push({path: `/task_detail/${this.item.task_id}`, query: {dept_id: this.item.dept_id}});
				}
			} else if (type == 1) {
				if (this.item.self_send == 1) {
					if (this.item.audit_status == 3) {
						this.$router.push({path: "/create_notice", query: {notice_id: this.item.notification_id}});
					} else {
						this.$router.push({path: `/notice_detail_self/${this.item.notification_id}`});
					}
				} else {
					this.$router.push({path: `/notice_detail/${this.item.notification_id}`});
				}
			} else if (type == 2) {
				this.$router.push({path: `/ask_detail/${this.item.ask_id}`, query: {self_send: this.item.self_send}});
			}
		}
	}
};
</script>

<style scoped>
  .item-extra-important{
    border-left:solid 5px red;
  }
  .item-important{
    border-left:solid 5px orange;
  }
  .item-normal{
    border-left:solid 5px #ddd;
  }
</style>
