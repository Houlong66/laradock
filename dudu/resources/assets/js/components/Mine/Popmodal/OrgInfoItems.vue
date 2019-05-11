<template>
  <div>
    <v-layout>
      <v-flex xs-8>
        <v-list-tile-title class="pl-2">
          {{ org }}-{{ dept }}
        </v-list-tile-title>
      </v-flex>
      <v-flex 
        xs-4 
        class="text-xs-right">
        <v-btn 
          small
          @click="exit()">
          退出
        </v-btn>
      </v-flex>
    </v-layout>
    <v-dialog
      v-model="dialog"
      max-width="290"
    >
      <v-card>
        <v-card-title class="headline">退出机构</v-card-title>
        <v-card-text>
          退出机构后相关信息将被全部清除，您确定要退出 {{ org }} 机构吗？
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            color="green darken-1"
            flat="flat"
            @click="dialog = false"
          >
            取消
          </v-btn>
          <v-btn
            color="green darken-1"
            flat="flat"
            @click="confirmExit()"
          >
            确定
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
	name: "OrgInfoItems",
	components: {
	
	},
	props: {
		org: {
			type: String,
			default: ()=>""
		},
		dept: {
			type: String,
			default: ()=>""
		},
		org_id: {
			type: Number,
			default: ()=>0
		}
	
	},
	data() {
		return {
			dialog: false
		};
	},
	methods: {
		exit: function() {
			this.dialog = true;
		},
		confirmExit: function() {
			this.dialog = false;
			this.axios.post("/api/mine/exitOrg").then((res)=>{
			}).catch((err)=>{
				// console.log(err);
			});
		}
	}
};
</script>

<style scoped>

</style>