<template>
  <div>
    <v-toolbar 
      color="#f44336" 
      fixed> 
      <v-btn
        v-if="!noHistory"
        icon
        @click="back()">
        <v-icon 
          style="color: #fff" 
          class="iconfont dudu-houtui"/>
      </v-btn>
      <span class="white--text">都督</span>
      <v-spacer/>

      <v-btn 
        icon
        @click="home()">
        <v-icon 
          style="color: #fff" 
          class="iconfont dudu-shouye"/>
      </v-btn>
    </v-toolbar>

    <!--占位元素-->
    <v-toolbar
      flat>
      <v-btn
        icon>
        <v-icon 
          style="color: #fff" 
          class="iconfont dudu-houtui"/>
      </v-btn>
      <span class="white--text">都督</span>
      <v-spacer/>

      <v-btn
        icon>
        <v-icon 
          style="color: #fff" 
          class="iconfont dudu-shouye"/>
      </v-btn>
    </v-toolbar>

  </div>
</template>

<script>
export default {
	name: "TopNav",
	data () {
		return {
			noHistory: false
		};
	},
	mounted() {
		if (history.length === 1) {
			this.noHistory = true;
		}
	},
	methods: {
		back () {
			this.$router.go(-1);
		},
		home () {
			this.$router.push("/");
		}
	}
};
</script>