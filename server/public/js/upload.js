
/**
 * 需要提供节点：
 * 1、上传按钮（必选） 		fileupload : <input id="fileupload" type="file" name="files[]" multiple>
 * 2、预览效果（可选） 		files : <div id="files" class="files"></div>
 * 3、上传进度条（可选） 		<div id="progress" class="progress" >
 <div class="progress-bar progress-bar-success"></div>
 </div>
 * 4、图片成功路径（必选）
 <input type="hidden" id="image_path" name="image_path">
 * @param domId
 * @param setting
 * @author bzhang
 */
function initUpload(domId ,setting,okFunc){
    if (typeof(settings) == 'undefined') {
        settings = {};
    }
    if (typeof(domId) == 'undefined') {
        domId = "fileupload";
    }
    defaultSetting = {
        url: "/course/upload",
        dataType: 'json',
        formData:{userId:1},
        autoUpload: true,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
        maxFileSize: 5000000, // 5 MB
        disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),
        //previewMaxWidth: 300,
        //previewMaxHeight: 100,
        previewCrop: true
    };
    defaultSetting = jQuery.extend(defaultSetting, setting);
    $('#' + domId).fileupload(
        defaultSetting
    ).on('fileuploadadd', function (e, data) {
            data.context = $('#files').html($('<div/>'));
            $('#progress .progress-bar').css('width','0%');
        }).on('fileuploadprocessalways', function (e, data) {
            var index = data.index,
                file = data.files[index],
                node = $(data.context.children()[index]);
            if (file.preview) {
                //node.prepend('<br>').prepend(file.preview);
            }
            if (file.error) {
                node.append('<br>').append($('<span class="text-danger"/>').text(file.error));
            }
            if (index + 1 === data.files.length) {
                data.context.find('button').text('Upload').prop('disabled', !!data.files.error);
            }
        }).on('fileuploadprogressall', function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css('width',progress + '%');
            $("#files").html('<img target="_blank" src="/img/wap-common/upload.jpg" />')
        }).on('fileuploaddone', function (e, data) {

            $.each(data.result.files, function (index, file) {
                if (file.url) {
                    var img =  $('<img>').prop('src', file.url);
                    data.context.html(img);

                    var link = $('<a>').attr('target', '_blank').prop('href', file.url);
                    $(data.context.children()[index]).wrap(link);

                    $("#image_path").val(file.relativePath + file.name);
                } else if (file.error) {
                    var error = $('<span class="text-danger"/>').text(file.error);
                    $(data.context.children()[index]).append('<br>').append(error);
                }
            });

            if (typeof(okFunc) != 'undefined') {
                okFunc(data.result.files);
            }

        }).on('fileuploadfail', function (e, data) {
            $.each(data.files, function (index, file) {
                var error = $('<span class="text-danger"/>').text('File upload failed.');
                $(data.context.children()[index]).append('<br>').append(error);
            });
        }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

}