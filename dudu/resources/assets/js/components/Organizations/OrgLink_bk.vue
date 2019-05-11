<template>
  <div>
    <v-list two-line>
      <v-layout wrap>
        <v-flex 
          xs12>
          <v-layout>
            <v-flex xs3>
              <v-list-tile-title class="my-3 pl-3">对接机构编码</v-list-tile-title>
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
          xs12>
          <v-layout>
            <v-flex xs3>
              <v-list-tile-title class="my-3 pl-3">对接部门/单位选择</v-list-tile-title>
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
          xs12>
          <v-layout>
            <v-flex xs12>
              <v-list-tile-title class="my-3 pl-3">对接申请说明</v-list-tile-title>
            </v-flex>
            <v-flex xs12>
              <v-textarea
                solo/>
            </v-flex>
          </v-layout>
        </v-flex>
      </v-layout>
    </v-list>

    <v-btn 
      block>确认提交申请</v-btn>



    <v-bottom-sheet v-model="sheet">
      <v-card/>
    </v-bottom-sheet>
  </div>
</template>


<script>
export default {
	name: "OrgLink",
	components: {

	},
	data() {
		return {
			sheet: false
		};
	},
	methods: {
		chooseDept: function() {
			this.sheet = true;
		}
	}
};
</script>

<style scoped>

</style>