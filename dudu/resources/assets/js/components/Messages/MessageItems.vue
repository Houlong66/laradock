<template>

  <div
    :class="itemImportantType"
    @click="flag && setItemRead()">

    <v-card flat>
      <v-card-title justify-space-between>
        <v-layout row>
          <v-flex xs11>
            <h3 class="pb-1 font-weight-regular">
              {{ item.title }}
              <i
                v-if="item.self_send == 1"
                class="iconfont dudu-send red--text"/>
            </h3>
            <ul class="pl-0">
              <li
                v-if="item.created_at"
                class="pt-1 grey--text">
                {{ item.created_at }}
              </li>
              <li
                v-if="item.content"
                class="pt-1 grey--text text--darken-1 line-limit-length">
                {{ item.content }}
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

import Dialogs from "../Commons/Dialogs";
import {mapState, mapActions} from "vuex";

export default {
	name: "MessageItems",
	inject: ["reload"],
	components: {
		Dialogs
	},
	props: {
		item: {
			type: Object,
			default: () => {
			},
		}
	},
	data() {
		return {
			flag: true
		};
	},
	computed: {
		...mapState(["selected_org","user_info"]),
		itemImportantType() {
			return ["item-extra-important", "item-important", "item-normal"][this.item.important];
		}
	},
	mounted() {

	},
	methods: {
		...mapActions(["initUser"]),
		// 消息的点击事件
		setItemRead() {
			let query_id = JSON.parse(this.item.params);

			/**
       * 注册申请类成功返回消息
 			 */
			// 注册机构成功和申请加入机构成功的返回
			let regOrgResFlag = (this.item.type === 5 && this.item.subtype === 1) ? true : false;
			let joinOrgResFlag = (this.item.type === 6 && this.item.subtype === 2) ? true : false;


			if (regOrgResFlag || joinOrgResFlag) {
				// 拿到的是注册成功的ID
				let org_id = query_id.org_id;

				// 判断当前的机构是否已经是要切换的机构
				if (this.selected_org.id == org_id) {
					this.$router.push({path: "/organizations"});
					this.setMessageStatus(this.item.id);
					return;
				}

				this.axios.get(`/api/my/org/${this.selected_org.id}/change_default?change_org=${org_id}`).then((res) => {
					if (res.data.errcode === 0) {
						this.initUser().then(() => {
							this.$router.push({path: "/organizations"});
							this.setMessageStatus(this.item.id);
							this.reload();
						});
					}
				});
				return;
			}

			// Url如果是调到 /message 可以直接设置已读
			if (this.item.url.split("/messages").length === 2) {
				// 设置消息为已读
				if (parseInt(this.item.status) !== 1){
					this.setMessageStatus(this.item.id);
					this.reload();
				}
				return ;
			}

			// 剩下的其他情况，直接访问 msg 的 url
			this.setMessageStatus(this.item.id);
			this.$router.push({path: this.item.url});
			this.reload();


		},

	}
};
</script>

<style scoped>

  .line-limit-length {
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
  }
</style>