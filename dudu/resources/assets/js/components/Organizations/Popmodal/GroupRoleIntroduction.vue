<template>
  <div>
    <v-layout>
      <span
        class="caption mb-0 grey--text align-self-center"
        @click="openDialog">
        <v-icon
          small
          color="blue"
          class="iconfont dudu-yiwen"/>
        查看群组角色说明
      </span>
    </v-layout>
    <!--工作群角色说明弹框-->
    <v-dialog
      v-model="w_role_info_dialog"
    >
      <v-card>
        <v-card-title
          class="subheading grey lighten-2"
          primary-title
        >
          工作群角色说明
        </v-card-title>

        <v-card-text class="px-0">
          <v-list
            two-line
            subheader>
            <v-list-tile
              avatar
              class="mb-3">
              <v-list-tile-content >
                <v-list-tile-title>任务发放人</v-list-tile-title>
                <p class="caption grey--text pt-1 mb-0">
                  若以任务发放人身份加入工作群组，您仅负责发放任务。
                </p>
              </v-list-tile-content>
            </v-list-tile>

            <v-list-tile
              avatar
              class="mb-3">
              <v-list-tile-content >
                <v-list-tile-title>任务签收人</v-list-tile-title>
                <p class="caption grey--text pt-1 mb-0">
                  若以任务签收人身份加入工作群组，您仅负责签收和上报任务。
                </p>
              </v-list-tile-content>
            </v-list-tile>

            <v-list-tile avatar>
              <v-list-tile-content >
                <v-list-tile-title>部门/单位领导</v-list-tile-title>
                <p class="caption grey--text pt-1 mb-0">
                  若以部门/单位领导身份加入工作群组，您仅负责督办任务。
                </p>
              </v-list-tile-content>
            </v-list-tile>
          </v-list>
        </v-card-text>

        <v-divider/>

        <v-card-actions>
          <v-spacer/>
          <v-btn
            color="primary"
            flat
            @click="w_role_info_dialog = false"
          >
            我知道了
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!--日程群角色说明弹框-->
    <v-dialog
      v-model="s_role_info_dialog"
    >
      <v-card>
        <v-card-title
          class="subheading grey lighten-2"
          primary-title
        >
          日程群角色说明
        </v-card-title>

        <v-card-text class="px-0">
          <div class="mb-4">
            <p class="subheading mb-1 px-4">日程共享人</p>
            <p class="caption px-4 grey--text pt-1 mb-0">
              您所允许公开的日程信息将公开给群组内其他成员；
            </p>
            <p class="caption px-4 grey--text pt-1 mb-0">
              未允许公开的日程信息，群组内其他成员只能看到相应时间段您已安排，但无法查看具体内容。
            </p>
          </div>

          <div>
            <p class="subheading mb-1 px-4">日程查看人</p>
            <p class="caption px-4 grey--text pt-1 mb-0">
              您自己的日程不作公开，但可查看群组内其他成员的日程信息。
            </p>
          </div>
        </v-card-text>

        <v-divider/>

        <v-card-actions>
          <v-spacer/>
          <v-btn
            color="primary"
            flat
            @click="s_role_info_dialog = false"
          >
            我知道了
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>

export default {
	name: "GroupRoleIntroduction",
	components: {},
	props: {
		dialog_type: {
			type: Number,
			default: 2
		},
	},
	data() {
		return {
			w_role_info_dialog: false,
			s_role_info_dialog: false,
		};
	},
	methods: {
		// 开启弹框
		openDialog(){
			if(this.dialog_type === 2) {
				this.w_role_info_dialog = true;
			}else{
				this.s_role_info_dialog = true;
			}
		}
	}
};

</script>

<style scoped>
</style>