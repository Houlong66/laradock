<template>
  <div>
    <v-list two-line>
      <v-layout wrap>
        <v-flex 
          xs12 
          sm12>
          <v-layout>
            <v-flex xs3>
              <v-list-tile-title class="my-3 pl-3">选择机构</v-list-tile-title>
            </v-flex>
            <v-flex xs9>
              <v-layout>
                <v-flex xs10>
                  <v-text-field 
                    required/>
                </v-flex>
                <v-flex xs2>
                  <br>
                </v-flex>
              </v-layout>
            </v-flex>
          </v-layout>
        </v-flex>
        <v-flex 
          xs12 
          sm12>
          <v-layout>
            <v-flex xs3>
              <v-list-tile-title class="my-3 pl-3">选择部门</v-list-tile-title>
            </v-flex>
            <v-flex xs9>
              <v-layout>
                <v-flex xs10>
                  <v-text-field 
                    required/>
                </v-flex>
                <v-flex xs2>
                  <v-icon 
                    class="iconfont dudu-arrow grey--text lighten-3 mt-2"
                    @click="chooseDept()"/>
                </v-flex>
              </v-layout>
            </v-flex>
          </v-layout>
        </v-flex>
        <v-flex 
          xs12 
          sm12>
          <v-layout>
            <v-flex xs3>
              <v-list-tile-title class="my-3 pl-3">选择身份</v-list-tile-title>
            </v-flex>
            <v-flex xs9>
              <v-layout>
                <v-flex xs10>
                  <v-text-field 
                    required/>
                </v-flex>
                <v-flex xs2>
                  <v-icon 
                    class="iconfont dudu-arrow grey--text lighten-3 mt-2"
                    @click="chooseIdentify()"/>
                </v-flex>
              </v-layout>
            </v-flex>
          </v-layout>
        </v-flex>
      </v-layout>
    </v-list>

    <v-btn 
      block>提交申请</v-btn>



    <v-bottom-sheet v-model="sheet">
      <v-card>
        <v-card-actions>
          <v-btn 
            color="blue darken-1" 
            flat 
            @click.native="sheet = false">取消</v-btn>
          <v-spacer/>
          <v-btn 
            color="blue darken-1" 
            flat 
            @click.native="confirmRegisterOrg()">确定</v-btn>
        </v-card-actions>
        <v-divider/>
        <v-card-text/>
      </v-card>
    </v-bottom-sheet>
  </div>
</template>

<script>
export default {
	name: "RegisterOrg",
	components: {
		
	},
	props: {
	
	},
	data() {
		return {
			org: {},
			orgNumber: 0,
			dept: "",
			identify: "",
			sheet: false
		};
	},
	watch: {
		orgNumber: function(newSearvh, oldSearch) {
			this.debouncedGetAnswer();
		}
	},
	created: function() {
		this.debouncedGetAnswer = window._.debounce(this.getOrg, 1000);
	},
	mounted() {
	},
	methods: {

		getOrg: function() {
			let url = "mine/orgs";
			this.axios.get("/api/" + url,this.orgNumber).then((res)=>{
				// 每一份都还没有对返回数据的结构，mock-data只能存对象数组？
				this.org = res.data[0];
				this.dept = this.org[0].dept;
				this.identify = this.org[0].dept[this.org[0].dept.length/2];
			}).catch((err)=>{
				// console.log(err);
			});
		},
		confirmRegisterOrg: function() {

		},
		chooseDept: function() {
			this.sheet = true;
		},
		chooseIdentify: function() {
			this.sheet = true;
		}
	}
};
</script>

<style scoped>


</style>