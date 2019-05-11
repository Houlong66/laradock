<template>
  <div>
    <div
      style="padding-bottom:66px;background-color: #f5f5f5;">
      <div
        v-for="(item, index) in depts"
        :key="index"
      >
        <!--分类栏-->
        <div
          v-show="item['data'].length !== 0 "
          class="contacts-title py-2">{{ item['name'] }}
        </div>
        <v-list class="pa-0 mb-2">
          <!--部门项-->
          <v-list-tile
            v-for="(dept, i) in item['data']"
            :key="i"
            style="border-bottom:solid 1px #eee;"
            class="pt-1 pb-1"
            @click="deptUserList(dept)">
            <v-layout class="ml-2 mr-1">
              <v-flex
                xs8
                class="align-self-center"
                style="font-size:14px;">
                <span>
                  <v-icon
                    size="20"
                    class="iconfont dudu-bumen icon-blue mr-2"/>{{ dept.name }}</span>
              </v-flex>
              <!--箭头图标-->
              <v-flex
                xs4
                class="list-box align-self-center">
                <v-icon class="iconfont dudu-arrow"/>
              </v-flex>
            </v-layout>
          </v-list-tile>
        </v-list>
      </div>
    </div>
  </div>
</template>

<script>

export default {
	name: "DeptItem",
	components: {},
	props: {
		dept_list: {
			type: Array,
			default: () => []
		},
		org_msg: {
			type: Object,
			default: () => []
		},
	},
	data() {
		return {
			depts: {}
		};
	},
	mounted() {
		this.initData();
	},
	methods: {
		// 初始化数据
		initData() {
			// 获取部门信息
			if (this.dept_list.length !== 0) {
				let type_list = [];
				this.dept_list.forEach((v, i) => {
					// 分类数据
					if (type_list.indexOf(v.level) === -1) {
						// 获取分类名称
						let name = null;
						switch (v.level) {
						case 0:
							name = "本级部门";
							break;
						case 1:
							name = "下级单位";
							break;
						}
						let new_v = {
							name: name,
							data: []
						};
						this.$set(this.depts, v.level, new_v);
						type_list.push(v.level);
					}
					this.depts[v.level]["data"].push(v);
				});
			}
			this.isLoading = false;
		},
		// 查看部门用户信息
		deptUserList: function (val) {
			let url = `/organizations/dept_user_list?dept_id=${val.id}`;
			this.$router.push({path: url});
		},
	}
};

</script>

<style scoped>
  .icon-blue {
    color: #308ace !important;
  }

  .contacts-title {
    background: #fff;
    padding: 0 0 0 16px;
    width: 100%;
    font-size: 1rem;
    border-left: 3px solid #ee412a;
    border-top: dotted 1px #eee;
    border-bottom: dotted 1px #ddd;
  }

  .v-list__tile {
    padding: 0;
  }

  .list-box {
    text-align: right;
  }
</style>