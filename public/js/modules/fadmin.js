function format_size(size) {
	re = '';
	
	if (size >= 1048576) {
		re = (Math.round(size / 1048576 * 100) / 100) + 'MB';
	} else if (size >= 1024) {
		re = (Math.round(size / 1024 * 100) / 100) + 'KB';
	} else {
		re = size + 'B';
	}
	
	return re;
}

function create_editor(params) {
	var editor_id = params.id;
	var container = params.container;
	
	width = params.width ? parseInt(params.width) + 'px' : '800px';
	height = params.height ? parseInt(params.height) + 'px' : '400px';
	allow_file = params.disable_file ? false : true;
	
	_params = params;
	_params.id = editor_id;
	_params.width = width;
	_params.height = height;
	_params.newlineTag = 'br';
	_params.langType = 'zh_CN';
	_params.minWidth = width;
	
	if (typeof(params.resizeType)=='undefined') {
		_params.resizeType = 0;
	}

	KE.init(_params);
	
}

function get_editor_content(key, d) {
	$('#' + d).val(KE.html(key));
}

function bind_attach_sort_and_grag()
{
	$('#attach_list, #attach_list li').disableSelection();
}

function cal_attach_list_orders(id)
{
	aobj = $('#'+id);
	size = aobj.find('li').size();
	
	if (size>0) {
		obj = $('#'+id+'_orders');
		tmp = [];
		
		aobj.find('li').each(function(i){
			tmp.push($(this).attr('fid'));
		});
		
		obj.val(tmp.join(','));
	}
}

function del_attach_row(event)
{
	var fid = $(this).attr('fid');

	if (confirm('确定？')) {
		$.getJSON(BASE_URL+'files/del?fid='+fid, function(){
			$('li[fid="'+fid+'"]').remove().replaceWith('');
			$('input[type="hidden"][value="'+fid+'"]').remove().replaceWith('');
		});
	}
	
	return false;
}

function append_attach_row(d, key)
{
	key = key ? key : 'attach_list';
	
	fid = d.FileId;

	a = $(document.createElement('a')).attr('fid', fid).attr('href', '#').text('删除').bind('click', del_attach_row);
	_d = fid.substr(0,1);
	_d2 = fid.substr(1, 1);
	li = $(document.createElement('li')).attr('fid', fid).append('<a href="'+FANDIAN_STATIC_URL+FANDIAN_ATTACHMENT_ARTICLE_URL+_d+'/'+_d2+'/'+fid+'.'+d.Ext+'" target="_blank">'+d.Name+'</a>&nbsp;'+format_size(d.Size)).append('&nbsp;').append(a);
	ip = $(document.createElement('input')).attr('type', 'hidden').attr('name', 'fids[]').val(fid);

	$('#'+key).append(li);
	
	$('#'+key+'_hide').append(ip);
	
	bind_attach_sort_and_grag();
}

$(function(){
	$('ol.attach_list, ol.attach_list li').disableSelection();
	
	$('ol.attach_list').sortable({
		revert: true,
		stop: function(event, ui) {
			id = $(this).attr('id');
			cal_attach_list_orders(id);
		}
	});
	
	$('input[rel="choose_all"]').click(function(){
		that = $(this);
		form = that.attr('reldata');
		$('#'+form+' input:checkbox').attr('checked', true);
	});
	
	$('input[rel="unchoose_all"]').click(function(){
		that = $(this);
		form = that.attr('reldata');
		$('#'+form+' input:checkbox').attr('checked', false);
	});
	
	$('input[rel="revert_all"]').click(function(){
		that = $(this);
		form = that.attr('reldata');
		$('#'+form+' input:checkbox').each(function(){
			$(this).attr('checked', !$(this).attr('checked'));
		});
	});	
});