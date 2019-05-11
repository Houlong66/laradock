/**
 * @author shaoDong
 * @email scut_sd@163.com
 * @create date 2018-12-17 10:38:54
 * @modify date 2018-12-17 10:38:54
 * @desc 权限管理
 */

const isInArr = (val, arr) => {
	for (var i = 0; i < arr.length; i++) {
		if (val === arr[i]) {
			return true;
		}
	}
	return false;
};

// 发布任务和通知时可选机构
const getControlledOrgs = (state, obj) => {
	obj.res.length = 0;
	// state.user_info.orgs.forEach((value) => {
	// 	let nowGroups = value.groups;
	// 	for (var i = 0, len = nowGroups.length; i < len; i++) {
	// 		if (isInArr(nowGroups[i].pivot.role_id, obj.arr)) {
	// 			obj.res.push(value);
	// 			// Bug fix: 防止重复添加同一个org
	// 			break;
	// 		}
	// 	}
	// });
	// 用户所有加入的群组
	let org_id_list = [];
	state.user_info.groups.forEach((v, i) => {
		// 判断是否为工作群组
		if (v.type === 0) {
			// 判断是否有发送任务或工作的权限
			if (isInArr(v.pivot.role_id, obj.arr)) {
				// 将该群对应的机构对象返回
				if(org_id_list.indexOf(v.org_id) === -1){ // 防止重复插入同一个机构
					obj.res.push(state.user_info.smart_orgs[v.org_id]);
					org_id_list.push(v.org_id);
				}
			}
		}
	});
};

// 发布通知时对应机构的可选群组
const getControlledGroupsByOrg = (state, obj) => {
	obj.res.length = 0;
	// state.user_info.orgs.forEach((value) => {
	// 	if (value.id === obj.id) {
	// 		value.groups.forEach((val) => {
	// 			// 仅工作群组
	// 			if (val.type === 0) {
	// 				if (isInArr(val.pivot.role_id, obj.arr)) {
	// 					obj.res.push(val);
	// 				}
	// 			}
	// 		});
	// 	}
	// });
	// 用户所有加入的群组
	state.user_info.groups.forEach((v, i) => {
		// 群组所属机构是当前创建工作时选定的机构
		if (v.org_id === obj.id) {
			// 判断是否为工作群组
			if (v.type === 0) {
				// 判断是否有发送任务或工作的权限
				if (isInArr(v.pivot.role_id, obj.arr)) {
					// 将该群对应的机构对象返回
					obj.res.push(v);
				}
			}
		}
	});
};

// 发布日程时可选机构
const getControlledOrgsSchedule = (state, obj) => {
	obj.res.length = 0;
	state.user_info.orgs.forEach((val) => {
		if (isInArr(val.pivot.role_id, obj.arr)) {
			obj.res.push(val);
		}
	});
};

// 根据org id 拿到所有的group
const getGroupsByOrg = (state, obj) => {
	obj.res.length = 0;
	state.user_info.orgs.forEach((value) => {
		if (value.id === obj.id) {
			value.groups.forEach((val) => {
				obj.res.push(val);
			});
		}
	});
};

// 针对某一机构的设置操作权限
// 判断用户是否有工作群组创建权限
// obj 包含 arr 和 permission，arr 中为符合权限的id
const haveOrgAccess = (state, obj) => {
	if (state.selected_org) {
		if (isInArr(state.selected_org.pivot.role_id, obj.arr)) {
			obj.permission = true;
		}
	}
};

// 判断当前机构下是否存在工作群组
const haveGroupAccess = (state, group_id, obj) => {
	if (state.user_info.smart_groups[group_id]) {
		if (isInArr(state.user_info.smart_groups[group_id].pivot.role_id, obj.arr)) {
			obj.permission = true;
		}
	}
};

const haveTaskAccess = (state, obj) => {
	obj.permission = isInArr(state.user_info.id, obj.arr);
};

// 构建 smart 数据结构
function smartData(data, item) {
	let smart_data = {};
	data[item].forEach((v, i) => {
		smart_data[v.id] = v;
	});
	data[`smart_${item}`] = smart_data;
	return data;
}

export {
	getControlledOrgs,
	getControlledGroupsByOrg,
	getControlledOrgsSchedule,
	getGroupsByOrg,
	haveOrgAccess,
	haveTaskAccess,
	haveGroupAccess,
	smartData
};