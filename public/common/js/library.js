var BAIDU_LOADED = false;

var bmap = null;

var LastPointer = {
	longitude: CITY.longitude,
	latitude: CITY.latitude
};
var MAP_CENTER = {
	longitude: CITY.longitude,
	latitude: CITY.latitude
};
var AjaxProcesses = 0;
/**
 * 设置Cookie
 */
function Fandian_SetCookie(ckey, cval, cparams)
{
	pms = cparams ? cparams : {};
	pms.path = '/';
	if (!pms.expires) {
		_date = new Date();
		_date.setTime(_date.getTime()+3600*24*3*1000);
		pms.expires = _date;
	}
	
	cval = String(cval);
	
	return document.cookie = [
								encodeURIComponent(ckey), '=',
								encodeURIComponent(cval),
								';expires='+pms.expires.toUTCString(),
								';path=/',
							//	';domain='+FANDIAN_DOMAIN
	                           ].join('');
}

function Fandian_GetCookie(ckey)
{
    var result;
	return (result = new RegExp('(?:^|; )' + encodeURIComponent(ckey) + '=([^;]*)').exec(document.cookie)) ? decodeURIComponent(result[1]) : null;
}

function Fandian_DelCookie(name)
{
	var date = new Date(); 
	date.setTime(date.getTime()-1); 
	return document.cookie = [
								encodeURIComponent(name), '=',
								';expires='+date,
								';path=/',
							//	';domain='+FANDIAN_DOMAIN
	                           ].join('');
}

/**
 * 加载百度地图
 * 
 * @param callback
 */
function Fandian_LoadBaiduMapScript(callback)
{
	if (BAIDU_LOADED==false) {
		var script = document.createElement('script');
		script.src = 'http://api.map.baidu.com/api?v=1.3&callback='+callback;
		document.body.appendChild(script);
		BAIDU_LOADED = true;
	}	
}

function Fandian_ChooseCoord(CoordGuid, CoordName, CoordLongitude, CoordLatitude)
{
	if (confirm('选择 '+CoordName+' 作为您的地标吗？')) {
		$('#CoordGuid').val(CoordGuid);
		Fandian_SetCookie('coord_guid', CoordGuid);
		Fandian_SetCookie('coord_name', CoordName);
		Fandian_SetCookie('longitude', CoordLongitude);
		Fandian_SetCookie('latitude', CoordLatitude);
		if($('#mkey').val()!='如：街道，交叉路口，明显建筑物等')
		{
			Fandian_SetCookie('searchaddress', CoordName);
		}
		$('#cboxClose').click();
		window.location.href = FANDIAN_BASE_URL+"vendor";
	}
}

function Fandian_ChooseFirstCoord()
{
	try {
		clis = document.getElementById('coords').getElementsByTagName('li');

		if (clis.length>0) {
			tmp = clis[0].getAttribute('rel').split('|');
			Fandian_ChooseCoord(clis[0].getAttribute('id'), clis[0].getAttribute('title'), tmp[0], tmp[1]);
		}
	} catch(e) {
		Fandian_DebugAlert('Fandian_ChooseFirstCoord: '+e.message);
	}
}

function Fandian_NearbyCoords(longitude, latitude)
{
	if(!$('#colorbox').is(":hidden")) {
		longitude = parseInt(longitude)>10 ? longitude : CITY.longitude;
		latitude = parseInt(latitude)>10 ? latitude : CITY.latitude;
	
		$('#coords').html("<img style='margin-left:65px;margin-top:50px;float:left' src='"+FANDIAN_BASE_URL+"common/images/loading.gif' />");
		$.get(FANDIAN_BASE_URL+'service/lbs/nearbycoords?longitude='+longitude+'&latitude='+latitude,function(data,status){
			$('#coords').html(data);
		});
	}
}

function Fandian_MoveMapCenter()
{
	if (bmap!=null) {
		_center = bmap.getCenter();
		if (_center.lng!=LastPointer.longitude || _center.lat!=LastPointer.latitude) {
			LastPointer.longitude = _center.lng;
			LastPointer.latitude = _center.lat;
			
			bmarker.setPosition(_center);
			Fandian_NearbyCoords(_center.lng, _center.lat);
		}
	}
}

function Fandian_RenderAddressMap()
{
	Fandian_NearbyCoords(MAP_CENTER.longitude, MAP_CENTER.latitude);

	bmap = new BMap.Map('modal_address_map_canvas');
	bpointer = new BMap.Point(MAP_CENTER.longitude, MAP_CENTER.latitude);
	bmarker = new BMap.Marker(bpointer);

	bmap.addOverlay(bmarker);
	bmap.addControl(new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_RIGHT, offset: new BMap.Size(10, 10)}));
	bmap.centerAndZoom(bpointer, 13);
	
	LastPointer.longitude = MAP_CENTER.longitude;
	LastPointer.latitude = MAP_CENTER.latitude;
	
	bmap.addEventListener("dragend", function(){    
		Fandian_MoveMapCenter();
	}); 
}

function Fandian_Service_BaiduPlace(place, callback)
{
	if(place == ADDRESS_TIP){
		MAP_CENTER.longitude = CITY.longitude;
		MAP_CENTER.latitude = CITY.latitude;
		callback();
	}else{
		$.ajax({
			type: "GET",
			url : FANDIAN_BASE_URL+'service/baidu/place?place='+encodeURI(place), 
			dataType: "json",
			success: function(json){
				MAP_CENTER.longitude = json.longitude;
				MAP_CENTER.latitude = json.latitude;
				callback();
			}
		});
	}
}

/**
 * 利用浏览器的Geolocation定位用户经纬度
 * 
 */
function Fandian_Geolocation()
{
	if (typeof(navigator.geolocation)=='undefined') {
		alert('抱歉，本功能只支持非IE核心的浏览器。');
	} else {
		var geolocation = new BMap.Geolocation();
		geolocation.getCurrentPosition(function(r){
			//Fandian_AjaxCompleted();
			
		    if(this.getStatus() == BMAP_STATUS_SUCCESS){
		    	if (bmap!=null) {
		    		_center = bmap.getCenter();
		    		if (_center.lng!= r.point.lng || _center.lat!=r.point.lat) {
		    			LastPointer.longitude = r.point.lng;
		    			LastPointer.latitude = r.point.lat;
		    			
		    			bmarker.setPosition(r.point);
		    			bmap.setCenter(r.point);
		    			Fandian_NearbyCoords(r.point.lng, r.point.lat);
		    		}
		    	}	        
		    } else {
		    	Fandian_Alert('囧，猜不到您的具体位置，请手动设定。\n\n======================\n\n同时，请确保您的浏览器是IE9或者Firefox、GoogleChrome。')
		    }        
		});
	}	
}


/**
 * 一个通用的订单信息保存
 * 
 * @param key
 * @param val
 */
function SaveOrderCommon(_key, val)
{
	if (_key=='contactor') {
		if (val==LAST_NAME) {
			return false;
		} else {
			LAST_NAME = val;
		}
	} else if (_key=='phone') {
		if (val==LAST_PHONE) {
			return false;
		} else {
			LAST_PHONE = val;
		}
	} else if (_key=='address') {
		if (val==LAST_ADDR) {
			return false;
		} else {
			LAST_ADDR = val;
		}
	} else if (_key=='remark')
	{
		if (val==LAST_REMARK) {
			return false;
		} else {
			LAST_REMARK = val;
		}
	}

	Fandian_SetCookie(_key, val);
}










