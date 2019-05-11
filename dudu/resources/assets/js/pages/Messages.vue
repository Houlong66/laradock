<template>
  <div>
    <!--主体消息内容-->
    <component
      :is="c_view"
      :list="message_list"
    />

    <!--按钮-->
    <v-layout 
      v-if="c_view !== 'Loading'"
      style="padding-bottom:56px;">
      <v-flex
        v-if="message_list"
        xs12>
        <v-btn
          v-if="!nomore_msg"
          :loading="loading"
          :disabled="loading"
          flat
          block
          @click="checkMore()">
          <span class="grey--text text--darken-1">点击查看更多</span>
        </v-btn>

        <v-btn
          v-else
          disabled
          flat
          block>
          <span class="grey--text text--darken-1">已无更多</span>
        </v-btn>
      </v-flex>
    </v-layout>
  </div>
</template>

<script>
import MessageList from "../components/Messages/MessageList";
import Loading from "../components/Commons/Loading";
import { mapState } from "vuex";

export default {
	name: "Messages",
	components: {
		MessageList,
		Loading
	},
	data () {
		return {
			loading: false,
			nomore_msg: false,
			c_view: "Loading",
			message_list: {},
			work_type: "messages",
			type_text_array: [
				{
					index: "unread",
					text: "未读"
				},
				{
					index: "read",
					text: "已读"
				}
			]
		};
	},
	computed: {
		...mapState(["varMap"])
	},
	mounted: function () {
		this.getMessages();
	},
	methods: {
		checkMore () {

			this.loading = true;
			var limit = 5;

			if (this.message_list.read == undefined) {
				this.nomore_msg = true;
				return;
			}

			var last_index = this.message_list.read.list[this.message_list.read.list.length - 1].id;
			this.axios.get(`/api/message/read/after/${last_index}/limit/${limit}`).then((res)=>{
				if (res.data.data.length === 0) {
					this.nomore_msg = true;

				} else {
					this.message_list.read.list.push(...res.data.data);

					this.loading = false;
				}
			}).catch((err)=>{

			});
		},

		getMessages () {
			this.axios.get("/api/message/unread").then((res) => {
				this.message_list = res.data.data;
				this.axios.get("/api/message/read").then((res) => {
					this.message_list.push(...res.data.data);
					var temp_list = this.listsRank(
						this.message_list,
						this.work_type,
						this.type_text_array,
						-1,
						null
					);
					this.message_list = temp_list.unfinished_list;
					this.c_view = "MessageList";
				}).catch((err)=>{

				});
			}).catch((err)=>{

			});
		}
	}
};
</script>

<style scoped>

</style>