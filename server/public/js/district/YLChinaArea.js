(function($) { 
    $.fn.jChinaArea = function(o) {
		o = $.extend({
			aspnet:false,
			s1:null,
			s2:null,
			s3:null
    }, o || {});
        var wrap=$(this);
		var sel=$("select",wrap);
		var sProvince=sel.eq(0);
		var sCity=sel.eq(1);
		var sCounty=sel.eq(2);
		var loc	= new Location();

		sProvince.empty();
		sCity.empty();
		sCounty.empty();
		loc.fillOption(sProvince , '0',o.s1);
		loc.fillOption(sCity , '0,'+sProvince.val(),o.s2);
		loc.fillOption(sCounty , '0,' + sProvince.val() + ',' + sCity.val(),o.s3);
		
		if(o.aspnet){
			var input=$("input",wrap);
			var tProvince=input.eq(0);
			var tCity=input.eq(1);
			var tCounty=input.eq(2);
			writeInput();
		}
		
		sProvince.change(function() {
		sCity.empty();
		loc.fillOption(sCity , '0,'+sProvince.val());
		sCounty.empty();
		loc.fillOption(sCounty , '0,' + sProvince.val() + ',' + sCity.val());
		if(o.aspnet){
			writeInput();
		}
	})
	
	sCity.change(function() {
		sCounty.empty();
		loc.fillOption(sCounty , '0,' + sProvince.val() + ',' + sCity.val());
		if(o.aspnet){
			writeInput();
		}
	})
	sCounty.change(function(){
		if(o.aspnet){
			writeInput();
		}
	})
	
	function writeInput(){
			tProvince.val($(":selected",sProvince).text());
			tCity.val($(":selected",sCity).text());
			tCounty.val($(":selected",sCounty).text());
	}
	};
		
})(jQuery);
