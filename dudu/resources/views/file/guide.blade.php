<!doctype html>
<html lang="zh-CN">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>附件下载</title>
    <link rel="stylesheet" type="text/css" href="{{ mix('css/app.css') }}">
    <style>
        .main-icon{
            font-size:56px;
            color: grey;
        }
        .content{
            padding: 30% 10%;
        }
        .text{
            padding-bottom: 10px;
            font-size:14px;
            margin:0 0;
        }
        .download-btn{
            width:100%;
            text-align: center;
            padding: 10px 0;
            background: #0d6aad;
            color: #fff;
            text-decoration: none;
            display:block;
        }
        .pop-box{
            width:100%;
            height:100%;
            position: fixed;
            top:0;
            left:0;
        }
        .back-place-holder{
            width:100%;
            height:100%;
            background: #000;
            opacity: 0.7;
        }
        .tips{
            position: absolute;
            width:80%;
            top:0;
            right:0;
        }
    </style>
</head>

<body>
	<div class="content">
        <div style="text-align:center; padding-bottom:20px;">
            <i class="iconfont dudu-yasuowenjian main-icon"></i>
        </div>
		<p class="text">文件名：{{$display_name}}</p>
		<p class="text">文件大小：{{$file_size}}</p>
		{{--<p>文件类型：{{$file_type}}</p>--}}
		<br>

		<!-- 这里由于微信电脑端浏览器的默认缓存机制的问题 只能使用直接的 href 跳转方式，否则会导致只有进度条而不会实际进行下载操作 -->
        <a class="download-btn" href="file?token={{$token}}">下载附件</a>
	</div>
    <div class="pop-box" id="pop" style="display:none;">
        <div class="back-place-holder"></div>
        <img id="tips" class="tips" src="/images/ios_browser_tips.png"/>
    </div>
</body>
<script>
    window.onload = function() {
    	var phone_sys = checkSys();
    	var wx = checkWx();

    	if(wx){
    		document.getElementById("pop").style.display = "block";
    		switch(phone_sys){
              case "iphone":
                document.getElementById("tips").src = "/images/ios_browser_tips.png";
              	break;
              case "android":
                document.getElementById("tips").src = "/images/android_browser_tips.png";
              	break;
            }
        }
    };

    function checkSys() {
        var ua = navigator.userAgent;
        var ipad = ua.match(/(iPad).*OS\s([\d_]+)/),
            isIphone = !ipad && ua.match(/(iPhone\sOS)\s([\d_]+)/),
            isAndroid = ua.match(/(Android)\s+([\d.]+)/),
            isMobile = isIphone || isAndroid;
        if (isMobile) {
        	return isIphone ? "iphone" : "android";
        } else {
            return "not mobile";
        }
    }

    function checkWx() {
    	// window.navigator.userAgent属性包含了浏览器类型、版本、操作系统类型、浏览器引擎类型等信息，
		var ua = navigator.userAgent;
		//通过正则表达式匹配ua中是否含有MicroMessenger字符串
		var isWx = ua.toLowerCase().match(/MicroMessenger/i) == "micromessenger";
		if (isWx) {
			return true;
		}
		return false;
    }
</script>
</html>
