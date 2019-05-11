import wx from "weixin-js-sdk";
import axios from "axios";

export default {
// list分类处理函数
// 将工作项分类成已完成和未完成两部分，
// 其中已完成部分又分为：我发送的和我签收的两部分
// 未完成部分再分为：我发送的和各种状态（任务：未签收，未上报，已上报，已办结）（通知：未读，已读）（请示：未处理，已批复）
// *若某状态分类下，没有数据项，则不分出此分类
	install(Vue) {
		Vue.prototype.listsRank = function (list, work_type, type_array, finished_status, selected_asks_type) {
			let return_data = {};
			let unfinished_list = {}; // 未完成项
			let finished_list = {}; // 已完成项

			// 根据每一项的status属性，升序排序
			list = orderList(list);

			// 根据asks的a_type筛选
			if (work_type == "ask" && selected_asks_type != "全部类型") {
				list = filtrateByAsksType(list, selected_asks_type);
			}

			// 初始化已完成的总数量
			for (let i in list) {
				// 已完成的
				if (list[i].status == finished_status) {
					// 我发出的
					if (list[i].self_send == 1) {
						// 判断数组中是否有.self_send属性
						if (finished_list.self_send == undefined) {
							// 初始化self_send属性，创建此分类的空数组
							finished_list.self_send = {
								flag: "我发出的",
								list: [],
							};
						}
						// 加入"我发出的"分类，已完成数组
						finished_list.self_send.list.push(list[i]);
					} else { // 我签收的
						// 判断数组中是否有received属性
						if (finished_list.received == undefined) {
							// 初始化received属性，创建此分类的空数组
							finished_list.received = {
								flag: "我接收的",
								list: [],
							};
						}
						// 加入"我签收的"分类，已完成数组
						finished_list.received.list.push(list[i]);
					}
				} else { // 未完成的
					if (list[i].self_send == 1) {
						// 判断数组中是否有.self_send属性
						if (unfinished_list.self_send == undefined) {
							// 初始化self_send属性，创建此分类的空数组
							unfinished_list.self_send = {
								flag: "我发出的",
								list: [],
							};
						}
						// 加入"我发出的"分类，未完成数组
						unfinished_list.self_send.list.push(list[i]);
					} else { // 我签收的
						// 获取当前项状态对应的分类索引和分类文案
						let status_index = type_array[list[i].status]["index"];
						let status_text = type_array[list[i].status]["text"];
						// 判断数组中是否有此分类属性
						if (unfinished_list[status_index] == undefined) {
							// 初始化"status_index"分类属性，创建此分类的空数组
							unfinished_list[status_index] = {
								flag: status_text,
								list: [],
							};
						}
						// 加入"status_index状态"分类，未完成数组
						unfinished_list[status_index].list.push(list[i]);
					}
				}
			}

			return_data = {
				"finished_list": (Object.keys(finished_list).length != 0) ? finished_list : null,
				"unfinished_list": (Object.keys(unfinished_list).length != 0) ? unfinished_list : null,
			};

			return return_data;
		};

		// 深度克隆对象
		Vue.prototype.deepClone = function (obj) {
			return deepCloneShadow(obj);
		};


		// 获取附件id字符串
		// uploadedFiles, 当前新上传的附件id数组 Array
		Vue.prototype.getAttachmentIdStr = function (uploadedFiles) {

			let attachment = "";

			for (let i in uploadedFiles) {
				if (parseInt(i) === uploadedFiles.length - 1) {
					attachment += uploadedFiles[i];
				} else {
					attachment += uploadedFiles[i] + ",";
				}
			}
			return attachment;
		};

		// 获取某个文字在字符串中出现的所有位置
		Vue.prototype.getStrIndex = function (str,subStr) {
			var indexs=[];
			var string= str;
			let trues = true;
			while(trues){
				var index=string.lastIndexOf(subStr);
				if(index!=-1){
					string = string.substr(0,index)+string.substr(index+subStr.length,string.length);
					indexs.push(index);
				}else{
					break;
				}
			}
			return indexs;
		};

		// 拼接已上传附件id字符串
		// attachment, 已有的字符串 String
		// attach_pic, 已上传的图片类型附件数组 Array
		// attach_file, 已上传的非图片类附件数组 Array
		Vue.prototype.plusAttachmentIdStr = function (attachment, attach_pic, attach_file) {
			// 合并已上传的附件id数组
			if (attach_pic.length !== 0) {
				for (let i in attach_pic) {
					attachment += "," + attach_pic[i].file_id;
				}
			}
			if (attach_file.length !== 0) {
				for (let i in this.attach_file) {
					attachment += "," + attach_file[i].file_id;
				}
			}
			return attachment;
		};

		// 构建已上传附件数组
		// attachment, 当前新上传的附件id数组 Array
		// item_id, 工作子项id
		Vue.prototype.structureAttachment = function (attachments, item_id = 0) {
			let build_attachments;
			let attach_pic = [];
			let attach_file = [];
			let report_attach_pic = [];
			let report_attach_file = [];

			// 构建附件变量
			for (let i in attachments) {
				let type = attachments[i].original_name.split(".");
				type = type[type.length - 1];
				let data = {
					file_id: attachments[i].id,
					file_name: attachments[i].original_name,
					file_path: attachments[i].total_path,
				};
				if (["jpg", "jpeg", "png", "gif"].indexOf(type) !== -1) {

					if (attachments[i].works_item_id === 0) {
						attach_pic.push(data);
					} else if (attachments[i].works_item_id === item_id) {
						report_attach_pic.push(data);
					}

				} else {
					if (attachments[i].works_item_id === 0) {
						attach_file.push(data);
					} else if (attachments[i].works_item_id === item_id) {
						report_attach_file.push(data);
					}
				}
			}

			build_attachments = {
				attach_pic: attach_pic,
				attach_file: attach_file,
				report_attach_pic: report_attach_pic,
				report_attach_file: report_attach_file,
			};

			return build_attachments;
		};


		Vue.prototype.formatDate = function (date, fmt) {
			if (/(y+)/.test(fmt)) {
				fmt = fmt.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length));
			}
			let o = {
				"M+": date.getMonth() + 1,
				"d+": date.getDate(),
				"h+": date.getHours(),
				"m+": date.getMinutes(),
				"s+": date.getSeconds()
			};
			for (let k in o) {
				if (new RegExp(`(${k})`).test(fmt)) {
					let str = o[k] + "";
					fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? str : padLeftZero(str));
				}
			}
			return fmt;
		};

		//处理被微信分享污染的url
		Vue.prototype.pureUrl = function (url) {
			// 污染字符串数组
			let pollutant = [
				"?from=singlemessage", // 微信分享给朋友
			];

			for (let i in pollutant) {
				if (url.indexOf(pollutant[i]) !== -1) {
					const url_str = url.split(pollutant[i]);
					window.location.href = url_str[0] + url_str[1];
				}
			}

			return "pure";
		};

		//判断是否是微信浏览器的函数
		Vue.prototype.isWxBrowser = function () {
			// window.navigator.userAgent属性包含了浏览器类型、版本、操作系统类型、浏览器引擎类型等信息，
			let ua = navigator.userAgent;
			//通过正则表达式匹配ua中是否含有MicroMessenger字符串
			let isWx = ua.toLowerCase().match(/MicroMessenger/i) == "micromessenger";
			if (isWx) {
				return true;
			}
			return false;
		};

		// 判断手机浏览器操作系统是否为 android
		Vue.prototype.isAndroid = () => {
			let ua = navigator.userAgent;
			let isAndroid = ua.indexOf("Android") > -1 || ua.indexOf("Adr") > -1; //android终端
			if (isAndroid) {
				return true;
			}
			return false;
		};

		// 判断手机浏览器操作系统是否为 ios
		Vue.prototype.isIos = () => {
			let ua = navigator.userAgent;
			let isIos = !!ua.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
			if (isIos) {
				return true;
			}
			return false;
		};

		// 判断是否是手机浏览器
		Vue.prototype.isMobileBrowser = () => {
			let ua = navigator.userAgent;
			let ipad = ua.match(/(iPad).*OS\s([\d_]+)/),
				isIphone = !ipad && ua.match(/(iPhone\sOS)\s([\d_]+)/),
				isAndroid = ua.match(/(Android)\s+([\d.]+)/),
				isMobile = isIphone || isAndroid;
			if (isMobile) {
				return true;
			} else {
				return false;
			}
		};

		// 正则字符串区间 或 非正则 截取
		Vue.prototype.strMatch = (str, star, end) => {
			//非正则
			// str.substring(str.indexOf(''+ star +'')+1,str.lastIndexOf(''+ end +''));

			let regs = new RegExp("(?<=[" + star + "]).*(?=[" + end + "])");
			let req = str.match(regs);
			return req;
		};

		// 根据传入的类型判断是否是返回的结果,是的话返回;
		Vue.prototype.checkReqMsg = (type) => {
			switch (type) {
			case 1 :
				return "3,5";
			case 2:
				return "3";
			case 3:
				return "2";
			case 4:
				return null;
			case 5:
				return "1,3,6";
			case 6:
				return "2,4,6,8";
			case 7:
				return null;
			}
		};

		// 设置消息为已读
		Vue.prototype.setMessageStatus = (msgId) => {
			axios.get(`/api/message/setread/${msgId}`).then((res) => {
				return true;
			}).catch((err) => {
			});
		};


		Vue.prototype.isInArr = (val, arr) => {
			for (var i = 0; i < arr.length; i++) {
				if (val === arr[i]) {
					return true;
				}
			}
			return false;
		};

		Vue.prototype.resetDate = (str) => {
			var time = new Date(parseInt(str)*1000);
			var y = time.getFullYear();
			var m = time.getMonth()+1;
			var d = time.getDate();
			var h = time.getHours();
			var mm = time.getMinutes();
			var s = time.getSeconds();
			return y+"-"+add0(m)+"-"+add0(d)+" "+add0(h)+":"+add0(mm)+":"+add0(s);
		};

		Vue.prototype.isValidate = (str, key) => {

			let arr = {
				"tel": /^(13[0-9]{1}|14[5|7|9]{1}|15[0-3|5-9]{1}|166|17[0-3|5-8]{1}|18[0-9]{1}|19[8-9]{1}){1}\d{8}$/,
				"check_html" : /<[^>]+>/g,
				"number":/^[0-9]*$/,
				"string":/[\u4E00-\u9FA5\uF900-\uFA2D]/
			};

			return arr[key].test(str.trim());

		};

		// 设置新手指引的css参数设置
		Vue.prototype.getGuideCss = (dom, arrow, text = false) => {

			let css = [];
			// 构造镂空元素 css
			css.push({
				top: dom.top + "px",
				left: dom.left + "px",
				height: dom.height + "px",
				width: dom.width + "px"
			});

			// 构造箭头元素 css
			css.push(arrow);


			// 构造文案框位置 css
			if (text) {
				css.push({
					top: dom.top - (dom.top / 1.5) + "px",
				});
			}

			return css;
		};

		// 滚动条位置调整
		Vue.prototype.scrollTo = function(y = 0) {
			// 兼容ios12 输入框导致页面上浮问题
			window.scrollBy(0, y);
		};



		// 60s倒计时
		/**
		 *  @param vueObject
		 *  countNum : 倒计时
		 *  btnText: 按钮文本
		 *  disabled : 判断条件为 countNum > 0
		 */
		Vue.prototype.setCountDown = (vueObject) => {
			vueObject.countNum = 60;
			let intId = setInterval(() => {
				vueObject.countNum--;
				vueObject.btnText = vueObject.countNum + "s后重试";
				if (vueObject.countNum === 0) {
					clearInterval(intId);
					vueObject.btnText = "获取";
				}
			}, 1000);
		};


		// 微信分享
		Vue.prototype.wxShare = function (url, title, img, desc, callback) {
			// 发送授权认证请求
			axios.get("/api/wx/js_sdk_config", {
				params: {
					page_uri: _isAndroid() ? window.location.href : window.entryUrl, // ios 天坑处理
				}
			}).then((res) => {
				wx.config(res.data);
				wx.ready(function () {
					// 在这里调用 API
					wx.onMenuShareTimeline({ //例如分享到朋友圈的API
						title: title, // 分享标题
						link: url, // 分享链接
						imgUrl: img, // 分享图标
						success: function () {
							callback();
						},
						cancel: function () {
							// 用户取消分享后执行的回调函数
						}
					});
					wx.onMenuShareAppMessage({
						title: title, // 分享标题
						desc: desc, // 分享描述
						link: url, // 分享链接
						imgUrl: img, // 分享图标
						type: "", // 分享类型,music、video或link，不填默认为link
						dataUrl: "", // 如果type是music或video，则要提供数据链接，默认为空
						success: function () {
							callback();
						},
						cancel: function () {
							// 用户取消分享后执行的回调函数
						}
					});
					wx.error(function (res) {
						alert(res.errMsg); //打印错误消息。及把 debug:false,设置为debug:ture就可以直接在网页上看到弹出的错误提示
					});
				});
			}).catch((err) => {

			});
		};


		// 限制按钮点击事件，在结果返回以前不能继续点击
		Vue.directive("btn-control", {
			// 插入dom 时做的事情
			bind: function (el, bind) {
				el.addEventListener("click", () => {
					el.disabled = true;
					// 如果表单未填写完整，则没有返回值
					let func = bind.value();
					if (!func) {
						el.disabled = false;
						return;
					}
					func.then(res => {
						el.disabled = false;
					});
				});
			}
		});
	}
};

// order list as need
function orderList(list) {
	let n_list;
	n_list = list.sort(sortByStatus("status"));
	return n_list;
}

// sort by list.status asc
function sortByStatus(property) {
	return function (obj1, obj2) {
		let v1 = obj1[property];
		let v2 = obj2[property];
		return v1 - v2;
	};
}

function add0(m){return m<10?"0"+m:m; }

// filtrate by a_type
function filtrateByAsksType(list, selected_a_type) {
	let n_list = [];
	for (let i in list) {
		if (list[i].a_type == selected_a_type) {
			n_list.push(list[i]);
		} else {
			// 过滤掉
		}
	}
	return n_list;
}

function padLeftZero(str) {
	return ("00" + str).substr(str.length);
}

// 判断是否为安卓机器
function _isAndroid() {
	let ua = navigator.userAgent;
	let isAndroid = ua.indexOf("Android") > -1 || ua.indexOf("Adr") > -1; //android终端
	if (isAndroid) {
		return true;
	}
	return false;
}


function deepCloneShadow(obj){
	if (obj === null) return null;
	if (typeof obj !== "object") return obj;
	if (obj.constructor === Date) return new Date(obj);
	if (obj.constructor === RegExp) return new RegExp(obj);
	let newObj = new obj.constructor();  //保持继承链
	for (let key in obj) {
		if (obj.hasOwnProperty(key)) {   //不遍历其原型链上的属性
			let val = obj[key];
			newObj[key] = (typeof val === "object" && val !== null) ? deepCloneShadow(val) : val; // 使用arguments.callee解除与函数名的耦合
		}
	}
	return newObj;
}
