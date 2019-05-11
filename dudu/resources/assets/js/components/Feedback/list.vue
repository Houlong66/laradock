<template>
  <div>
    <v-layout row>
      <v-flex 
        xs12 
        sm6 
        offset-sm3>
        <v-card>

          <v-list two-line>
            <template v-for="(item, index) in items">
              <v-list-tile
                :key="index"
                avatar
                style="border-bottom:solid #eee 1px;"
                @click="detail(item.id)"
              >
                <v-list-tile-content>
                  <v-list-tile-title v-html="item.title"/>
                  <v-list-tile-sub-title v-html="item.created_at"/>
                </v-list-tile-content>

                <v-list-tile-action>
                  <v-icon class="iconfont dudu-you1"/>
                </v-list-tile-action>
              </v-list-tile>
            </template>
          </v-list>
        </v-card>
      </v-flex>
    </v-layout>
  </div>
</template>


<script>
export default {
	name: "FeedbackList",
	data() {
		return {
			items: []
		};
	},
	mounted() {
		this.init();
	},
	methods: {
		init() {
			this.axios.get("/api/feedback/list").then((res) => {
				this.items = res.data.data;
			}).catch((err) => {

			});
		},
		detail(id) {
			this.$router.push(`/feedback/detail/${id}`);
		}
	}
};
</script>

<style scoped>
</style>
