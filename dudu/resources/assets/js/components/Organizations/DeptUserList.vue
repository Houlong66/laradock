<template>
  <div>
    <v-container
      v-if="!isLoading"
      class="pa-0">

      <div v-if="!no_data && is_org_user">
        <span class="org-title">{{ dept_name }}（{{ this_org_users.length }}）</span>

        <!-- 当前机构用户 -->
        <v-card
          v-if="!no_data"
          flat>

          <user-item
            :user_list="this_org_users"
            list_type="dept"
          />

        </v-card>

        <!-- 子机构用户 -->
        <v-flex v-if="son_orgs.length !== 0">
          <span class="org-title">对接子机构：{{ son_orgs[0].name }}（{{ son_orgs[0].users.length }}）</span>
          <v-card
            v-if="!no_data"
            flat>
            <user-item
              :user_list="son_orgs[0].users"
              list_type="dept"
            />
          </v-card>
        </v-flex>

        <!-- 孙子机构用户 -->
        <template
          v-if="grandson_orgs.length !== 0"
        >
          <v-flex
            v-for="(item, index) in grandson_orgs"
            :key="index">
            <span class="org-title">孙机构：{{ item.name }}（{{ item.users.length }}）</span>
            <v-card
              v-if="!no_data"
              flat>
              <user-item
                :user_list="item.users"
                list_type="dept"
              />
            </v-card>
          </v-flex>
        </template>
      </div>
    </v-container>

    <div v-if="!isLoading && no_data">
      <v-flex>
        <v-layout
          row
          justify-center
          class="py-4"
          align-center>
          <span class="grey--text">暂无部门信息</span>
        </v-layout>
      </v-flex>
    </div>

    <component
      v-if="isLoading"
      :is="cView"
    />
  </div>
</template>


<script>
import Loading from "../Commons/Loading";
import UserItem from "../Organizations/Popmodal/UserItem";
import {mapState, mapMutations} from "vuex";

export default {
	name: "DeptUserList",
	components: {
		Loading,
		UserItem,
	},
	data: function () {
		return {
			isLoading: true,
			cView: "Loading",
			dept_id: null,
			// 当前机构 子机构 孙子机构人员信息
			this_org_users: null,
			son_orgs: null,
			grandson_orgs: null,
			no_data: false,
			is_org_user: false,
			// 权限控制
			view_user_control: { // 创建群组功能
				arr: [1, 2],
				permission: false
			},
		};
	},
	computed: {
		...mapState(["selected_org", "user_info"]),
		dept_name() {
			return this.selected_org.smart_depts[this.dept_id].name;
		}
	},
	mounted() {
		// 初始化展示数据
		this.initData();
	},
	methods: {
		...mapMutations(["haveOrgAccess"]),
		// 初始化展示数据
		initData() {
			// 判断用户是否已加入机构
			if (!this.selected_org) {
				this.no_data = true;
				this.isLoading = false;
				return;
			}

			// 获取 url 参数
			this.dept_id = this.$route.query.dept_id;
			// 判断用户是否有权查看
			// 部门成员、机构超管、机构系统管理员可看
			this.haveOrgAccess(this.view_user_control);
			if (this.user_info.smart_depts[this.dept_id] || this.view_user_control.permission){
				this.is_org_user = true;
			}

			// 判断展示数据类型
			let url = `/api/user/dept/${this.dept_id}?org_id=${this.selected_org.id}`;
			this.axios.get(url).then((res) => {
				let data = res.data.data;
				this.this_org_users = data.users;

				this.son_orgs = data.son_orgs;
				this.grandson_orgs = data.grandson_orgs;
				// 判断数据是否为空，只有三种机构都没有user 的时候才为空
				if (this.this_org_users.length === 0) {
					let flag_son = 1;
					let flag_grandson = 1;
					// 判断所有子机构有无用户
					for (let i = 0, len = this.son_orgs.length; i < len; i++) {
						if (this.son_orgs[i].users.length === 0) {
							continue;
						} else {
							this.flag_son = 0;
							break;
						}
					}
					// 判断所有孙机构有无用户
					for (let i = 0, len = this.grandson_orgs.length; i < len; i++) {
						if (this.grandson_orgs[i].users.length === 0) {
							continue;
						} else {
							this.flag_granson = 0;
							break;
						}
					}
					// 如果都没有，则无数据
					if (flag_son && flag_grandson) {
						this.no_data = true;
						this.isLoading = false;
						return;
					}
				}
				this.isLoading = false;
			}).catch((err) => {
				// console.log(err);
			});
		},
	}
};
</script>

<style scoped>
  .org-title {
    align-items: center;
    display: flex;
    height: 48px;
    font-size: 14px;
    font-weight: 500;
    padding: 0 16px;
    color: rgba(0, 0, 0, .54);
  }
</style>