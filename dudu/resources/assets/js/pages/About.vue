<template>
  <div>
    <v-layout
      class="pa-3"
    >
      <v-flex
        v-if="!showdesc"
      >
        <h1 class="title text pb-3 mb-4">{{ desc.title }}</h1>

        <v-flex
          @click="handelClick"
          v-html="desc.desc"
        />

      </v-flex>

      <v-flex
        v-if="showdesc"
      >
        暂无介绍文档
      </v-flex>
    </v-layout>

    <img
      v-show="imgscale"
      id="opneimg"
      :src="imgsrc"
      preview="2"
      preview-text="缩略图与大图模式"
    >

  </div>
</template>

<script>
export default {
	name: "About",
	props: {
		open: {
			type: Boolean,
			default: false,
		}
	},
	data (){
		return {
			desc:null,
			showdesc:true,
			imgsrc:null,
			imgscale:false
		};
	},
	mounted(){
		this.initData();
	},
	methods:{
		initData(){
			this.axios.get("/api/admin/getAcricles").then((res) => {
				let data = res.data.data;
				let arr = 	data.filter(n => n.type !== 0);
				if(arr.length !== 0){
					this.desc = arr[arr.length-1];
					this.showdesc = false;
				}
				this.imgsrc = "#";
			});
		},
		handelClick(e){

			document.querySelectorAll("img[preview]").forEach((n,i)=>{
				n.src = e.target.src;
			});

			if(e.target.nodeName.toLowerCase() === "img"){
				this.imgsrc = e.target.src;
				document.getElementById("opneimg").click();
			}

		}

	}
};
</script>

<style scoped>

  .title{
    border-bottom:solid #ccc 1px;
  }
</style>
