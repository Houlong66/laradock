import axios  from "axios";
// import toast from "./toast/index";

// var request = axios.create({
// 	baseURL:window.host,                  // 配置路径
// 	timeout:10000                         // 超时时间
// });

let pending = [];                       // 记录每次发送的请求
let cancelToken = axios.CancelToken;    // 初始化取消请求的构造函数

// 判断函数
let removePending = (data,c,isrequest = false) => {

	let token = null;

	if(isrequest){

		if(data.method == "post"){

			token = data.baseURL +  data.url + "&" + data.method; // 生成标识

			// 检查是否已经在数组中

			if(pending.indexOf(token) === -1){

				pending.push(token);

			}else{

				c();

			}

		}

		return ;
	}

	//  reponse 时
	token = data.url + "&" + data.method;// 重新组装标识

	let index = pending.indexOf(token);

	pending.splice(index,1);

};


// 响应拦截器
axios.interceptors.request.use(

	// 在发送请求之前对于数据做些什么
	request => {

		// 取消令牌
		request.cancelToken = new cancelToken((c) => {
			removePending(request,c,true);

		});

		return request;
	},

	// 对请求错误做些什么
	err => {
		return Promise.reject(err).catch( (err) =>{
		});
	}

);


// 响应拦截器
axios.interceptors.response.use(
	// 对响应数据做点什么
	response => {
		removePending(response.config);
		// const res = response.data;
		// 统一状态管理
		// if(res.errcode !== 0){
		// 	let error_msg = res.errmsg ? res.errmsg : res.hintmsg;
		// 	toast(error_msg,"warning");
		// 	return ;
		// }
		return response;
	},

	// 对响应错误做点什么
	error => {
		// 这列处理一些response出错的逻辑
		return Promise.reject(error).catch( (err) => {

		});
	}
);


export default axios;