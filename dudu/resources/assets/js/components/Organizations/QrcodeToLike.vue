<template>
  <div>
    <v-container>
      <v-layout class="pt-5">
        <v-flex 
          style="text-align:center;">
          <p>使用“都督”开展工作，您必须关注“都督”公众号</p>
          <img 
            :src="qrcode" 
            style="margin:0 auto; width:80%">
          <p>长按上方二维码—点击“识别图中二维码”</p>
          <p>随后点击关注公众号</p>
        </v-flex>
      </v-layout>
    </v-container>
  </div>
</template>

<script>
export default{
	name: "QrcodeToLike",
	data () {
		return {
			qrcode: null
		};
	},
	mounted () {
		// this.qrcode = "http://assets.processon.com/chart_image/5be2e455e4b012807691120f.png";
		// this.qrcode = "http://mmbiz.qpic.cn/mmbiz_jpg/FicJ7NmFEraxFBWtLJMWziaCKTtXACrGS0qG6ACR0BgOibMpXQpWicfAib3iaVqnL5Ra7B1Wib4BQC8pxz7jw5GVmFNNg/0";
		this.qrcode = "/images/qrcode.jpg";
	}
};
</script>