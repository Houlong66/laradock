<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="pagelist"
      :search="search"
      hide-actions
      class="elevation-1 mt-3"
    >
      <template v-slot:items="props">
        <td class="text-xs-center"> {{ props.item.id }}</td>
        <td class="text-xs-center">{{ props.item.title }}</td>
        <!--<td class="text-xs-center">{{ props.item.author[0].username }}</td>-->
        <td class="text-xs-center">{{ props.item.created_at }}</td>
        <td class="justify-center layout px-0">
          <v-icon
            small
            class="mr-2"
            @click="editArticle(props.item)"
          >
            edit
          </v-icon>
          <v-icon
            small
            @click="deleteArticleDialog(props.item)"
          >
            delete
          </v-icon>
        </td>
      </template>
    </v-data-table>


    <div
      v-if="pagelength > 0"
      style="margin-top: 10px"
      class="text-xs-center">
      <v-pagination
        v-model="page"
        :length="pagelength"
      />
    </div>

    <!--提示框-->
    <Dialogs
      :dialog.sync="tips_show"
      :title="tips_titile"
      :text="tips_text"
      :fn="tips_fn"
      :agreed="tips_agreed"
      :types="tips_type"

    />


  </div>
</template>

<script>
import Dialogs from  "../components/Commons/Dialogs";
export default {
	name: "Textlist",
	inject: ["reload"],
	components: {
		Dialogs
	},
	data () {
		return {
			// 提示框参数
			tips_show:false,
			tips_titile:"",
			tips_text:"",
			tips_fn:null,
			tips_type:null,
			tips_agreed:null,
			// -------------
			search: "",
			pagination: {},
			selected: [],
			headers: [
				{
					text: "编号",
					align: "center",
					sortable: false,
					value: "id",
					width: "120"
				},
				{
					text: "标题",
					value: "title" ,
					align: "center",
				},
				// {
				// 	text: "作者",
				// 	value: "author",
				// 	align: "center",
				// },
				{
					text: "创建时间",
					value: "create_at" ,
					align: "center",

				},
				{
					text: "操作",
					value: "iron" ,
					width: "200",
					align: "center",
				}
			],
			page: 1,
			pagelength:1,
			desserts:[],
			pagelist:[],
		};
	},
	computed: {
	},
	watch:{
		page(n){
			this.pagelist = this.desserts.slice((n-1)*10,n*10);
		}
	},
	mounted() {
		this.initData();
	},
	methods:{
		initData(){
			this.axios.get("/api/admin/getAcricles").then((res) => {
				this.desserts = res.data.data;
				this.pagelength = Math.ceil(this.desserts.length / 10);
				this.pagelist = this.desserts.slice(0,10);
			});
		},

		editArticle(item){
			this.$router.push({name:"addlist",params:{item}});
		},

		// 删除文章弹框
		deleteArticleDialog(item){
			this.tips_text = "确认删除文章?";
			this.tips_titile = "删除文章";
			this.tips_agreed = "确定删除";
			this.tips_type = item;
			this.tips_show = !this.tips_show;
			this.tips_fn = this.deleteArticle;
		},
		//  删除文章
		deleteArticle(item){
			item.is = "delete";
			this.axios.post("/api/admin/postArticles",item).then((res) =>{
				if (res.data.errcode == 0) {
					this.$toast(res.data.data, "success");
					this.reload();
					return ;
				}
				this.$toast("服务器忙！", "error");
				this.reload();
			});
		}
	}
};
</script>

<style scoped>

</style>
