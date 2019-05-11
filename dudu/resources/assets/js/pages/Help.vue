<template>
  <div
  >

    <v-layout class="pa-0 ma-0">
      <div
        ref="helpImg"
        class="help-img">
        <ul class="pa-0">
          <li><img
            src="/images/helpimg/1.jpg"
            alt=""></li>
          <li><img
            src="/images/helpimg/2.jpg"
            alt=""></li>
          <li><img
            src="/images/helpimg/3.jpg"
            alt=""></li>
          <li><img
            src="/images/helpimg/4.jpg"
            alt=""></li>
          <li><img
            src="/images/helpimg/5.jpg"
            alt=""></li>
        </ul>
      </div>

    </v-layout>

    <hr>

    <div class="help-title"> 详细帮助文档</div>
    
    <v-layout
      style="overflow: hidden"
      row
      wrap
    >
      <v-flex
        xs12
        lg5
        mb-3
      >
        <v-expansion-panel
          popout
        >
          <v-expansion-panel-content
            v-for="(item,i) in header"
            :key="i"
          >
            <template
              v-slot:header
            >
              <div
                @click="test($event,i)"
              >{{ item.title }}</div>
            </template>
            <v-card
            >
              <v-card-text
                class="testimg"
                @click="handelClick"
                v-html="item.desc"
              />
            </v-card>
          </v-expansion-panel-content>
        </v-expansion-panel>
      </v-flex>
    </v-layout>

    <img
      v-show="imgscale"
      id="opneimg"
      :src="imgsrc"
      :preview="preview"
      preview-text="图片查看"
    >
  </div>
</template>

<script>
export default {
	name: "Help",
	data (){
		return {
			header:null,
			imgdialog:false,
			imgsrc:null,
			imgscale:false,
			preview:"",
			scrolltop:null,
			layoutstyle:null
		};
	},
	watch:{
	},
	mounted() {
		this.initData();
	},

	created(){
	},
	methods:{
		initData(){
			this.axios.get("/api/admin/getAcricles").then((res) => {
				let data =  res.data.data;
				let arr = 	data.filter(n => n.type != 1);
				this.header = arr;
				this.imgsrc= "#";
			});
		},


		test(e,i){
			// 获取元素展开后的高度
			let escrolltop = e.target.parentNode.parentNode;
			if(escrolltop.className == "v-expansion-panel__container"){


				let scroll_height =   escrolltop.getBoundingClientRect().height * (i + 1);

				let heloImg_Div = this.$refs.helpImg.getBoundingClientRect().height;

				// 最外层盒子
				let dom  = document.getElementsByClassName("v-dialog--fullscreen")[0];

				let Scroll = scroll_height + heloImg_Div;

				this.$nextTick(()=>{
					if (dom != void(0)){
						dom.scroll(0,Scroll) ;
						return false;
					}

					let scroll_top =   escrolltop.getBoundingClientRect().height * (i + 1);
					window.scroll(0,scroll_top + heloImg_Div);
				});
			}
		},


		handelClick(e){
			document.querySelectorAll("img[preview]").forEach((n,i)=>{
				n.src = e.target.src;
				n.setAttribute("preview",i);
			});

			if(e.target.nodeName.toLowerCase() === "img"){
				this.imgsrc = e.target.src;
				document.getElementById("opneimg").click();
			}
		},


	}
};
</script>

<style scoped>
.v-expansion-panel--popout .v-expansion-panel__container{
max-width: 100%;
}
.v-expansion-panel li{
  margin-bottom: 0;
}
.help-img{
  width: 100%;
}
.help-img ul li {
  width: 100%;
}
.help-img ul li img{
  width: 100%;
}
.help-title{
background-color: #e2e2e2;
padding: 7px;
margin-top: 2px;
font-size: 1.2rem;
}

</style>
