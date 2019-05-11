import request from "./requestAxios";

export default {

	/*
	 -----------------------------------	注册顶级机构页面请求集合   ------------------------- start
	 */

	// 注册机构
	joinOrg(names,regions){
		return request({
			url:"/api/org/storeagree",
			method:"post",
			data:{
				name: names,
				region:regions
			}
		});
	},

	// 设置新注册成功的机构为默认机构
	setOrgDefault(orgId,chang_orgId){
		return request({
			url:`/api/my/org/${orgId}/change_default?change_org=${chang_orgId}`,
			method:"get",
		});
	},

	// 获取所有已经加入过的机构
	getAllorgs(){
		return request({
			url:"/api/org/getallorgs",
			method:"get"
		});
	},

	// 检查输入的手机验证码
	checksmsCode(sms_code,tel,identity,openid){
		return request({
			url:"/api/org/check_sms_code",
			method:"post",
			data:{
				sms_code: sms_code,
				phone: tel,
				identity:identity,
				openid: openid,
			}
		});
	},

	// 获取手机验证码
	getCode(openid,mobile_rule,phone){
		return request({
			url:"/api/org/send_sms_code",
			method:"post",
			data:{
				openid: openid,
				mobile_rule: "mobile_required",
				phone:phone
			}
		});
	}

	/*
	 -----------------------------------	注册顶级机构页面请求集合   ------------------------- end
 */

};