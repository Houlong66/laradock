<template>
  <div style="background:#fff;">
    <v-layout
      class="px-3 pt-3 pb-3"
      row 
      wrap>
      <v-flex 
        xs6 
        class="align-self-center"
        @click="edit_dialog = true">
        <h3>{{ org_name }}</h3>
        <span>机构编码：{{ org_code }}</span>
      </v-flex>
      <v-flex 
        xs6 
        class="align-self-center" 
        style="text-align: right">
        <v-btn-toggle>
          <v-btn
            small
            style="background-color: #f5f5f5 !important"
            @click="setUp()">
            密码
          </v-btn>
          <v-btn
            small
            style="background-color: #f5f5f5 !important"
            @click="linkUp()">
            对接
          </v-btn>
          <!--<v-btn-->
          <!--small-->
          <!--@click="migrate()">-->
          <!--迁移-->
          <!--</v-btn>-->
          <!--<v-btn-->
          <!--small-->
          <!--@click="payCost()">-->
          <!--缴费-->
          <!--</v-btn>-->
        </v-btn-toggle>
      </v-flex>
      <v-bottom-sheet
        v-model="edit_dialog"
      >
        <v-card>
          <v-card-text>
            <v-layout 
              column 
              align-center>
              <v-text-field
                v-model="org_name"
                style="width: 100%"
                label="机构名称"
                @blur="scrollTo"
              />
              <v-text-field
                v-model="org_code"
                style="width: 100%"
                label="机构编码"
                @blur="scrollTo"
              />
              <v-flex>
                <v-btn 
                  flat
                  color="primary"
                  @click="cancelEditOrg()">取消</v-btn>
                <v-btn 
                  :disabled="!org_name || !org_code" 
                  flat
                  color="primary"
                  @click="confirmEditOrg()">确定</v-btn>
              </v-flex>
            </v-layout>
          </v-card-text>
        </v-card>
      </v-bottom-sheet>
    </v-layout>
  </div>
</template>


<script>
import { mapMutations } from "vuex";

export default {
	name: "FrameHeader",
	components: {

	},
	props: {
		org: {
			type: Object,
			default: ()=>{}
		}
	},
	inject: ["reload"],
	data: function() {
		return {
			org_name: null,
			org_code: null,
			edit_dialog: false
		};
	},
	mounted() {
		this.org_name = this.org.name;
		this.org_code = this.org.code;
	},
	methods: {
		...mapMutations(["toggleRefreshUser"]),
		setUp: function() {
			let url = "/organizations/set";
			this.$router.push({path: url});
		},
		linkUp: function() {
			let url = "/organizations/link";
			this.$router.push({path: url});
		},
		migrate: function() {
			let url = "/organizations/migrate";
			this.$router.push({path: url});
		},
		payCost: function() {
			let url = "/organizations/pay";
			this.$router.push({path: url});
		},
		confirmEditOrg () {
			this.axios.post(`/api/org/update/${this.org.id}`, {
				name: this.org_name,
				code: this.org_code
			}).then((res) => {
				if (res.data.errcode == 0) {
					this.edit_dialog = false;
					this.$toast("修改成功！");
					// this.reload();
					this.toggleRefreshUser();
				}
			}).catch((Err) => {
				
			});
		},
		cancelEditOrg () {
			this.org_name = this.org.name;
			this.org_code = this.org.code;
			this.edit_dialog = false;
		}
	}
};
</script>

<style scoped>

</style>