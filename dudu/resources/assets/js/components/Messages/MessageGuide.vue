<template>
  <div v-if="iswhu">
    <!--蒙版以及引导-->
    <div class="mask"/>
    <!--镂空效果-->
    <div
      class="test">
      <div
        :style="[css[0],show_style]"
        class="mask-show"
        @click="getfn"
      />
    </div>


    <!--文本信息-->
    <div
      :style="css[2]"
      class="text"
    >{{ text }}
    </div>



    <div
      :style="css[1]"
      class="arrow_big"
    >
      <div
        :style="css[2]"
        class="arrow"
      />
    </div>
  </div>
</template>

<script>
import { mapGetters,mapMutations} from "vuex";

export default {
	name: "MessageGuide",
	props: {
		fn:{
			type:Function,
			default:null
		},
		text:{
			type:String,
			default:null
		},
		iswhu:{
			type:Boolean,
			default:false
		},
		css:{
			type:Array,
			default: () => []
		}
	},
	data() {
		return{
			styles:{
				"top":"99px"
			},
			show_style:{
				"box-shadow":null,
				"-moz-box-shadow":null,
				"-webkit-box-shadow":null
			},
		};
	},
	computed: {
		...mapGetters(["guide_info"]),
	},
	mounted () {
		this.initAllData();
	},
	updated () {

	},
	methods: {
		...mapMutations(["setTempGuideInfo"]),
		initAllData(){

			this.setShadowStyle();
		},
		getfn(){
			this.$props.fn();
		},
		setShadowStyle(){
			let orderHight = document.body.clientHeight ;
			this.show_style["box-shadow"] =" 0 0 0 "+ orderHight+"px" +" rgba(0,0,0,.6)";
			this.show_style["-moz-box-shadow"] =" 0 0 0 "+ orderHight+"px" +" rgba(0,0,0,.6)";
			this.show_style["-webkit-box-shadow"] =" 0 0 0 "+ orderHight+"px" +" rgba(0,0,0,.6)";
		}

	}
};
</script>

<style scoped>

    /*遮照层*/
    .mask{
        width: 100%;
        height: 100%;
        z-index: 101;
        position: fixed;
        top: 0;
        opacity: 0;
    }
    .mask-show {
        position: fixed;
        width: 60px;
        height: 60px;
        filter: blur(1px);
        z-index: 102;
        border-radius: 60px;
        -webkit-border-radius: 60px;
        -moz-border-radius: 60px;
        -webkit-appearance: none;
         /*box-shadow: 0 0 0 200px rgba(0,0,0,.6);*/
        /*-moz-box-shadow:0  0 0 200px rgba(0,0,0,.6);*/
        /*-webkit-box-shadow:0  0 0 200px rgba(0,0,0,.6);*/
      }
    /*介绍文本*/
    .text{
        position: fixed;
        z-index: 102;
        width: 270px;
        -webkit-box-align: Center;
        -ms-flex-align: Center;
        align-items: Center;
        margin: 0 auto;
        padding:10px;
        top: 43%;
        left: 0;
        right: 0;
        color: #fff;
        font-size: 1.3rem;
        border: 1px solid #fff;
        text-align: center;
    }

    /*箭头 盒子*/
    .arrow_big{
        position: fixed;
        z-index: 103;
    }

    /* 箭头 */
    .arrow {
        display: block;
        width: 20px;
        height: 20px;
        bottom: 25px;
        border: 3px solid transparent;
        border-top: 3px solid #fff ;
        border-right: 3px solid #fff ;
        z-index: 99;
        opacity: .8;

        /*  尝试开启硬件加速  */
        transform: rotate(45deg) translateZ(0) ;
        -webkit-transform:rotate(45deg) translateZ(0);
        -moz-transform:rotate(45deg) translateZ(0);
        -ms-transform: rotate(45deg) translateZ(0);
        -o-transform: rotate(45deg)  translateZ(0);
        /*  动画  */
        -webkit-animation: arrow 2s infinite ease-in-out;
        -moz-animation: arrow 2s infinite ease-in-out;
        -ms-animation: arrow 2s infinite ease-in-out;
        -o-animation: arrow 2s infinite ease-in-out;
        animation: arrow 2s infinite ease-in-out;


    }
    /* Safari 和 Chrome */
    @-webkit-keyframes arrow {
      0% {
        opacity:0;
      }
      100% {
        opacity:1;
        -webkit-transform : translate(180px) rotate(45deg) ;
      }
    }
    /* Firefox */
    @-moz-keyframes arrow{
      0% {
        opacity:0;
      }
      100% {
        opacity:1;
        -moz-transform : translate(180px) rotate(45deg);
      }
    }
    /* IE  */
    @-ms-keyframes arrow{
      0% {
        opacity:0;
      }
      100% {
        opacity:1;
        -ms-transform : translate(180px) rotate(45deg);
      }
    }
    /* Opera */
    @-o-keyframes arrow{
      0% {
        opacity:0;

      }
      100% {
        opacity:1;

        -o-transform : translate(180px) rotate(45deg) ;
      }
    }

    @keyframes arrow{
        0% {
          opacity:0;

        }
        100% {
          opacity:1;
          transform : translate(180px) rotate(45deg) ;
        }
    }


</style>