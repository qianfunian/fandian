/**
 * 特价套餐
 * 
 */
var ADDED_THIS_VENDOR = true;
var PREVENT_DEFAULT = false;
var LAST_ITEM_COUNT = 0;
var JS_ONE_DAY = 3600*24*1000;
var TYPE_Y_POS = [];
var FIXED_OFFSET = 0;

function Tuan_FindPVendor(VendorGuid)
{
	Vendor = [];
	
	for (i in CITEMS) {
		if (CITEMS[i][0]==VendorGuid) {
			Vendor = CITEMS[i];
			break;
		}
	}

	return Vendor;
}

function Tuan_FindVendor(VendorGuid)
{
	Vendor = [];
	
	for (i in TUAN_ITEMS) {
		if (TUAN_ITEMS[i][0]==VendorGuid) {
			Vendor = TUAN_ITEMS[i];
			break;
		}
	}
	
	return Vendor;
}

function FindVendorItem(VendorGuid, ItemGuid)
{
	_item = null;
	Vendor = Tuan_FindVendor(VendorGuid);
	VENDOR_ITEMS = Vendor[3];

	for(var i=0;i<VENDOR_ITEMS.length;i++) {
		if (VENDOR_ITEMS[i].ItemGuid==ItemGuid) {
			_item = VENDOR_ITEMS[i];
			break;
		}
	}

	return _item;
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

function Tuan_SetCITEMS(VendorGuid, items)
{
	for (i in CITEMS) {
		if (CITEMS[i][0]==VendorGuid) {
			CITEMS[i][1] = items;
			break;
		}
	}
}

function PrepareReduceItem(VendorGuid, ItemGuid, count)
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
	Fandian_SetCookie('gift_items', arr.join(','));
	
	narr = [];
	vendor = Tuan_FindPVendor(VendorGuid);
	for (i in vendor[1]) {
		if (vendor[1][i][0]==ItemGuid) {
			c = vendor[1][i][1] - count;
			if (c>0) {
				narr.push([vendor[1][i][0], c]);
			}
		} else if (vendor[1][i][0] && vendor[1][i][1]) {
			narr.push([vendor[1][i][0], vendor[1][i][1]]);
		}
	}

	Tuan_SetCITEMS(VendorGuid, narr);
}

/**
 * 减少订单菜品数量
 * @param ItemGuid
 */
function Tuan_ReduceItem(VendorGuid, ItemGuid)
{
	var ThisItem = null;
	
	try {
		ThisItem = FindVendorItem(VendorGuid, ItemGuid);
		_count = ThisItem.MinOrderQty;
	} catch(e) {
		_count = 1;
	}

	if (Fandian_IsGuid(ThisItem.ItemGuid) && ThisItem) {
		LAST_ITEM_COUNT = parseInt(document.getElementById('order_vendor_item_count_'+ThisItem.ItemGuid).innerHTML);
		
		if (LAST_ITEM_COUNT>ThisItem.MinOrderQty || (LAST_ITEM_COUNT<=ThisItem.MinOrderQty && confirm('确定要删除这道菜吗？'))) {
			PrepareReduceItem(VendorGuid, ThisItem.ItemGuid, _count);
			
			if (LAST_ITEM_COUNT==ThisItem.MinOrderQty) {
				Fandian_RemoveDom('order_vendor_item_'+ThisItem.ItemGuid);

				PREPARED_ITEMS = Fandian_ArrayRemove(ThisItem.ItemGuid, PREPARED_ITEMS);
			} else if (ThisItem) {
				Fandian_SetDomHtml('order_vendor_item_count_'+ThisItem.ItemGuid, LAST_ITEM_COUNT-ThisItem.MinOrderQty);
				Fandian_SetDomHtml('order_vendor_item_price_'+ThisItem.ItemGuid, (LAST_ITEM_COUNT-ThisItem.MinOrderQty)*ThisItem.UnitPrice);
			}

			boxes = parseInt(document.getElementById('order_boxes_'+VendorGuid).innerHTML);
			boxes_price = parseFloat(document.getElementById('order_boxes_price_'+VendorGuid).innerHTML);
			total = parseFloat(document.getElementById('order_total_'+VendorGuid).innerHTML);

			Fandian_SetDomHtml('order_boxes_'+VendorGuid, boxes - parseInt(ThisItem.BoxQty));
			Fandian_SetDomHtml('order_boxes_price_'+VendorGuid, boxes_price - parseFloat(ThisItem.BoxQty*ThisItem.BoxUnitPrice));
			Fandian_SetDomHtml('order_total_'+VendorGuid, total-parseFloat(ThisItem.BoxQty*ThisItem.BoxUnitPrice)-parseFloat(ThisItem.MinOrderQty*ThisItem.UnitPrice));					
		
			lis = document.getElementById('order_vendor_items_'+VendorGuid).getElementsByTagName('li');
			
			if (lis.length<=5) {
				PREPARED_ITEMS = [];
				Fandian_RemoveDom('order_vendor_'+VendorGuid);
			}
		}
		
		try {
			pl2r();
		} catch(e) {
			
		}				
	}
}

function CPreTime(ckey, cval)
{
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
	Fandian_SetCookie(ckey, cval);
	return true;
}

function AddOrderItemRow(VendorGuid, ItemGuid)
{
	ThisItem = null;
	
	try {
		ThisItem = FindVendorItem(VendorGuid, ItemGuid);
	} catch(e) {}

	if (ThisItem!=null) {
		wp = document.getElementById('order_vendor_items_'+VendorGuid);
		pv = Tuan_FindPVendor(VendorGuid);

		count = ThisItem.MinOrderQty;
		if (!Fandian_InArray(ItemGuid, PREPARED_ITEMS)) {
			html = "<li id='order_vendor_item_"+ThisItem.ItemGuid+"' class='order_vendor_item'>";
			html += "<ul class='order_vendor_item_row'>";
			html += "<li class='order_vendor_item_row_1'>"+ThisItem.ItemName+"</li>";
			html += "<li class='order_vendor_item_row_2'><span id='order_vendor_item_count_"+ThisItem.ItemGuid+"'>"+ThisItem.MinOrderQty+"</span> "+ThisItem.UnitName+"</li>";
			html += "<li class='order_vendor_item_row_3'><span id='order_vendor_item_price_"+ThisItem.ItemGuid+"'>"+ThisItem.MinOrderQty*ThisItem.UnitPrice+"</span></li>";
			html += "<li class='order_vendor_item_row_4'>";
			html += "<a href='javascript:void(0);' class='add_order_item' onclick=\"Tuan_AddItem('"+VendorGuid+"', '"+ThisItem.ItemGuid+"', 'false');\">+</a>\n";
			html += "<a href='javascript:void(0);' class='reduce_order_item' onclick=\"Tuan_ReduceItem('"+VendorGuid+"', '"+ThisItem.ItemGuid+"', 'false');\">-</a>";
			html += "</li>";
			html += "</ul>";
			html += "</li>";

			Fandian_AppendHtml(wp, html);

			PREPARED_ITEMS.push(ThisItem.ItemGuid);
			pv[1].push({
				'ItemGuid': ThisItem.ItemGuid,
				'count': count
			});
		} else {
			count = parseInt(parseInt(document.getElementById('order_vendor_item_count_'+ThisItem.ItemGuid).innerHTML)+parseInt(ThisItem.MinOrderQty));
			
			Fandian_SetDomHtml('order_vendor_item_count_'+ThisItem.ItemGuid, count);
			Fandian_SetDomHtml('order_vendor_item_price_'+ThisItem.ItemGuid, count*ThisItem.UnitPrice);
		}
		
		boxes = parseInt(document.getElementById('order_boxes_'+VendorGuid).innerHTML);
		boxes_price = parseFloat(document.getElementById('order_boxes_price_'+VendorGuid).innerHTML);
		total = parseFloat(document.getElementById('order_total_'+VendorGuid).innerHTML);

		Fandian_SetDomHtml('order_boxes_'+VendorGuid, parseInt(parseInt(ThisItem.BoxQty)+parseInt(boxes)));
		Fandian_SetDomHtml('order_boxes_price_'+VendorGuid, parseFloat(parseFloat(ThisItem.BoxQty*ThisItem.BoxUnitPrice)+parseFloat(boxes_price)));
		Fandian_SetDomHtml('order_total_'+VendorGuid, parseFloat(total+parseFloat(ThisItem.BoxQty*ThisItem.BoxUnitPrice)+parseFloat(ThisItem.MinOrderQty*ThisItem.UnitPrice)));
		
		try {
			pl2r();
		} catch(e) {
			
		}
	}
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
	return true;
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
 * 批量设置预定相关参数
 * 
 */
function BatchSetPre()
{
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
	}
	
	PREVENT_DEFAULT = false;
}

function PrepareAddItem(VendorGuid, ItemGuid, count)
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
	
	vendor = Tuan_FindPVendor(VendorGuid);
	founded = false;
	for (i in vendor[1]) {
		if (vendor[1][i][0]==ItemGuid) {
			vendor[1][i][1] += count;
			founded = true;
			break;
		}
	}
	
	if (!founded) {
		vendor[1].push([ItemGuid, count]);
	}

	Fandian_SetCookie('gift_items', arr.join(','));
}

/**
 * 清理订单数据
 */
function ClearOrder()
{
	if (confirm('确定吗？')) {
		Fandian_SetCookie('gift_items', '');	
		Fandian_SetCookie('remarks', '');
		
		dom = document.getElementById('order_vendors');

		for (i in CITEMS) {
			CITEMS[i][1] = [];
		}

		ORDER_PARAMS.items = [];
		PREPARED_ITEMS = [];
		Fandian_SetDomHtml('order_vendors', '');
		
		try {
			pl2r();
		} catch(e) {
			
		}
	}
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
	Fandian_SetCookie('paymethod', PayMethod);
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

		Fandian_SetCookie('pre_year', pobj.pre_year);
		Fandian_SetCookie('pre_month', pobj.pre_month);
		Fandian_SetCookie('pre_day', pobj.pre_day);
		Fandian_SetCookie('pre_hour', pobj.pre_hour);
		Fandian_SetCookie('pre_minute', pobj.pre_minute);

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
	Fandian_SetCookie('express_setting', ExpressTimeSetting);
	
	return true;
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
	
	Fandian_SetCookie('remarks', arr.join('[]'));
}

function SetService()
{
	now = new Date();
	nt = now.getTime();
	gotted = false;
	
	for(i in SERVICES) {
		if (!gotted) {
			start_hour = SERVICES[i].start.toString().substr(0,2);
			start_minute = SERVICES[i].start.toString().substr(3,2);
			end_hour = SERVICES[i].end.toString().substr(0,2);
			end_minute = SERVICES[i].end.toString().substr(3,2);
			
			sdate = new Date();
			sdate.setHours(start_hour);
			sdate.setMinutes(parseInt(start_minute));
			sdate.setSeconds(0);
			
			edate = new Date();
			edate.setHours(end_hour);
			edate.setMinutes(parseInt(end_minute));
			edate.setSeconds(0);
			
			st = sdate.getTime();
			ed = edate.getTime();
			
			if (st>ed) {
				if (nt>st && nt<ed) {
					Fandian_SetCookie('service', SERVICES[i].guid);
					gotted = true;
				}
			} else {
				ed += parseInt(JS_ONE_DAY);
				if (nt>st && nt<ed) {
					Fandian_SetCookie('service', SERVICES[i].guid);
					gotted = true;
				}
			}
		}
	}
}

function InitFirstItem(VendorGuid)
{
	Vendor = Tuan_FindVendor(VendorGuid);
	_this_vendor_name = Vendor[1];
	_this_vendor_guid = Vendor[0];
	_this_vendor_freight = Vendor[2];

	wp = document.getElementById('order_vendors');
	hidden_dom = document.getElementById('order_vendor_'+_this_vendor_guid);
	
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
	html += "<span style='float:left;margin-left:10px;width:100px'>外送费：￥<span id='order_freight_"+_this_vendor_guid+"'>"+_this_vendor_freight+"</span></span>";
	html += "<span style='float:right;margin-right:10px;'>打包盒：<span id='order_boxes_"+_this_vendor_guid+"'>0</span>，共：￥<span id='order_boxes_price_"+_this_vendor_guid+"'>0</span></span>";
	html += "</li>";
	html += "<li style='clear: both;margin-left: 10px;'>";
	html += "合计：￥<span id='order_total_"+_this_vendor_guid+"'>"+_this_vendor_freight+"</span>";
	html += "</li>";
	html += "<li style='clear: both;margin-left: 10px;'>";
	html += "备注：<input type='text' onblur=\"SaveRemark(this.value, '"+_this_vendor_guid+"');\" name='order_remark_"+_this_vendor_guid+"' id='order_remark_"+_this_vendor_guid+"' value='' size='30' />";
	html += "</li>";
	html += "</ol>";
	
	html += hidden_dom ? '' : "</li>";
	
	if (hidden_dom) {
		Fandian_SetDomHtml('order_vendor_'+_this_vendor_guid, html);
		html = "<li id='order_vendor_"+_this_vendor_guid+"'>"+html+"</li>";
	}
	
	SetService();
	
	Fandian_AppendHtml(wp, html);
}

function Tuan_AddItem(VendorGuid, ItemGuid)
{
	if($.trim($('#order_vendors').html()) != '')
	{
		if($("#order_vendors li").attr('id') != 'order_vendor_'+VendorGuid)
		{
			alert("抱歉，当前商品与购物车中的商品不属于同一商家");
			return false;
		}
	}
	var FORCE_PRE = false;
	
	try {
		ORDER_IS_PRE = document.getElementById('OrderExpressTime_1').checked;
		FORCE_PRE = true;
	} catch(e) {
		ORDER_IS_PRE = false
	}

	if (Fandian_IsGuid(ItemGuid)) {
		//	如果是该商家的第一个菜
		try {
			ThisItem = FindVendorItem(VendorGuid, ItemGuid);
			_count = ThisItem.MinOrderQty;
			if (_count<=0) {
				_count = 1;
			}
		} catch(e) {
			_count = 1;
		}

		pv = Tuan_FindPVendor(VendorGuid);
		if (!pv || pv[1].length==0) {
			InitFirstItem(VendorGuid);
		}

		PrepareAddItem(VendorGuid, ItemGuid, _count);

		if (ORDER_IS_PRE==false && !VENDOR_IN_SERVICE) {
			BatchSetPre();
			document.getElementById('OrderExpressTime_1').click();
		}
		
		AddOrderItemRow(VendorGuid, ItemGuid);
	}
}
/**
 * 礼品卡验证
 */
var is_ok = fase;
function Validate()
{
	var giftcode = $("#giftcode").val();
	var text = '';
	$.ajax({
		type: "POST",
		dataType : "text",
		data:{"giftcode":giftcode},
		async : false,
		url: FANDIAN_BASE_URL+'welfare/validate?giftcode='+giftcode,
		success: function(res){
			if(res==1)
			{
				text = "抱歉，验证失败，请重新输入";
				is_ok = false;
			}else if(res==2)
			{
				text = "抱歉，您的礼品卡已使用";
				is_ok = false;
			}else
			{
				text = res;
				is_ok = true;
				$('#validate').css({'color':'#3c3'})
			}
			$('#validate').html('');
			$('#validate').html(text);
		},
		error : function(res,msg,err) {
			alert("验证失败，请联系网站管理员，谢谢！");
		}
	});
}
/**
 * 提交订单
 */

function SubmitOrder()
{
	if(is_ok)
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
	
	/*	if (ORDER_IS_PRE==true && !CheckPreTime()) {
			ErrorMsg += spliter+'您设定的预定时间不在商家的营业时间范围内！';
		}*/
	
		if (PREPARED_ITEMS.length==0 && Fandian_Trim(document.getElementById('order_vendors').innerHTML)=='') {
			ErrorMsg += spliter+'请先选择要点的菜品！';
		}
		
		if (ErrorMsg!='') {
			Fandian_Alert('订单提交失败！请检查您填写的信息。'+spliter+spliter+"==========="+spliter+spliter+ErrorMsg);
		} else {
			Fandian_WaitAjax(function(){
				Fandian_AjaxLoading();
				window.location = FANDIAN_BASE_URL+'order/confirm?from=gift&service=生日卡&giftcode='+$('#giftcode').val();
			});
		}
	}else
	{
		return false;
	}
}
