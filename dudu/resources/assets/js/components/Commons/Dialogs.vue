<template>
  <div>
    <v-layout
      row
      justify-center>

      <!-- 普通提示弹窗 -->
      <v-dialog
        v-model="isdialog"
        max-width="290"
      >
        <v-card>
          <v-card-title class="subheading">{{ title }}</v-card-title>
          <v-card-text>
            {{ text }}
          </v-card-text>

          <v-card-actions>
            <v-spacer/>

            <v-btn
              v-if="isAgreed"
              color="green darken-1"
              flat="flat"
              @click="getfn"
            >
              {{ isAgreed }}
            </v-btn>

            <v-btn
              v-if="close"
              color="green darken-1"
              flat="flat"
              @click="checkoutDialog"
            >
              {{ close }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>


      <!--底部弹窗-->
      <v-dialog
        v-model="show"
        fullscreen
        hide-overlay
        transition="dialog-bottom-transition"
        max-width="290"
      >
        <v-card flat>

          <!--标题和关闭-->
          <v-toolbar
            dark
            color="red">
            <v-btn
              icon
              dark
              @click="checkoutDialog">
              <v-icon
                small
                class="iconfont dudu-guanbi1"/>
            </v-btn>
            <v-toolbar-title class="subheading">{{ title }}</v-toolbar-title>
            <v-spacer/>
          </v-toolbar>

          <v-card flat>
            <slot name="dialogs-title"/>
            <slot name="dialogs-content"/>
          </v-card>

        </v-card>
      </v-dialog>

    </v-layout>

  </div>
</template>

<script>
export default {
	name: "Dialogs",
	props: {
		title:{
			type:String,
			default:null
		},
		text:{
			type:String,
			default:null
		},
		fn:{
			type:Function,
			default:null
		},
		closefn:{
			type:Function,
			default:null
		},
		types:{
			type:[String,Number,Object],
			default:null
		},
		dialog:{
			type:Boolean,
			default:false
		},
		show:{
			type:Boolean,
			default:false
		},
		agreed:{
			type:String,
			default:()=>"确定"
		},
		close:{
			type:String,
			default:()=>"取消"
		}
	},
	data (){
		return {
			isdialog : this.$props.dialog,
			ishow : this.$props.show,
			isAgreed : "确认"
		};
	},
	watch:{
		isdialog(n){
			this.$emit("update:dialog",n);
		},
		dialog(n){
			this.isdialog = this.$props.dialog;
		},
		agreed(n,o){
			if (n){
				this.isAgreed = n;
			}
		}
	},
	update (){

	},


	methods:{
		getfn(){
			this.isdialog = false;
			this.$emit("update:dialog",this.isdialog);
			if (this.types != null){
				this.$props.fn(this.types);
			} else{
				this.$props.fn();
			}
		},
		checkoutDialog(){
			if (this.$props.closefn == null){
				this.isdialog = false;
				this.ishow = false;
				this.$emit("update:dialog",this.isdialog);
				this.$emit("update:show",this.ishow);
				return false;
			}
			this.$props.closefn();
		}
	}

};
</script>

<style scoped>

</style>
