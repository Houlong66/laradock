<template>
  <div>
    <v-layout 
      row 
      wrap>
      <v-flex xs6>
        <h2>{{ org }}</h2>
        <span>当前状态：{{ bill.status }}</span>
      </v-flex>
      <v-flex xs6>
        <v-btn
          @click="pay()">缴费</v-btn>
        <span>有效期至：{{ bill.valid_time }}</span>
      </v-flex>
    </v-layout>
    <v-subheader center>
      历史账单
    </v-subheader>
    <v-list 
      v-for="(item,index) in bill.bill_list"
      :key="index"
      two-line>
      <v-layout
        :key="index">
        <v-flex 
          xs-4>
          <v-list-tile-title>{{ item.time }}</v-list-tile-title>
        </v-flex>
        <v-flex 
          xs-4>
          <v-list-tile-title>{{ item.cost }}</v-list-tile-title>
        </v-flex> 
        <v-flex 
          xs-4>
          <v-list-tile-title>{{ item.status }}</v-list-tile-title>
        </v-flex>
      </v-layout>
      <v-divider/>
      <v-divider/>
    </v-list>
  </div>
</template>


<script>
export default {
	name: "OrgPay",
	components: {

	},
	data: function() {
		return {
			org: "",
			bill: {},
		};
	},
	mounted() {
		this.org = this.$store.state.org_info.m_org;
		this.bill = this.$store.state.org_info.bill;
	},
	methods: {
		pay: function() {

		}
	}
};
</script>

<style scoped>

</style>