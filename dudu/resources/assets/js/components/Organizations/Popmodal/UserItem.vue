<template>
  <div>
    <v-list
      three-line
      class="pa-0">
      <v-list-tile
        v-for="(item, index) in user_list"
        :key="index"
        style="border-bottom:solid 1px #eee;"
        @click="viewUserDetail(item.id)"
      >
        <v-layout>
          <!--头像-->
          <v-flex
            xs2
            class="align-self-center">

            <v-list-tile-avatar class="mt-0">
              <img :src="item.avatar">
            </v-list-tile-avatar>
          </v-flex>

          <!--信息-->
          <v-flex
            xs6
            class="align-self-center">
            <v-list-tile-content>
              <!--姓名-->
              <v-list-tile
                v-html="item.name"/>

              <v-list-tile
                v-if=" list_type == 'dept' && item.agreed"
                class="caption"
                style="font-size: 14px;color: gray;"
                v-html="`审批人：` + item.agreed.agreed_user.name"/>

              <v-list-tile
                v-if="list_type == 'dept' && item.agreed"
                class="caption"
                style="font-size: 14px;color: gray;"
                v-html="`加入时间：` + item.agreed.agreed_time.split(' ')[0]"/>

              <!--单位职务-->
              <v-list-tile-sub-title
                v-if="list_type==='search'"
                v-html="item.identity"/>
              <!--电话-->
              <v-list-tile-sub-title
                v-if="list_type==='search'"
                v-html="item.tel"/>

              <v-list-tile-sub-title
                v-if="list_type==='group'"
                v-html="item.pivot.role.name"
              />
            </v-list-tile-content>
          </v-flex>

          <!--功能按钮-->
          <v-flex
            justify-end
            xs4
            class="align-self-center"
            style="text-align:right;"
          >
            <v-btn-toggle>
              <v-btn
                v-if="list_type==='group' && if_group_owner"
                flat
                @click.stop="changeRole(item)">
                <v-icon class="iconfont dudu-qiehuanjiaose"/>
              </v-btn>
              <v-btn flat>
                <v-icon class="iconfont dudu-chakan"/>
              </v-btn>
            </v-btn-toggle>
          </v-flex>
        </v-layout>
      </v-list-tile>
    </v-list>
  </div>
</template>


<script>
import {mapState} from "vuex";

export default {
	name: "UserItem",
	components: {},
	props: {
		user_list: {
			type: Array,
			default: () => []
		},
		list_type: {
			type: String,
			default: () => ""
		},
	},
	data() {
		return {
			dialog: false,
			if_group_owner: false
		};
	},
	computed: {
		...mapState(["user_info"]),
	},
	mounted() {
		// 初始化数据
		this.initData();
	},
	methods: {
		// 初始化数据
		initData() {
			// 若是群组用户列表，则构建权限管理
			if (this.list_type === "group") {
				// 判断用户是否为群主
				this.user_list.forEach(val => {
					if(val.id === this.user_info.id){
						if (val.pivot.role.id === 5) {
							this.if_group_owner = true;
						}
					}
				});
			}
		},

		// 查看用户详情
		viewUserDetail: function (id) {
			if (this.user_info.id === id) {
				this.$router.push({path: "/mine"});
			} else {
				this.$router.push({path: "/user_info", query: {id: id}});
			}
		},

		// 切换用户群组内身份
		changeRole: function (item) {
			if (item.pivot.role.id === 5) {
				this.$toast("不能修改群组创建人的角色", "warning");
				return;
			}
			this.$emit("showRole", item);
		}
	},
};
</script>

<style scoped>
</style>