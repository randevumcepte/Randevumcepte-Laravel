<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-language" content="tr">

	<title>{{ $title }}</title>
	<style type="text/css">
		h4{float:left; font-family:"DeJaVu Sans Mono",monospace; margin-left: 5px;}
		p{ font-family:"DeJaVu Sans Mono",monospace;font-size: 12px}
		.metin{width:750px; height:200px;word-wrap:break-word;}
		span{font-family:"DeJaVu Sans Mono",monospace;margin-left: 70px;font-size: 12px }

}
	</style>
</head>
<body>
	<div style=" text-align: center;">
		<img src="data:image/png;base64, {!! base64_encode(QrCode::size(800)->generate('https://'.$isletme->domain)) !!} " style="width: 750px;height: 920px;" >
	</div>
	
		
	
	
</body>

</html>