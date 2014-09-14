
$(function() {
	$('#videoTable').dataTable({
		"aLengthMenu": [ 5, 10, 20, 25, 50, 100 ],
	});
	$('#add').on('click', function() {
		var item = parseInt($(".upload-item").last().data('item'));
		item++;
		$("form .yt-link").before('<div class="form-group"><label for="" class="control-label col-sm-2">Upload '+item+'</label><small> (Max size 50MB)</small><div class="col-sm-8 input-group"><span class="input-group-addon"><i class="fa fa-file-video-o"></i></span><input type="file" name="upload[]" class="form-control upload-item" data-item="'+item+'"></div></div>');
	});
	$(".embed").on('click', function(){
		var embed = '<iframe src="'+path+'/embed.php?hash='+$(this).data('hash')+'&amp;id='+$(this).data('id')+'" width="510" height="360" scrolling="no" style="overflow:hidden" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
		$("#copy-content").val(embed);
		$(".copy_modal_overlay, .copy_modal").fadeIn();
		$("#copy-content").select().focus();
	});
	$("#copy-content").select();
	$("#copy-content").on('click', function(){
		$(this).select();
	});
	$(".copy_modal .close_copy").on('click', function(){
		$(this).parent().fadeOut();$(".copy_modal_overlay").fadeOut();
	});
});

