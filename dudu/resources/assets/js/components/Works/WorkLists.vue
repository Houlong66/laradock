<template>
  <v-layout column>
    <v-flex v-if="list===null && show_text ">

      <p
        class="grey--text text--lighten-1 mt-2"
        style="text-align: center">暂无工作信息
      </p>
    </v-flex>
    <v-flex
      v-else
      xs12>
      <v-list
        v-for="(type, index) in list"
        :key="index"
        three-line
        subheader>
        <div>
          <v-subheader
            class="grey lighten-4">
            <v-layout>
              <v-flex xs12>
                <span class="pl-2">{{ type.flag }} ({{ type.list.length }})</span>
              </v-flex>
            </v-layout>
          </v-subheader>

          <work-items
            v-for="(item, index) in type.list"
            :key="index"
            :item="item"/>
        </div>
      </v-list>
    </v-flex>

  </v-layout>
</template>

<script>
import WorkItems from "./WorkItems";
import {mapState} from "vuex";
export default {
	name: "WorkLists",
	components: {
		WorkItems
	},
	props: {
		list: {
			type: Object,
			default: () => { }
		},
	},
	data () {
		return{
			show_text:false
		};
	},

	computed:{
		...mapState(["selected_org_user_info"]),
	},
	beforeMount(){

	},
	mounted (){
		if(this.selected_org_user_info.groups.length !== 0){
			this.selected_org_user_info.groups.forEach((item,index)=>{
				if(item.type === 0){
					this.show_text = true;
					return false;
				}
			});
		}
	},
	methods: {
	}
};
</script>

<style scoped>
</style>
