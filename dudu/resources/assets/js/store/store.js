import Vue from "vue";
import Vuex from "vuex";
import * as auth from "../auth";
import axios from "axios";
// import { calendarFormat } from "moment";

Vue.use(Vuex);

// state list
const state = {
	search_value: "",
	count: 0,
	finished_list: {},
	message_list: {},
	user_info: {},
	org_info: {},
	selected_org: {
		pivot: {
			role_id: 0
		}
	},
	// 当前选中机构下用户的群组和部门
	selected_org_user_info: {}

};

const getters = {
	finished_list: state => state.finished_list,
	message_list: state => state.message_list,
	user_info: state => state.user_info,
	org_info: state => state.org_info,
	selected_org: state => state.selected_org,

	// 新手引导的判断
	guide_info: state => {
		if (state.selected_org) {
			let org = state.selected_org;
			let org_user_msg = state.selected_org_user_info;
			let storage = window.localStorage;

			if (org.pivot.role_id === 1) {
				// 机构超管
				if(state.temp_guide_info){
					return  state.temp_guide_info;
				}

				if (org.depts.length === 1 && !storage.create_dept_finished) {
					// 新注册机构 >>> 进行第一步引导
					return "create_dept";
				}

				// if (org.depts.length === 2 && org.depts[0].users.length === 0 && org.groups.length === 0 && org.depts[1].users.length === 0) {
				// 	// 新注册机构 >>> 进行第二步引导   有部门无人.无群组
				// 	return "operation_members";
				// }

				if (org.depts.length > 1 && !storage.create_dept_finished) {
					return "create_dept_continue";
				}

				// 返回到主页之后
				// todo: 后面需要修改！
				// if (org.groups.length === 0) {
				// 	let storage = window.localStorage;
				//
				//
				// 	if(org.depts.length > 0 ){
				//
				// 		if (org.depts[0].users.length === 1) {
				//
				// 			if(org.depts[1]){
				// 				if( org.depts[1].users.length === 1){
				// 					if (!storage.isOneGuide) {
				// 						return "create_group";
				// 					}
				// 				}
				// 			}
				//
				// 			if (!storage.isOneGuide) {
				// 				return "create_group";
				// 			}
				// 		}
				//
				// 		if (org.depts[0].users.length === 0){
				// 			if(org.depts[1]){
				// 				if( org.depts[1].users.length === 1){
				// 					if (!storage.isOneGuide) {
				// 						return "create_group";
				// 					}
				// 				}
				// 			}
				//
				// 			if (!storage.isOneGuide) {
				// 				return "create_group";
				// 			}
				// 		}
				// 	}
				// }
				if(storage.create_dept_finished && !storage.isOneGuide){
					return "invite_users";
				}

			} else {

				// 非机构超管
				let storage = window.localStorage;
				if (org_user_msg.groups && org_user_msg.groups.length === 0) {
					if ((org.role.id === 3 || org.role.id === 2) && !storage.isOneGuide) {
						// 是 任务管理员 或者 系统管理员 可以创建群组
						return "insert_groups";
					} else {
						// 平民只能申请,申请只能在这个机构已经有群组的时候进行
						if (!storage.addGroups && !storage.isOneGuide) {
							if (org.groups.length > 0) {
								return "add_groups";
							}
						}
					}
				}
			}
		} else {
			return false;
		}
	}
};

const actions = {

	initUser({commit}) {
		// 获取用户数据
		return new Promise((resolve, reject) => {
			axios.get("/api/my/user").then((res) => {
				// 数据结构构建
				let user_info = res.data.data;

				user_info = auth.smartData(user_info, "orgs");
				// 部门进行处理机构本部改为机构名称
				// user_info.depts.forEach((v, i) => {
				// 	if(v.name === "机构本部") v.name = user_info.smart_orgs[v.id].name;
				// });

				user_info = auth.smartData(user_info, "depts");
				user_info = auth.smartData(user_info, "groups");

				// 更新 user_info
				commit("setUserInfo", user_info);

				// 获取用户默认选中的机构
				let orgs = res.data.data.orgs;
				let selected_org = null;
				if (orgs.length !== 0) {
					orgs.forEach((value, index) => {
						if (value.pivot.is_default === 1) {
							selected_org = value;
							// 机构本部改为机构名
							// selected_org.depts[0].name = value.name;
						}
					});
				}

				// 数据结构构建
				if (selected_org) {
					selected_org = auth.smartData(selected_org, "depts");
					selected_org = auth.smartData(selected_org, "groups");
				}
				// 更新 selected_org
				commit("setSelectedOrg", selected_org);

				// 构造当前选中机构用户所在群组跟部门

				let org_user_list = {"groups": [], "depts": []};

				if (selected_org && user_info) {
					// 群组
					selected_org.groups.forEach((item, index) => {
						if (user_info.smart_groups[item.id]) {
							org_user_list.groups.push(user_info.smart_groups[item.id]);
						}
					});

					// 部门
					selected_org.depts.forEach((item, index) => {
						if (user_info.smart_depts[item.id]) {
							org_user_list.depts.push(user_info.smart_depts[item.id]);
						}
					});

				}

				commit("setSelectedOrgUserinfo", org_user_list);

				// 返回用户更新结果
				resolve(true);
			});
		});
	}
};

const mutations = {
	setSearchValue(state, nValue) {
		state.search_value = nValue;
	},
	setSelectedOrgUserinfo(state, list) {
		state.selected_org_user_info = list;
	},
	setFinishedList(state, list) {
		state.finished_list = list;
	},
	setMessageList(state, list) {
		state.message_list = list;
	},
	setUserInfo(state, info) {
		state.user_info = info;
	},
	setOrgInfo(state, info) {
		state.org_info = info;
	},
	setSelectedOrg(state, org) {
		state.selected_org = org;
	},
	getControlledOrgs(state, obj) {
		auth.getControlledOrgs(state, obj);
	},
	getControlledGroupsByOrg(state, obj) {
		auth.getControlledGroupsByOrg(state, obj);
	},
	getControlledOrgsSchedule(state, obj) {
		auth.getControlledOrgsSchedule(state, obj);
	},
	getGroupsByOrg(state, obj) {
		auth.getGroupsByOrg(state, obj);
	},
	haveOrgAccess(state, obj) {
		auth.haveOrgAccess(state, obj);
	},
	haveTaskAccess(state, obj) {
		auth.haveTaskAccess(state, obj);
	},
	haveGroupAccess() {
		auth.haveGroupAccess(state);
	},
	setTempGuideInfo(state,info){
		Vue.set(state,"temp_guide_info",info);
	}
};


export default new Vuex.Store({
	state,
	mutations,
	actions,
	getters,

});
