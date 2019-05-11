<template>
  <div class="ma-3">
    <v-form
      ref="form"
      v-model="valid"
      lazy-validation
    >
      <v-text-field
        v-model="textFrom.title"
        :rules="titleRules"
        label="文章标题"
        required
      />

      <v-select
        v-model="textFrom.type"
        :items="items"
        :rules="[v => !!v || '请选择文章类型']"
        label="文章类型"
        required
      />

      <span class="subheading">
        请在下面输入框中编辑文章内容
      </span>

      <v-divider class="mb-3"/>

      <!--富文本 start-->
      <q-editor
        :fatherdesc="textFrom.desc"
        @getvalues="getchilrenvalues"
      />
      <!--富文本 end-->
      <hr>

      <v-btn
        class="admin-btn mb-4"
        color="info"
        @click="sumbit"
      >
        提交
      </v-btn>

    </v-form>
  </div>
</template>

<script>
import QEditor from "../components/Commons/Editor";

export default {
	name: "Addtext",
	inject: ["reload"],
	components:{
		QEditor
	},
	data() {
		return {
			valid: true,
			textFrom:{},
			titleRules: [
				v => !!v || "标题不能为空",
			],
			select: null,
			items: [
				{text:"帮助文档",value:"0"},
				{text:"关于文档",value:"1"}
			],
			checkbox: false,
			editorOption: {
			},
			change:false
		};
	},
	computed: {
		editor() {
			return this.$refs.myTextEditor.quill;
		},
	},
	beforeMount(){
		if (this.$route.params.item != void (0)){
			let selectstr = this.$route.params.item.type.toString();
			this.$route.params.item.type = selectstr;
			this.textFrom = this.$route.params.item;
			this.change = true;
		}
	},
	mounted(){
	},
	methods:{
		getchilrenvalues(data){
			this.textFrom.desc = data.html;
		},

		sumbit () {
			if (this.$refs.form.validate()) {

				if (this.textFrom.desc === void(0)){
					this.$toast("请先输入文章内容", "error");
					return false;
				}

				if (this.textFrom.desc.trim().length   < 1 ){
					this.$toast("请先输入文章内容", "error");
					return false;
				}


				if (this.change){
					this.textFrom.is = "change";
				}

				let data = this.textFrom;

				this.axios.post("/api/admin/postArticles",data).then((res) =>{
					if (res.data.errcode == 0) {
						this.$toast(res.data.data, "success");
						this.reload();
						return ;
					}
					this.$toast("服务器忙", "error");
				});
			}
		},
	}
};
</script>

<style scoped>

  .admin-btn{
    width: 126px;
  }

</style>
