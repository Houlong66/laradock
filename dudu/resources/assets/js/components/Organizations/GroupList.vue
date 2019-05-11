<template>
  <div>
    <header-text
      :header_text="header_text"
      entry_type="group_list"/>

    <!--用户加入群组卡片展示-->
    <v-list
      three-line
      class="pa-0">
      <div
        v-for="(item, index) in groups"
        :key="index"
      >
        <!--标题-->
        <div
          class="contacts-title pt-2 pb-2">{{ item.name }}
        </div>

        <v-list-tile
          v-for="(g, i) in item['data']"
          :key="i"
          style="border-bottom: solid 1px #eee;"
          @click="viewUserDetail(g.id || g.user_id)"
        >
          <v-layout
            justify-space-between
          >
            <!--信息区-->
            <v-flex
              xs8
              class="align-self-center text-truncate">
              <!--名字-->
              <v-list-tile-title
                v-html="g.name"/>
              <!--成员人数-->
              <v-list-tile-sub-title
                v-html="`成员人数（${g.users.length}）`"/>
            </v-flex>

            <!--功能区-->
            <v-flex
              xs4
              class="align-self-center"
              style="text-align:right;"
            >

              <v-btn-toggle>
                <v-btn
                  v-if="g.pivot.role_id === 5"
                  flat
                  @click.stop="show(g)">
                  <v-icon class="iconfont dudu-Group-"/>
                </v-btn>

                <v-btn flat>
                  <v-icon class="iconfont dudu-chakan"/>
                </v-btn>
              </v-btn-toggle>

              <!--修改群名弹框-->
              <div class="text-xs-center">
                <v-dialog
                  v-model="edit_dialog"
                  width="500">
                  <v-card>
                    <v-card-title
                      class="headline grey lighten-2"
                      primary-title>修改群名
                      <v-icon
                        class="iconfont dudu-guanbi1"
                        @click="edit_dialog = false"/>
                    </v-card-title>
                    <v-card-text>
                      <v-layout>
                        <v-flex
                          xs12
                          md4>
                          <v-text-field
                            v-model="groupName"
                            :rules="nameRules"
                            :counter="20"
                            label="修改群名"
                            required
                            @blur="scrollTo"/>
                        </v-flex>
                      </v-layout>
                    </v-card-text>
                    <v-divider/>
                    <v-card-actions>
                      <v-spacer/>
                      <v-btn
                        color="primary"
                        flat
                        @click="edit_dialog = false">
                        取消
                      </v-btn>
                      <v-btn
                        color="primary"
                        flat
                        @click="edit_groupName">
                        确认
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
              </div>

            </v-flex>
          </v-layout>
        </v-list-tile>
      </div>
    </v-list>
  </div>
</template>


<script>
import HeaderText from "./Popmodal/HeaderText";
import {mapState,mapActions} from "vuex";

export default {
	name: "GroupList",
	components: {
		HeaderText,
	},
	data() {
		return {
			groups: {},
			dialog: false,
			header_text: "",
			edit_dialog: false,
			groupName:"",
			groupId: "",
			nameRules: [
				// v => !!v || '内容不能为空',
				v => v.length <= 20 || "超过长度"
			],
		};
	},
	computed: {
		...mapState(["selected_org", "user_info", "selected_org_user_info"]),
	},
	mounted() {
		this.initData();
	},
	methods: {
		...mapActions(["initUser"]),
		initData() {
			// 获取用户当前机构加入的群组
			let groups = this.selected_org_user_info.groups;
			this.header_text = `已加群组(${groups.length})`;
			if (groups.length === 0) {
				this.no_data = true;
			} else {

				let type_list = [];
				groups.forEach((v, i) => {
					// 分类数据
					if (type_list.indexOf(v.type) === -1) {
						// 获取分类名称
						let name = null;
						switch (v.type) {
						case 0:
							name = "工作群组";
							break;
						case 1:
							name = "日程群组";
							break;
						}
						let new_v = {
							name: name,
							data: []
						};
						this.$set(this.groups, v.type, new_v);
						type_list.push(v.type);
					}
					this.groups[v.type]["data"].push(v);
				});
			}
			this.isLoading = false;
		},
		viewUserDetail: function (id) {
			this.$router.push({path: "/group_user_list", query: {group_id: id}});
		},

		edit_groupName() {
			let that = this;
			let data = {
				group_id: this.groupId,
				name: this.groupName
			};
			this.axios.post("/api/group/editGroupName", data).then((res) =>{
				that.edit_dialog = false;
				that.initUser().then((res) =>{
					that.initData();
				});
			});
		},
		show(item) {
			this.groupName = item.name;
			this.edit_dialog = true;
			this.groupId = item.id;
		}
	},
};
</script>

<style scoped>
.contacts-title {
padding: 0 0 0 16px;
width: 100%;
font-size: 1rem;
border-left: 3px solid #ee412a;
border-bottom: dotted 1px #ddd;
}
.v-card__title {
justify-content: space-between
}
</style>