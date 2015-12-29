
/**
 * 地区点击下拉按钮动作
 * add by bzhang
 * @author xuhui
 */
var SelectOne = {
	showOptions : function(elem, e) {
		var ul = $(elem).next();
		var lis = ul.children();
		if (lis.length > 8) {
			ul.css('height', '180px');
			ul.css('overflow-y', 'auto');
		} else {
			ul.css('height', 'auto');
		}
		var i = 0;
		var selected = ul.next().val();
		for (i in lis) {
			// todo highlight selected
		}
		;
		ul.css('display', '');
		if ($(elem).parent().siblings().find('ul')) {
			$(elem).parent().siblings().find('ul').hide();
		}
		ul.show();
		if (e.stopPropagation)
			e.stopPropagation(); // DOM Level 2
		else
			e.cancelBubble = true; // IE
		this.bindUnselectEvent(ul);
	},

	pick : function(li, onchangeCallback) {
		var val = $(li).data('value');
		$(li).parent().next().attr('value', val);
		$(li).parent().prev().find('.selected').html($(li).text());
		$(li).parent().prev().find('.selected').attr('title',$(li).text());
		if ($(li).data('value')) {
			$(li).parent().prev().find('.selected').data('value', $(li).data('value'));
		}
		$(li).parent().hide();

		if (typeof (onchangeCallback) == 'undefined') {
			var func = $(li).parent().attr('onPick');
			onchangeCallback = function() {
				var v = val;
				eval(func);
			}
		}
		if (typeof (onchangeCallback) != 'undefined') {
			onchangeCallback(val);
		}
	},

	bindUnselectEvent : function(ul) {
		$(document).bind("click", function(e) {
			if (e.target != ul) {
				$(ul).hide();
			}
		});
	},

	unbindUnselectEvent : function() {
		$(document).unbind("click");
	}
};