var LAST_ITEM_COUNT = 0;
var MINI_MARKET = false;
var PREVENT_DEFAULT = false;
var ADDED_THIS_VENDOR = true;
var LAST_VENDOR_GUID = '';
var JS_ONE_DAY = 3600*24*1000;
var TAB_IMGS_LOADED = [];
var BH = 0;

function FindServiceParam(ServiceName)
{
	_Service = null;
	
	for (i in SERVICES) {
		if (SERVICES[i].name==ServiceName) {
			_Service = SERVICES[i];
			break;
		}
	}
	
	return _Service;
}

function V_SetCookie(ckey, cval, cparams)
{
	return Fandian_SetCookie(C_PREFIX+ckey, cval, cparams);
}

/**
 * 菜品是否已估清
 * 
 * @param ItemGuid
 * @returns
 */
function ItemIsSoldOut(ItemGuid)
{
	return !!(Fandian_InArray(ItemGuid, SOLDOUT_ITEMS));
}

function PrepareAddItem(ItemGuid, count)
{
	count = parseInt(count);
	arr = [];
	
	founded = false;
	for (i in ORDER_PARAMS.items) {
		if (ORDER_PARAMS.items[i].ItemGuid==ItemGuid) {
			ORDER_PARAMS.items[i].count += count;
			founded = true;
		}
		arr.push(ORDER_PARAMS.items[i].ItemGuid+'|'+ORDER_PARAMS.items[i].count);
	}
	
	if (!founded) {
		ORDER_PARAMS.items.push({
			'ItemGuid': ItemGuid,
			'count': count
		});
		arr.push(ItemGuid+'|'+count)
	}

	V_SetCookie('items', arr.join(','));
}

function PrepareReduceItem(ItemGuid, count)
{
	count = parseInt(count);
	new_items = [];
	arr = [];
	
	for (i in ORDER_PARAMS.items) {
		if (ORDER_PARAMS.items[i].ItemGuid==ItemGuid) {
			c = ORDER_PARAMS.items[i].count - count;
			if (c>0) {
				arr.push(ItemGuid+'|'+c);
				new_items.push({
					'ItemGuid': ItemGuid,
					'count': c
				});
			}
		} else {
			new_items.push(ORDER_PARAMS.items[i]);
			arr.push(ORDER_PARAMS.items[i].ItemGuid+'|'+ORDER_PARAMS.items[i].count);
		}
	}
	
	ORDER_PARAMS.items = new_items;
	V_SetCookie('items', arr.join(','));
}

/**
 * 是否是有效的电话号码
 * 
 * @param value
 * @returns
 */
function IsValidPhone(value)
{
	pat = /^([0-9,]{1,})$/;
	
	return pat.test(value);
}

/**
 * 检测用户输入的电话号码格式
 * 
 * @param dom
 */
function CheckPhoneFormat(dom)
{
	result = false;
	
	if (dom.value!='') {
		value = Fandian_Trim(dom.value);
		pat = /^([0-9,]{1,})$/;
		
		if (Fandian_IsValidPhone(value)) {
			result = true;
		} else {
			value = value.substr(0, value.length-1);
			Fandian_Alert('电话号码只允许英文半角数字及逗号！请检查您的输入。');
		}
		
		dom.value = value;
	} else {
		result = true;
	}
	
	return result;
}

/**
 * 菜品图片异常时的事件
 * 
 * @param dom
 */
function ItemImgOnError(dom)
{
	Fandian_ImgOnError(dom);
}

/**
 * 在强制预定时设定下一个商家开始服务的时间
 * 
 */
function ParseNextServiceTime()
{
	var pre_hour = parseInt(document.getElementById('order_pre_hour').value);
	var pre_minute = parseInt(document.getElementById('order_pre_minute').value);
	var now = new Date();
	year = now.getFullYear();
	month = now.getMonth();
	day = now.getDate();
	hour = now.getHours();
	minute = now.getMinutes();
	nowt = now.getTime();
	gotted = [];
	
	for (var i in VENDOR_SERVICE_TIME) {
		start = VENDOR_SERVICE_TIME[i].start;
		end = VENDOR_SERVICE_TIME[i].end;
		
		start_hour = parseInt(start.substr(0,2));
		end_hour = parseInt(end.substr(0,2));
		
		sdate = new Date();
		sdate.setHours(start_hour);
		sdate.setMinutes(parseInt(start.substr(3,2)));
		sdate.setSeconds(0);
		stime = sdate.getTime();
		
		edate = new Date();
		if (start_hour>end_hour) {
			//	跨天的营业时间
			edate.setTime(nowt+3600*24*1000);
		}
		edate.setHours(end_hour);
		edate.setMinutes(parseInt(end.substr(3,2)));
		edate.setSeconds(0);
		etime = edate.getTime();

		if (nowt>etime) {
			continue;
		} else if (parseInt(gotted.length)<=0) {
			if ((stime-nowt)>3600*1000) {
				gotted = [year, month, day, start.substr(0,2), start.substr(3,2)];
			} else {
				gdate = new Date();
				gdate.setTime(nowt+1000*3600);
				gotted = [gdate.getFullYear(), gdate.getMonth(), gdate.getDate(), gdate.getHours(), gdate.getMinutes()];
			}
		} else {
			continue;
		}
	}	

	return gotted;
}

/**
 * 检查预定时间是否在商家营业时间范围内容
 * 
 */
function CheckPreTime()
{
	result = false;
	var pre_year = parseInt(document.getElementById('order_pre_year').value);
	var pre_month = parseInt(document.getElementById('order_pre_month').value);
	var pre_day = parseInt(document.getElementById('order_pre_day').value);
	var pre_hour = parseInt(document.getElementById('order_pre_hour').value);
	var pre_minute = parseInt(document.getElementById('order_pre_minute').value);
	var pre_date = new Date();

	pre_date.setFullYear(pre_year);
	pre_date.setMonth(pre_month-1);
	pre_date.setDate(pre_day);
	pre_date.setHours(pre_hour);
	pre_date.setMinutes(pre_minute);
	pre_date.setSeconds(0);
	var pre_time = pre_date.getTime();
	
	var now = new Date();
	var nowt = now.getTime();
	var now_year = now.getFullYear();
	var now_month = now.getMonth();
	var now_day = now.getDate();

	if (nowt<pre_time) {
		for (var i in VENDOR_SERVICE_TIME) {
			var _offset = 1;
			start = VENDOR_SERVICE_TIME[i].start;
			end = VENDOR_SERVICE_TIME[i].end;
			
			start_hour = parseInt(start.substr(0,2));
			end_hour = parseInt(end.substr(0,2));
			
			sdate = new Date();
			edate = new Date();
			
			for(var j=0;j<3;j++) {
				sdate.setTime(pre_time+(j-1)*JS_ONE_DAY);
				sdate.setHours(start_hour);
				sdate.setMinutes(parseInt(start.substr(3,2)));
				sdate.setSeconds(0);
				stime = sdate.getTime();
				
				if (start_hour>end_hour) {
					edate.setTime(pre_time+j*JS_ONE_DAY);
				} else {
					edate.setTime(pre_time+(j-1)*JS_ONE_DAY);
				}
				edate.setHours(end_hour);
				edate.setMinutes(parseInt(end.substr(3,2)));
				edate.setSeconds(0);
				etime = edate.getTime();	
				
				if (pre_time>stime && pre_time<etime) {
					result = true;
				}
			}
		}
	}

	return result;
}

/**
 * 检查预定时间是否在业务时间范围内容
 * 
 */
function CheckServicePreTime()
{
	result = false;
	var pre_year = parseInt(document.getElementById('order_pre_year').value);
	var pre_month = parseInt(document.getElementById('order_pre_month').value);
	var pre_day = parseInt(document.getElementById('order_pre_day').value);
	var pre_hour = parseInt(document.getElementById('order_pre_hour').value);
	var pre_minute = parseInt(document.getElementById('order_pre_minute').value);
	var pre_date = new Date();

	pre_date.setFullYear(pre_year);
	pre_date.setMonth(pre_month-1);
	pre_date.setDate(pre_day);
	pre_date.setHours(pre_hour);
	pre_date.setMinutes(pre_minute);
	pre_date.setSeconds(0);
	var pre_time = pre_date.getTime();
	
	_start = new Date();
	_start.setFullYear(pre_year);
	_start.setMonth(pre_month-1);
	_start.setDate(pre_day);
	_start.setHours(SERVICE_SERVICE_TIME.sh);
	_start.setMinutes(SERVICE_SERVICE_TIME.sm);
	_start_time = _start.getTime();
	
	_end_time = _start_time + SERVICE_SERVICE_TIME.et - SERVICE_SERVICE_TIME.st;

	if (pre_time>=_start_time && pre_time<=_end_time) {
		result = true;
	} else {
		_end = new Date();
		_end.setFullYear(pre_year);
		_end.setMonth(pre_month-1);
		_end.setDate(pre_day);
		_end.setHours(SERVICE_SERVICE_TIME.eh);
		_end.setMinutes(SERVICE_SERVICE_TIME.em);
		
		_end_time = _end.getTime();
		_start_time = _end_time - SERVICE_SERVICE_TIME.et + SERVICE_SERVICE_TIME.st;
		if (pre_time>=_start_time && pre_time<=_end_time) {
			result = true;
		}
	}

	return result;
}

/**
 * 批量设置预定相关参数
 * 
 */
function BatchSetPre()
{
	if (!Fandian_InArray(SERVICE_NAME, SERVICE_NOW_SERVICES)) {
		return BatchSetServicePre();
	}

	PREVENT_DEFAULT = true;
	gotted = ParseNextServiceTime();

	if (gotted.length>0) {
		year = parseInt(gotted[0]);
		month = parseInt(gotted[1])+1;
		day = parseInt(gotted[2]);
		hour = parseInt(gotted[3]);
		minute = parseInt(gotted[4]);

		if (minute>30) {
			hour = hour+1;
			minute = 0;
		} else {
			minute = 30;
		}
		
		if (hour<10) {
			hour = '0'+hour;
		}
		
		if (minute<10) {
			minute = '0'+minute;
		}
		
		if (minute=='00') {
			minute = '0';
		}

		Fandian_SetDomValue('order_pre_year', year);
		Fandian_SetDomValue('order_pre_month', month);
		Fandian_SetDomValue('order_pre_day', day);
		Fandian_SetDomValue('order_pre_hour', hour);
		Fandian_SetDomValue('order_pre_minute', minute);
		
		pobj = {};
		pobj.pre_year = document.getElementById('order_pre_year').value;
		pobj.pre_month = document.getElementById('order_pre_month').value;
		pobj.pre_day = document.getElementById('order_pre_day').value;
		pobj.pre_hour = document.getElementById('order_pre_hour').value;
		pobj.pre_minute = document.getElementById('order_pre_minute').value;

		V_SetCookie('pre_year', pobj.pre_year);
		V_SetCookie('pre_month', pobj.pre_month);
		V_SetCookie('pre_day', pobj.pre_day);
		V_SetCookie('pre_hour', pobj.pre_hour);
		V_SetCookie('pre_minute', pobj.pre_minute);

		ORDER_PARAMS.pre_year = pobj.pre_year;
		ORDER_PARAMS.pre_month = pobj.pre_month;
		ORDER_PARAMS.pre_day = pobj.pre_day;
		ORDER_PARAMS.pre_hour = pobj.pre_hour;
		ORDER_PARAMS.pre_minute = pobj.pre_minute;
	}
	
	PREVENT_DEFAULT = false;
}

/**
 * 批量设置预定相关参数
 * 
 */
function BatchSetServicePre()
{
	PREVENT_DEFAULT = true;
	x = PREPARE_PARAMS.next_service_open_time;
	ddd = new Date();
	ddd.setTime(x+'000');
	
	year = ddd.getFullYear();
	month = ddd.getMonth();
	day = ddd.getDate();
	hour = ddd.getHours();
	minute = ddd.getMinutes();
	
	gotted = [year, month, day, hour, minute];

	if (gotted.length>0) {
		year = parseInt(gotted[0]);
		month = parseInt(gotted[1])+1;
		day = parseInt(gotted[2]);
		hour = parseInt(gotted[3]);
		minute = parseInt(gotted[4]);

		if (minute>30) {
			hour = hour+1;
			minute = 0;
		} else {
			minute = 30;
		}
		
		if (hour<10) {
			hour = '0'+hour;
		}
		
		if (minute<10) {
			minute = '0'+minute;
		}
		
		if (minute=='00') {
			minute = '0';
		}

		Fandian_SetDomValue('order_pre_year', year);
		Fandian_SetDomValue('order_pre_month', month);
		Fandian_SetDomValue('order_pre_day', day);
		Fandian_SetDomValue('order_pre_hour', hour);
		Fandian_SetDomValue('order_pre_minute', minute);
		
		pobj = {};
		pobj.pre_year = document.getElementById('order_pre_year').value;
		pobj.pre_month = document.getElementById('order_pre_month').value;
		pobj.pre_day = document.getElementById('order_pre_day').value;
		pobj.pre_hour = document.getElementById('order_pre_hour').value;
		pobj.pre_minute = document.getElementById('order_pre_minute').value;

		V_SetCookie('pre_year', pobj.pre_year);
		V_SetCookie('pre_month', pobj.pre_month);
		V_SetCookie('pre_day', pobj.pre_day);
		V_SetCookie('pre_hour', pobj.pre_hour);
		V_SetCookie('pre_minute', pobj.pre_minute);

		ORDER_PARAMS.pre_year = pobj.pre_year;
		ORDER_PARAMS.pre_month = pobj.pre_month;
		ORDER_PARAMS.pre_day = pobj.pre_day;
		ORDER_PARAMS.pre_hour = pobj.pre_hour;
		ORDER_PARAMS.pre_minute = pobj.pre_minute;
	}
	
	PREVENT_DEFAULT = false;
}

/**
 * 搜寻菜品对应的js对象
 * 
 * @param ItemGuid
 * @returns
 */
function FindVendorItem(ItemGuid)
{
	ADDED_THIS_VENDOR = true;
	_item = null;

	for(var i=0;i<VENDOR_ITEMS.length;i++) {
		if (VENDOR_ITEMS[i].ItemGuid==ItemGuid) {
			_item = VENDOR_ITEMS[i];
			break;
		}
	}
	
	if (!_item) {
		for(var i=0;i<MARKET_ITEMS.length;i++) {
			if (MARKET_ITEMS[i].ItemGuid==ItemGuid) {
				_item = MARKET_ITEMS[i];
				break;
			}
		}
	}
	
	if (!_item) {
		for(var i=0;i<REMAIN_ITEMS.length;i++) {
			if (REMAIN_ITEMS[i].ItemGuid==ItemGuid) {
				_item = REMAIN_ITEMS[i];
				ADDED_THIS_VENDOR = false;
				break;
			}
		}
	}
	
	return _item;
}

/**
 * 提交订单
 */
function SubmitOrder()
{
	if (FANDIAN_CA_ENABLED) {
		try {
			tf.open();
			return false;
		} catch (e) {}
	}
	
	spliter = ALERT_USE_MODAL ? '<br />' : "\n";
	try {
		ORDER_IS_PRE = document.getElementById('OrderExpressTime_1').checked;
		FORCE_PRE = true;
	} catch(e) {
		ORDER_IS_PRE = false
	}
	
	//	检查订单数据完整性
	ErrorMsg = '';
	Contactor = document.getElementById('OrderContactor').value;
	if (Fandian_Trim(Contactor)=='') {
		ErrorMsg += spliter+"请填写您的姓名！";
	}
	
	Phone = document.getElementById('OrderPhone').value;
	if (Fandian_Trim(Phone)=='') {
		ErrorMsg += spliter+'请填写您的电话！';
	} else if (!Fandian_IsValidPhone(Phone)) {
		ErrorMsg += spliter+'电话号码只允许英文半角数字及逗号！';
	}
	
	Address = document.getElementById('OrderAddress').value;
	if (Fandian_Trim(Address)=='') {
		ErrorMsg += spliter+'请填写您的送餐地址！';
	}

	if (ORDER_IS_PRE==true && !CheckPreTime()) {
		ErrorMsg += spliter+'您设定的预定时间不在商家的营业时间范围内！';
	} 
	
	if (ORDER_IS_PRE==true && !CheckServicePreTime()) {
		_sName = '';
		if (SERVICE_NAME!='普通') {
			_sName = ' '+SERVICE_NAME+' ';
		}
		ErrorMsg += spliter+'您设定的时间不在我们'+_sName+'的服务时间范围内容！';
	}

	if (PREPARED_ITEMS.length==0 && MARKET_PREPARED_ITEMS.length==0 && Fandian_Trim(document.getElementById('order_vendors').innerHTML)=='') {
		ErrorMsg += spliter+'请先选择要点的菜品！';
	}
	
	if (ErrorMsg!='') {
		Fandian_Alert('订单提交失败！请检查您填写的信息。'+spliter+spliter+"==========="+spliter+spliter+ErrorMsg);
	} else {
		Fandian_WaitAjax(function(){
			Fandian_AjaxLoading();
			window.location = FANDIAN_BASE_URL+'order/confirm/service/'+SERVICE_NAME;
		});
	}
}

/**
 * 清理订单数据
 */
function ClearOrder()
{
	if (confirm('确定吗？')) {
		V_SetCookie('items', '');	
		V_SetCookie('remarks', '');
		
		dom = document.getElementById('order_vendors');
		
		for (i in PREPARED_ITEMS) {
			Fandian_SetDomCss('ii_'+PREPARED_ITEMS[i], 'item_not_choosed');
			Fandian_SetDomCss('it_'+PREPARED_ITEMS[i], 'item_not_choosed_title');
		}
		
		for (i in MARKET_PREPARED_ITEMS) {
			Fandian_SetDomCss('ii_'+MARKET_PREPARED_ITEMS[i], 'item_not_choosed');
			Fandian_SetDomCss('it_'+MARKET_PREPARED_ITEMS[i], 'item_not_choosed_title');
		}
		
		PREPARED_ITEMS = [];
		MARKET_PREPARED_ITEMS = [];
		Fandian_SetDomHtml('order_vendors', '');
		
		try {
			pl2r();
		} catch(e) {
			
		}
	}
}

/**
 * 当点某商家第一个菜时，做一下初始化操作
 * 
 * @param MiniMarket
 */
function InitFirstItem(MiniMarket)
{
	MiniMarket = typeof(MiniMarket)!='undefined' ? !!MiniMarket : false;
	
	_this_vendor_name = MiniMarket ? '迷你超市' : VENDOR_NAME;
	_this_vendor_guid = MiniMarket ? MARKET_GUID : VENDOR_GUID;
	
	var wrapper = document.getElementById('order_vendors');
	hidden_dom = document.getElementById('order_vendor_'+_this_vendor_guid);
	
	if (MiniMarket) {
		_freight = 0;
	} else {
		_freight = THIS_VENDOR_FREIGHT;
	}
	
	html = hidden_dom ? '' : "<li id='order_vendor_"+_this_vendor_guid+"'>";
	html += "<span class='order_vendor_title'>【"+_this_vendor_name+"】</span>";
	html += "<ol class='order_vendor_items' id='order_vendor_items_"+_this_vendor_guid+"'>";
	html += "<li class='order_vendor_item_header'>";
	html += "<ul class='order_vendor_item_row'>";
	html += "<li class='order_vendor_item_row_1'>菜名</li>";
	html += "<li class='order_vendor_item_row_2'>份数</li>";
	html += "<li class='order_vendor_item_row_3'>价格</li>";
	html += "<li class='order_vendor_item_row_4'>操作</li>";
	html += "</ul>";
	html += "</li>";
	html += "</ol>";
	html += "<ol class='order_vendor_items' id='order_summary_"+_this_vendor_guid+"'>";
	html += "<li>";
	html += "<span style='float:left;padding-left:10px;'>打包盒：<span id='order_boxes_"+_this_vendor_guid+"'>0</span>&nbsp;&nbsp;&nbsp;&nbsp;共：￥<span id='order_boxes_price_"+_this_vendor_guid+"'>0</span></span>";
	html += "</li>";
	html += "<li style='clear: both;'>";
	html += "<span style='float:left;padding-left:10px'>外送费:￥<span id='order_freight_"+_this_vendor_guid+"'>"+_freight+"</span></span>";
	html += "</li>";
	html += "<li style='clear: both;'>";
	html += "<span style='float:left;padding-left:10px;'>合计:￥</span><span id='order_total_"+_this_vendor_guid+"'>"+THIS_VENDOR_FREIGHT+"</span>";
	html += "</li>";
	html += "<li>";
	html += "<span style='float:left;padding-left:10px;'>备注:</span><input type='text' onblur=\"SaveRemark(this.value, '"+_this_vendor_guid+"');\" name='order_remark_"+_this_vendor_guid+"' id='order_remark_"+_this_vendor_guid+"' value='' size='30' />";
	html += "</li>";
	html += "</ol>";
	
	html += hidden_dom ? '' : "</li>";
	
	if (hidden_dom) {
		Fandian_SetDomHtml('order_vendor_'+_this_vendor_guid, html);
		html = "<li id='order_vendor_"+_this_vendor_guid+"'>"+html+"</li>";
	}
	Fandian_AppendHtml(wrapper, html);
}

/**
 * 减少订单菜品数量
 * @param ItemGuid
 */
function ReduceOrderItem(ItemGuid, MiniMarket)
{
	MINI_MARKET = typeof(MiniMarket)!='undefined' ? !!MiniMarket : false;
	LAST_ITEM_GUID = ItemGuid.toString().replace('ii_', '').replace('it_', '');
	var ThisItem = null;
	
	try {
		ThisItem = FindVendorItem(LAST_ITEM_GUID);
		_count = ThisItem.MinOrderQty;
	} catch(e) {
		_count = 1;
	}

	if (Fandian_IsGuid(LAST_ITEM_GUID) && ThisItem) {
		LAST_ITEM_COUNT = parseInt(document.getElementById('order_vendor_item_count_'+LAST_ITEM_GUID).innerHTML);
		
		if (LAST_ITEM_COUNT>ThisItem.MinOrderQty || (LAST_ITEM_COUNT<=ThisItem.MinOrderQty && confirm('确定要删除这道菜吗？'))) {
			PrepareReduceItem(LAST_ITEM_GUID, _count);
			if (LAST_ITEM_COUNT==ThisItem.MinOrderQty) {
				Fandian_RemoveDom('order_vendor_item_'+LAST_ITEM_GUID);
				Fandian_SetDomCss('ii_'+LAST_ITEM_GUID, 'item_not_choosed');
				Fandian_SetDomCss('it_'+LAST_ITEM_GUID, 'item_not_choosed_title');
				if (MINI_MARKET) {
					MARKET_PREPARED_ITEMS = Fandian_ArrayRemove(LAST_ITEM_GUID, MARKET_PREPARED_ITEMS);
				} else {
					PREPARED_ITEMS = Fandian_ArrayRemove(LAST_ITEM_GUID, PREPARED_ITEMS);
				}
			} else if (ThisItem) {
				Fandian_SetDomHtml('order_vendor_item_count_'+LAST_ITEM_GUID, LAST_ITEM_COUNT-ThisItem.MinOrderQty);
				Fandian_SetDomHtml('order_vendor_item_price_'+LAST_ITEM_GUID, (LAST_ITEM_COUNT-ThisItem.MinOrderQty)*ThisItem.UnitPrice);
			}
			
			_this_vendor_guid = ThisItem.VendorGuid;
			
			boxes = parseInt(document.getElementById('order_boxes_'+_this_vendor_guid).innerHTML);
			boxes_price = parseFloat(document.getElementById('order_boxes_price_'+_this_vendor_guid).innerHTML);
			total = parseFloat(document.getElementById('order_total_'+_this_vendor_guid).innerHTML);

			Fandian_SetDomHtml('order_boxes_'+_this_vendor_guid, boxes - parseInt(ThisItem.BoxQty));
			Fandian_SetDomHtml('order_boxes_price_'+_this_vendor_guid, boxes_price - parseFloat(ThisItem.BoxQty*ThisItem.BoxUnitPrice));
			Fandian_SetDomHtml('order_total_'+_this_vendor_guid, total-parseFloat(ThisItem.BoxQty*ThisItem.BoxUnitPrice)-parseFloat(ThisItem.MinOrderQty*ThisItem.UnitPrice));					
		
			lis = document.getElementById('order_vendor_items_'+_this_vendor_guid).getElementsByTagName('li');
			if (lis.length<=5) {
				Fandian_RemoveDom('order_vendor_'+_this_vendor_guid);
			}
		}
					
	}
}

/**
 * 添加一个菜品html行
 * 
 * @param ItemGuid
 * @param MiniMarket
 */
function AddOrderItemRow(ItemGuid, MiniMarket)
{
	MINI_MARKET = typeof(MiniMarket)!='undefined' ? !!MiniMarket : false;
	ThisItem = null;
	
	try {
		ThisItem = FindVendorItem(ItemGuid);
	} catch(e) {}

	if (ThisItem!=null) {
		_this_vendor_guid = ThisItem.VendorGuid;
		var wrapper = document.getElementById('order_vendor_items_'+_this_vendor_guid);
		
		count = ThisItem.MinOrderQty;
		if (ADDED_THIS_VENDOR && ((!MINI_MARKET && !Fandian_InArray(ItemGuid, PREPARED_ITEMS)) || (MINI_MARKET && !Fandian_InArray(ItemGuid, MARKET_PREPARED_ITEMS)))) {
			html = "<li id='order_vendor_item_"+ItemGuid+"' class='order_vendor_item'>";
			html += "<ul class='order_vendor_item_row'>";
			html += "<li class='order_vendor_item_row_1'>"+ThisItem.ItemName+"</li>";
			html += "<li class='order_vendor_item_row_2'><span id='order_vendor_item_count_"+ItemGuid+"'>"+ThisItem.MinOrderQty+"</span> "+ThisItem.UnitName+"</li>";
			html += "<li class='order_vendor_item_row_3'><span id='order_vendor_item_price_"+ItemGuid+"'>"+ThisItem.MinOrderQty*ThisItem.UnitPrice+"</span></li>";
			html += "<li class='order_vendor_item_row_4'>";
			html += "<a href='javascript:void(0);' class='add_order_item' onclick=\"AddOrderItem('"+ItemGuid+"', "+(MINI_MARKET ? 'true' : 'false')+");\">+</a>\n";
			html += "<a href='javascript:void(0);' class='reduce_order_item' onclick=\"ReduceOrderItem('"+ItemGuid+"', "+(MINI_MARKET ? 'true' : 'false')+");\">-</a>";
			html += "</li>";
			html += "</ul>";
			html += "</li>";
			
			Fandian_AppendHtml(wrapper, html);

			if (MINI_MARKET) {
				MARKET_PREPARED_ITEMS.push(ItemGuid);
			} else {
				PREPARED_ITEMS.push(ItemGuid);
			}
		} else {
			count = parseInt(parseInt(document.getElementById('order_vendor_item_count_'+ItemGuid).innerHTML)+parseInt(ThisItem.MinOrderQty));
			
			Fandian_SetDomHtml('order_vendor_item_count_'+ItemGuid, count);
			Fandian_SetDomHtml('order_vendor_item_price_'+ItemGuid, count*ThisItem.UnitPrice);
		}
		
		boxes = parseInt(document.getElementById('order_boxes_'+_this_vendor_guid).innerHTML);
		boxes_price = parseFloat(document.getElementById('order_boxes_price_'+_this_vendor_guid).innerHTML);
		total = parseFloat(document.getElementById('order_total_'+_this_vendor_guid).innerHTML);

		Fandian_SetDomHtml('order_boxes_'+_this_vendor_guid, parseInt(parseInt(ThisItem.BoxQty)+parseInt(boxes)));
		Fandian_SetDomHtml('order_boxes_price_'+_this_vendor_guid, parseFloat(parseFloat(ThisItem.BoxQty*ThisItem.BoxUnitPrice)+parseFloat(boxes_price)));
		Fandian_SetDomHtml('order_total_'+_this_vendor_guid, parseFloat(total+parseFloat(ThisItem.BoxQty*ThisItem.BoxUnitPrice)+parseFloat(ThisItem.MinOrderQty*ThisItem.UnitPrice)));
		
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
	if (PREVENT_DEFAULT==true) {
		return false;
	}
	
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
	}
	
	params = {
		'act': _key,
		'silent': false
	};
	eval('ORDER_PARAMS.'+_key+'="'+val+'"');
	Fandian_SetCookie(_key, val);
}

/**
 * 保存订单支付信息
 * 
 * @param dom
 */
function SwitchOrderPayMethod(dom)
{
	PayMethod = parseInt(dom.value);
	ORDER_PARAMS.paymethod = PayMethod;
	V_SetCookie('paymethod', PayMethod);
}

/**
 * 保存订单备注
 * 
 * @param remark
 * @param VendorGuid
 */
function SaveRemark(remark, VendorGuid)
{
	arr = [];
	founded = false;
	for (i in ORDER_PARAMS.remarks) {
		_r = ORDER_PARAMS.remarks[i];
		if (_r.VendorGuid==VendorGuid) {
			ORDER_PARAMS.remarks[i].remark = remark;
			founded = true;
		}
		
		arr.push(ORDER_PARAMS.remarks[i].remark+'{}'+ORDER_PARAMS.remarks[i].VendorGuid);
	}
	
	if (!founded) {
		arr.push(remark+'{}'+VendorGuid);
		ORDER_PARAMS.remarks.push({
			'VendorGuid': VendorGuid,
			'remark': remark
		});
	}
	
	V_SetCookie('remarks', arr.join('[]'));
}

/**
 * 保存预订时间
 * 
 * @param dom
 * @param key
 */
function SaveOrderPreTime(dom, key)
{
	_value = dom.value;
	Fandian_PrepareOrder({
		'act': 'express_time',
		'express_time': '1',
		key: _value
	});
}

/**
 * 设置订单配送时间
 * 
 * @param dom
 */
function SwitchOrderExpressTime(dom)
{
	var FORCE_PRE = false;
	
	try {
		ORDER_IS_PRE = document.getElementById('OrderExpressTime_1').checked;
		FORCE_PRE = true;
	} catch(e) {
		ORDER_IS_PRE = false
	}

	if (!!!VENDOR_IN_SERVICE && !!!ORDER_IS_PRE) {
		Fandian_Alert('现在非该商家的服务时间，你只能通过预订的方式进行下单。');
		BatchSetPre();
		document.getElementById('OrderExpressTime_1').checked = true;
		return false;
	} else if (!!!ORDER_IS_PRE && !Fandian_InArray(SERVICE_NAME, SERVICE_NOW_SERVICES)) {
		Fandian_Alert('现在不是我们 '+SERVICE_NAME+' 的服务时间，你只能通过预订的方式进行下单。');
		BatchSetServicePre();
		document.getElementById('OrderExpressTime_1').checked = true;
		return false;
	} else {
		ExpressTimeSetting = parseInt(dom.value);
		pobj = {};
		
		if (ExpressTimeSetting>0) {
			//	预订
			Fandian_ShowDom('OrderExpressTimePreSettings');
			
			BatchSetPre();
			
			pobj.pre_year = document.getElementById('order_pre_year').value;
			pobj.pre_month = document.getElementById('order_pre_month').value;
			pobj.pre_day = document.getElementById('order_pre_day').value;
			pobj.pre_hour = document.getElementById('order_pre_hour').value;
			pobj.pre_minute = document.getElementById('order_pre_minute').value;

			V_SetCookie('pre_year', pobj.pre_year);
			V_SetCookie('pre_month', pobj.pre_month);
			V_SetCookie('pre_day', pobj.pre_day);
			V_SetCookie('pre_hour', pobj.pre_hour);
			V_SetCookie('pre_minute', pobj.pre_minute);

			ORDER_PARAMS.pre_year = pobj.pre_year;
			ORDER_PARAMS.pre_month = pobj.pre_month;
			ORDER_PARAMS.pre_day = pobj.pre_day;
			ORDER_PARAMS.pre_hour = pobj.pre_hour;
			ORDER_PARAMS.pre_minute = pobj.pre_minute;
		} else {
			//	尽快
			Fandian_HideDom('OrderExpressTimePreSettings');
		}

		ORDER_PARAMS.express_setting = ExpressTimeSetting;
		V_SetCookie('express_setting', ExpressTimeSetting);
		
		return true;
	}
}

/**
 * 添加一个菜品到订单
 * 
 * @param ItemGuid
 * @param dom
 */
function AddOrderItem(ItemGuid, MiniMarket)
{
	if(NOW_DATE < END_DATE && NOW_DATE > START_DATE)
	{
		
		$(".addtocart").colorbox({href:'http://x.fandian.com/images/banners/wx_czfj.jpg'});
		
		return false;
	}
	var FORCE_PRE = false;
	MINI_MARKET = typeof(MiniMarket)!='undefined' ? !!MiniMarket : false;
	LAST_ITEM_GUID = ItemGuid.toString().replace('ii_', '').replace('it_', '');
	
	try {
		ORDER_IS_PRE = document.getElementById('OrderExpressTime_1').checked;
		FORCE_PRE = true;
	} catch(e) {
		ORDER_IS_PRE = false
	}
	
	if (Fandian_IsGuid(LAST_ITEM_GUID)) {
		if (ItemIsSoldOut(ItemGuid)) {
			Fandian_Alert('对不起，该菜品今天已经售完，请换换其他的菜吧 >__<');
			return false;
		}

		if (!VENDOR_IN_SERVICE && !ORDER_IS_PRE) {
			Fandian_Alert('现在非该商家的服务时间，你只能通过预订的方式进行下单。');
		} else if (!Fandian_InArray(SERVICE_NAME, SERVICE_NOW_SERVICES) && !ORDER_IS_PRE) {
			sName = SERVICE_NAME=='普通' ? '' : ' '+SERVICE_NAME+' ';
			Fandian_Alert('现在不是我们'+sName+'服务时间，你只能通过预订的方式进行下单。');
		}
		
		//	如果是该商家的第一个菜
		try {
			ThisItem = FindVendorItem(ItemGuid);
			_count = ThisItem.MinOrderQty;
			if (_count<=0) {
				_count = 1;
			}
		} catch(e) {
			_count = 1;
		}

		if (ADDED_THIS_VENDOR) {
			if (!MINI_MARKET && PREPARED_ITEMS.length==0) {
				InitFirstItem();
			} else if (MINI_MARKET && MARKET_PREPARED_ITEMS.length==0) {
				InitFirstItem(MINI_MARKET);
			}
		}

		PrepareAddItem(ItemGuid, _count);

		if (Fandian_InArray(SERVICE_NAME, SERVICE_NOW_SERVICES) && ORDER_IS_PRE==false && !VENDOR_IN_SERVICE) {
			BatchSetPre();
			document.getElementById('OrderExpressTime_1').click();
		} else if (!Fandian_InArray(SERVICE_NAME, SERVICE_NOW_SERVICES) && ORDER_IS_PRE==false) {
			BatchSetPre();
			document.getElementById('OrderExpressTime_1').click();
		}
		
		AddOrderItemRow(LAST_ITEM_GUID, MINI_MARKET);
		//Fandian_SetDomCss('ii_'+LAST_ITEM_GUID, 'item_choosed');
		//Fandian_SetDomCss('it_'+LAST_ITEM_GUID, 'item_choosed_title');
	}
}

/**
 * 切换菜品收藏状态
 * 
 * @param ItemGuid
 * @param dom
 */
function SwitchItemFavoriteStatus(ItemGuid, dom)
{
	LAST_ITEM_GUID = ItemGuid;

	if (Fandian_IsGuid(FANDIAN_UID) && Fandian_IsGuid(ItemGuid)) {
		if (Fandian_InArray(ItemGuid, FAVORITED_ITEMS)) {
			Fandian_SimpleAjax({
				'url': FANDIAN_BASE_URL+'member/favorites/del_item?ItemGuid='+ItemGuid,
				'callback': function(){
					FAVORITED_ITEMS = Fandian_ArrayRemove(LAST_ITEM_GUID, FAVORITED_ITEMS);
					Fandian_SetDomHtml(dom, '收藏');
					Fandian_SetDomCss(dom, 'item_favorite');
				}
			});
		} else {
			Fandian_SimpleAjax({
				'url': FANDIAN_BASE_URL+'member/favorites/add_item?ItemGuid='+ItemGuid,
				'callback': function(){
					FAVORITED_ITEMS.push(LAST_ITEM_GUID);
					Fandian_SetDomHtml(dom, '取消收藏');
					Fandian_SetDomCss(dom, 'item_cancel_favorite');
				}
			});
		}
	} else {
		Fandian_Alert('请先登录！');
	}
}

function SwitchFavoriteStatus(dom)
{
	if (Fandian_IsGuid(FANDIAN_UID) && Fandian_IsGuid(VENDOR_GUID)) {
		if (!VENDOR_IS_FAVORITED) {
			Fandian_SimpleAjax({
				'url': FANDIAN_BASE_URL+'member/favorites/add_vendor?VendorGuid='+VENDOR_GUID,
				'callback': function(){
					VENDOR_IS_FAVORITED = true;
					Fandian_SetDomHtml(dom, '取消收藏');
					Fandian_SetDomCss(dom, 'vendor_cancel_favorite');
					Fandian_IncreaseDomText('vendor_favorites_count');
				}
			});
		} else {
			Fandian_SimpleAjax({
				'url': FANDIAN_BASE_URL+'member/favorites/del_vendor?VendorGuid='+VENDOR_GUID,
				'callback': function(){
					VENDOR_IS_FAVORITED = false;
					Fandian_SetDomHtml(dom, '收藏');
					Fandian_SetDomCss(dom, 'vendor_favorite');
					Fandian_DecreaseDomText('vendor_favorites_count');
				}
			});
		}
	} else {
		Fandian_Alert('请先登录！');
	}
}


function CPreTime(ckey, cval)
{
	if (CheckPreTime()) {
		switch (ckey) {
			case 'pre_year':
				LAST_YEAR_YEAR = cval;
				break;
			case 'pre_month':
				LAST_PRE_MONTH = cval;
				break;
			case 'pre_day':
				LAST_PRE_DAY = cval;
				break;
			case 'pre_hour':
				LAST_PRE_HOUR = cval;
				break;
			case 'pre_minute':
				LAST_PRE_MINUTE = cval;
				break;
		}
		
		eval('ORDER_PARAMS.'+ckey+'='+cval);
		V_SetCookie(ckey, cval);
		return true;
	} else {
		switch (ckey) {
			case 'pre_year':
				Fandian_SetDomValue('order_'+ckey, LAST_YEAR_YEAR);
				break;
			case 'pre_month':
				Fandian_SetDomValue('order_'+ckey, LAST_PRE_MONTH);
				break;
			case 'pre_day':
				Fandian_SetDomValue('order_'+ckey, LAST_PRE_DAY);
				break;
			case 'pre_hour':
				Fandian_SetDomValue('order_'+ckey, LAST_PRE_HOUR);
				break;
			case 'pre_minute':
				Fandian_SetDomValue('order_'+ckey, LAST_PRE_MINUTE);
				break;
		}
		
		Fandian_Alert('预定时间不在商家服务时间范围内，或者不在我们外送的服务时间范围内！请重设。');
		return false;
	}
}

/**
*菜品搜索
*/
 
 function searchItem(val,id)
 {
	 val=$.trim(val);
	 var tag = id=='supermarket'?"#" + id:'#list'+ id;
	 
	 if(val=='')
	 {
		 $(tag +' ul li').show();
	 }
	 else{
		 $(tag + ' ul li').hide();
		 $(tag +' ul').children("li").each(function(){
			 if($(this).attr('item').indexOf(val)!=-1)
			{
				 $(this).show();
			}
		 });
	 }
	 
 }
 
$(function(){
		if (URL_ADD && !URL_ADDED) {
			URL_ADDED = true;
			$('#hidden_add').click();
		}
});
