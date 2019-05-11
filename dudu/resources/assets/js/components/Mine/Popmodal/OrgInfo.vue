<template>
  <div class="mt-2">
    <v-subheader 
      class="title-color" 
      style="background:#f5f5f5;">
      <span>机构信息</span>
    </v-subheader>
    <v-list class="pa-0">
      <div v-if="orgItems.length>0">
        <div
          v-for="(item, index) in orgItems"
          :key="index">
          <v-list-tile
            :key="index"
            class="pt-1 pb-1">
            <v-layout>
              <v-flex 
                xs8 
                class="align-self-center line-limit-length" 
                style="font-size:14px;">
                {{ item.name }}
                <!--<span -->
                <!--v-if="item.depts.length!=0" -->
                <!--class="color-grey"> > {{ orgItems.smart_org }}</span>-->
              </v-flex>
              <v-flex 
                xs4 
                class="align-self-center" 
                style="text-align:right;font-size:14px;">
                {{ item.role.name }}
              </v-flex>
            </v-layout>
          </v-list-tile>

          <v-divider v-if="index < orgItems.length-1"/>
        </div>
      </div>
      <v-flex v-if="orgItems.length==0">
        <v-layout
          style="border-bottom:dotted 1px #eee;"
          row
          justify-center>
          <v-btn
            color="error"
            flat
            @click="jumpToCreateOrg()">创建机构</v-btn>
        </v-layout>
        <v-layout
          row
          justify-center>
          <v-btn
            color="error"
            flat
            @click="jumpToJoinOrg()">加入机构</v-btn>
        </v-layout>
      </v-flex>
    </v-list>
  </div>
</template>


<script>
export default {
	name: "OrgInfo",
	components: {
	},
	props: {
		userDetailInfo: {
			type: Object,
			default: ()=>{}
		}
	},
	data() {
		return {
			orgItems: [],
		};
	},
	mounted() {
		this.orgItems = this.userDetailInfo.orgs;
	},
	methods: {
		jumpToJoinOrg () {
			this.$router.push("/apply_join_org");
		},
		jumpToCreateOrg () {
			this.$router.push("/organizations/joinOrg");
		}
	},
};
</script>

<style scoped>
.color-grey {
  color:#999;
}
.title-color {
  background: #f5f5f5;
}
.line-limit-length {
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
}
</style>