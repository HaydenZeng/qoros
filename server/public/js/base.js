$(function(){

});

/**
 * 后台使用的弹窗
 * @param title
 * @param content
 * @param okFunc
 * @param cancelFunc
 * @param options
 * @author bzhang
 */
function showModal(title ,content , okFunc, cancelFunc, options){
    var okBtn = '确定';
    var cancelBtn = '取消';
    if (typeof(options) != 'undefined') {
        if (typeof(options['okBtn']) != 'undefined') {
            okBtn = options['okBtn'];
        }
        if (typeof(options['cancelBtn']) != 'undefined') {
            cancelBtn = options['cancelBtn'];
        }
    }

    $('#site-modal-title').html(title);
    $('#site-modal-content').html(content);

    //1\ close
    $('.close').unbind('click');
    $('.close').click(function(){
        if (typeof(cancelFunc) != 'undefined') {
            cancelFunc();
        }
    });

    //2\ ok
    $('#site-model-ok').unbind('click');
    $('#site-model-ok').click(function(){
        if (typeof(okFunc) != 'undefined') {
            okFunc();
        }
    });

    //3\cancel
    $('#site-model-cancel').unbind('click');
    $('#site-model-cancel').click(function(){
        if (typeof(cancelFunc) != 'undefined') {
            cancelFunc();
        }
    });

    $('#site-modal').modal();
}
