<template>
  <div>
    <v-dialog
      v-model="dialog_flag"
      dark
      transition="dialog-bottom-transition"
    >
      <div class="dialog-inner">
        <v-flex>
          <v-layout 
            justify-end 
            class="px-2">
            <img
              class="share-img"
              src="/images/share.png"
              alt="share">
          </v-layout>
        </v-flex>

        <v-flex class="mt-3">
          <v-layout justify-center>
            <v-btn
              class="mx-0"
              color="white"
              style="color: #000"
              @click="close()">
              知道了
            </v-btn>
          </v-layout>
        </v-flex>
      </div>
    </v-dialog>
  </div>
</template>

<script>
export default {
	name: "ShareTips",
	props: {
		dialog_flag: {
			type: Boolean,
			default: false
		}
	},
	methods:{
		close() {
			this.$emit("update:dialog_flag", false);
		}
	}
};
</script>

<style scoped>
.share-img {
  width:200px;
  height:152px;
}
</style>