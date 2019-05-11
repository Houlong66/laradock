<template>

  <div>

    <div
      v-if="!isLoading"
    >

      <v-card-title
        v-if="notmessage"
        class="red accent-2-grey white--text"
      >
        暂无内容
      </v-card-title>

      <v-card
        v-if="!notmessage"
        class="mx-auto"
        max-width="600"
      >
        <v-card-title
          class="red accent-2-grey white--text"
        >
          审核详情
        </v-card-title>
        <v-card-text class="py-0">

          <v-timeline dense>

            <v-slide-x-reverse-transition
              group
              hide-on-leave
            >
              <v-timeline-item
                v-for="item in items"
                :key="item.id"
                :color="item.color"
                :icon="item.icon"
                small
                fill-dot
              >
                <v-alert
                  :value="true"
                  :color="item.color"
                >
                  <!--展示流转审批内容-->
                  <div
                    v-if="type == 0 || type == 1"
                  >
                    <p class="mb-1">{{ item.updated_at }}</p>
                    <p class="mb-1">申请转发人:{{ item.send_user.name }}</p>
                    <p class="mb-1">备注说明:</p>
                    <div
                      class="details-text white mb-2"
                    >
                      {{ item.note_text }}
                    </div>

                    <p class="mb-1">审批人：{{ item.user.name }}</p>

                    <p class="mb-1">批复内容:</p>
                    <div
                      v-if="item.audit_text"
                      class="details-text white"
                    >
                      {{ item.audit_text }}
                    </div>

                    <div
                      v-else
                      class="details-text white"
                    >
                      无批复内容
                    </div>

                  </div>


                  <!--请示审批-->
                  <div
                    v-if="type == 3"
                  >
                    <p class="mb-1">{{ item.updated_at }}</p>
                    <p class="mb-1">申请转发人:{{ item.work_send.name }}</p>
                    <p class="mb-1">备注说明:</p>
                    <div
                      class="details-text white mb-2"
                      v-html="item.note_text || `无说明`"
                    />

                    <p class="mb-1">审批人：{{ item.user.name }}</p>

                    <p class="mb-1">批复内容:</p>
                    <div
                      class="details-text white"
                      v-html=" item.audit_text || `无内容`"
                    />
                  </div>

                  <!--展示上报内容-->
                  <div
                    v-if="type == 2"
                  >
                    <p class="mb-1">上报人：</p>
                    <div
                      class="details-text white"
                      v-html="item.user.name "
                    />

                    <p class="mb-1">上报时间：</p>
                    <div
                      class="details-text white"
                      v-html="item.report_time "
                    />

                    <p class="mb-1">上报内容：</p>
                    <div
                      v-if="item.report_text"
                      class="details-text white"
                      v-html="item.report_text || `无上报内容` "
                    />


                    <p class="mb-1">批复人：</p>
                    <div
                      class="details-text white"
                      v-html="item.audit_user.name"
                    />

                    <p class="mb-1">批复时间：</p>
                    <div
                      class="details-text white"
                      v-html="item.audit_time || `未批复或正在批复` "
                    />

                    <p class="mb-1">批复内容：</p>
                    <div
                      class="details-text white"
                      v-html="item.audit_text || `无` "
                    />

                  </div>

                </v-alert>

              </v-timeline-item>
            </v-slide-x-reverse-transition>


          </v-timeline>

        </v-card-text>
      </v-card>

    </div>

    <component
      v-if="isLoading"
      :is="cView"
    />

  </div>
</template>

<script>
import Loading from "../Commons/Loading";

export default {
	name: "AllRecord",
	components: {
		Loading,
	},
	data (){
		return{
			items: [],
			notmessage:false,
			isLoading:true,
			cView: "Loading",
			type:this.$route.query.type,
		};
	},
	mounted (){
		this.initData();
	},
	methods: {
		initData(){
			// 查找任务流转审核记录
			// 拿到任务的ID
			let data  = this.$route.query;
			this.axios.post("/api/my/approval/approvaldetails", data).then((res) => {
				let list = res.data.data;

				this.isLoading = !this.isLoading;

				if(list.length === 0){
					this.notmessage = !this.notmessage;
					return;
				}

				// 判断是否是上报的内容
				if(list[0].is === "report"){
					this.isreport = true;
				}

				// 颜色判断
				list.forEach( (v,i) => {

					if (this.type != 3){
						if(v.status === 3){
							list[i].color = "error";
							list[i].icon  = "iconfont dudu-cuowutishi";
						}

						if(v.status === 2 || v.status === 4){
							list[i].color = "success";
							list[i].icon  = "iconfont dudu-duigou";
						}
					} else {

						if(v.status !==  1){
							list[i].color = "error";
							list[i].icon  = "iconfont dudu-cuowutishi";
						}

						if(v.status === 1){
							list[i].color = "success";
							list[i].icon  = "iconfont dudu-duigou";
						}
					}


				});


				this.items = list;

			}).catch((err)=>{

			});
		},
	}
};
</script>

<style scoped>
.details-text{
color:darkolivegreen;
overflow: hidden;
font-size: .9rem;
padding: 4px;
word-break: break-word;
word-break: break-all;
}

</style>
