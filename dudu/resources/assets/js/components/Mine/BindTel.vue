<template>
  <div>
    <v-layout>
      <v-flex xs-3>
        <v-text-field>手机号</v-text-field>
      </v-flex>
      <v-flex xs-9>
        <v-text-field
          class="mx-3"
          flat
          solo-inverted
          @blur="scrollTo"
        />
      </v-flex>
    </v-layout>
    <v-layout>
      <v-flex xs-3>
        <v-text-field>验证码</v-text-field>
      </v-flex>
      <v-flex xs-7>
        <v-text-field
          class="mx-3"
          flat
          solo-inverted
          @blur="scrollTo"
        />
      </v-flex>
      <v-flex xs-2>
        <v-btn 
          flat 
          @click="getVerCode()">
          获取
        </v-btn>
      </v-flex>
    </v-layout>
    <v-btn 
      block 
      @click="bindTel()">
      绑定手机
    </v-btn>
  </div>
</template>

<script>
export default {
	name: "BindTel",
	components: {
	
	},
	props: {

	
	},
	data() {
		return {
		};
	},
	methods: {
		getVerCode: function() {
			// 
		},
		bindTel: function() {

		}
	}
};
</script>

<style scoped>

</style>