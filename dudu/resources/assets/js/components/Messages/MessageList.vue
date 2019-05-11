<template>
  <v-layout column>

    <v-flex v-if="!list">
      <p 
        class="grey--text text--lighten-1 mt-4"
        style="text-align: center">暂无消息</p>
    </v-flex>

    <v-flex 
      v-else 
      xs12>
      <v-list
        v-for="(type, index) in list"
        :key="index"
        three-line
        subheader>

        <div>
          <!--消息名称-->
          <v-subheader
            class="grey lighten-4">
            <v-layout>

              <v-flex xs6>
                <span class="pl-0">{{ type.flag }} ({{ type.list.length }})</span>
              </v-flex>

              <v-flex
                v-if="type.flag == '未读'"
                xs6
                style="text-align: right;"
              >
                <span
                  class="pl-2"
                  style="color: #409EFF;"
                  @click="setMessageStatus"
                >
                  标记所有为已读
                </span>
              </v-flex>

            </v-layout>
          </v-subheader>

          <!--消息块-->
          <message-items
            v-for="(item, index) in type.list"
            :key="index"
            :item="item"/>

        </div>

      </v-list>
    </v-flex>

  </v-layout>
</template>

<script>
import MessageItems from "./MessageItems";

export default {
	name: "MesaageLists",
	components: {
		MessageItems
	},
	props: {
		list: {
			type: Object,
			default: () => { }
		},
	},
	mounted (){
	},

	methods: {
		// 设置为全部已读
		setMessageStatus(){
			this.axios.get("/api/message/allsetread").then((res) => {
				if (res.data.errcode == 0 ){
					this.$router.go(0);
				}
			});
		}
	},
};
</script>

<style scoped>
</style>
