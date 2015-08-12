<?php
	include('./class.MySQL.php');

	$oMySQL = new MySQL('dropzone', 'root', 'root', '127.0.0.1');

	$query  = "SELECT * FROM sanpham WHERE idsp = 1;";

	$record = $oMySQL->ExecuteSQL($query);

	$imgs 	 = explode(',', $record['imgs']);
	$mainImg = $record['img_main'];

	function showThumb($link)
	{
		$link = str_replace('.jpg', 's.jpg', $link);
		$link = str_replace('.png', 's.png', $link);

		return $link;
	}
?>
<html>
	<head>
		<title>Upload</title>

		<meta http-equiv="content-type" content="text/html;charset=utf-8" />

		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="./css/bootstrap.min.css">
		<link rel="stylesheet" href="./css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="./fancybox/jquery.fancybox.css">
		<link rel="stylesheet" href="./fancybox/helpers/jquery.fancybox-thumbs.css">
		<link rel="stylesheet" href="./fancybox/helpers/jquery.fancybox-buttons.css">
		<link rel="stylesheet" href="./growl/stylesheets/jquery.growl.css">
		<link rel="stylesheet" href="./bootstrap/css/bootstrap.min.css">

		<link rel="stylesheet" href="./css/style2.css">
	</head>

	<body>
		<div class="container" id="container">
			<a href="javascript:triggerFormImages();" class="btn btn-primary btn-large">Chọn hình ảnh</a>

		    <div id="dropzone">
		    	<form class="dropzone" id="demo-upload">
					<?php if(is_array($imgs)){?>
						<?php foreach($imgs as $img){ ?>
							<?php if($img){?>
								<div class="dz-preview dz-processing dz-image-preview <?php if($img == $mainImg){?>dz-active<?php }?>">
									<div class="dz-details" style="margin-bottom: 5px;">
										<a class="fancybox-thumb" rel="fancybox-thumb" href="<?php echo $img;?>">
											<img src="<?php echo showThumb($img);?>" width="160" />
										</a>
									</div>

									<a class="dz-remove" href="javascript:void(0);" onClick="deleteImg('<?php echo $img;?>', this);" style="margin-top: 0;">Xóa ảnh</a>
									<a class="dz-make-main" href="javascript:void(0);" onClick="makeMainImg('<?php echo $img;?>', this);">Ảnh chính</a>
								</div>
							<?php }?>
						<?php }?>
					<?php }?>
		    	</form>
		    </div>
	    </div>

		<script src="./js/jquery-1.11.1.min.js"></script>
		<script src="./js/jquery-migrate-1.2.1.min.js"></script>
		<script src="./js/dropzone.js"></script>
		<script src="./js/resize/binaryajax.js"></script>
		<script src="./js/resize/exif.js"></script>
		<script src="./js/resize/canvasResize.js"></script>
		<script src="./fancybox/jquery.fancybox.pack.js"></script>
		<script src="./fancybox/helpers/jquery.fancybox-thumbs.js"></script>
		<script src="./fancybox/helpers/jquery.fancybox-media.js"></script>
		<script src="./fancybox/helpers/jquery.fancybox-buttons.js"></script>
		<script src="./growl/javascripts/jquery.growl.js"></script>
		<script src="./bootstrap/js/bootstrap.min.js"></script>
		<script src="./jconfirm/jquery.confirm.min.js"></script>

		<script type="text/javascript">
			var idsp = 1;
			Dropzone.autoDiscover = false;

			var myDropzone = new Dropzone($("#demo-upload")[0],{ // Make the whole body a dropzone
				url: "https://api.imgur.com/3/image", // API Imgur
				headers: {
                    'Authorization': 'Client-ID 28aaa2e823b03b1', // Tài khoản Imgur
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                paramName: 'image',
                success: function(file, result){
                    var link = result.data.link;

                    $.get('api.php?mode=add&link='+link+'&idsp='+idsp, function(data){
                		var json = $.parseJSON(data);

                		if(json.success == undefined)
                		{
                			ownAlert('Cập nhật dữ liệu vào database thất bại. Link Imgur = '+link);

                			if (file.previewElement) {
                				$(file.previewElement).find('.dz-remove').attr('href', 'javascript:void(0);').attr('onClick', 'deleteImg("'+result.data.link+'", this, 1); return false;').removeAttr('data-dz-remove');

	                            return file.previewElement.classList.add("dz-error");
	                        }
                		}

                		if(json.success == false)
                		{
                			ownAlert('Cập nhật dữ liệu vào database thất bại. '+json.message);

                			if (file.previewElement) {

                				$(file.previewElement).find('.dz-remove').attr('href', 'javascript:void(0);').attr('onClick', 'deleteImg("'+result.data.link+'", this, 1); return false;').removeAttr('data-dz-remove');

	                            return file.previewElement.classList.add("dz-error");
	                        }
                		}else{
                			ownSuccess('Tải ảnh thành công.');

		                	if(file.previewElement) {
		                        $(file.previewElement).find('.dz-details img').wrap('<a class="fancybox-thumb" rel="fancybox-thumb" href="'+result.data.link+'"></a>');
		                        $(file.previewElement).find('.dz-remove').attr('href', 'javascript:void(0);').attr('onClick', 'deleteImg("'+result.data.link+'", this); return false;').removeAttr('data-dz-remove');
		                        $(file.previewElement).find('.dz-remove').after('<a class="dz-make-main" href="javascript:void(0);" onClick="makeMainImg(\''+result.data.link+'\', this);">Ảnh chính</a>');
		                    }

		                    if (file.previewElement)
		                    {
		                        return file.previewElement.classList.add("dz-success");
		                    }
                		}
                    });
                },
                acceptedFiles: 'image/*', // chỉ chấp nhận file ảnh
				thumbnailWidth: 80,
				thumbnailHeight: 80,
				maxFilesize: 2, // Dung lượng tối đa của ảnh, đơn vị. MB
				parallelUploads: 20, // Số lượng file tối đa có thể upload 1 lần.
				addRemoveLinks: '<a class="dz-remove" href="javascript:undefined;" data-dz-remove="">Remove file</a>'
			});

			$(document).ready(function() {
				$(".fancybox-thumb").fancybox({
					prevEffect	: 'none',
					nextEffect	: 'none',
					helpers	: {
						title	: {
							type: 'outside'
						},
						thumbs	: {
							width	: 50,
							height	: 50
						}
					}
				});
			});

			function deleteImg(link, that, noConfirm)
			{
				if(noConfirm != undefined && noConfirm == 1)
				{
					return _deleteImg(link, that);
				}

				$.confirm({
				    text: "Bạn có chắc chắn muốn xóa ảnh này không ?",
				    confirm: function(button) {
				        _deleteImg(link, that);
				    },
				    cancel: function(button) {
				        // do something
				    }
				});
			}

			function _deleteImg(link, that)
			{
				$.get('api.php?mode=delete&link='+link+'&idsp='+idsp, function(data)
				{
            		var json = $.parseJSON(data);

            		if(json.success == undefined)
            		{
            			ownAlert('Xóa ảnh thất bại. Link Imgur = '+link);
            		}

            		if(json.success == false)
            		{
            			ownAlert('Xóa ảnh thất bại. Lỗi: '+json.message);
            		}else{
            			ownSuccess('Xóa thành công.');

            			$(that).parent().fadeOut();
            		}
                });
			}

			function makeMainImg(link, that)
			{
				$.confirm({
				    text: "Bạn có chắc chắn muốn chọn ảnh này làm ảnh chính không ?",
				    confirm: function(button) {
						$.get('api.php?mode=makeMain&link='+link+'&idsp='+idsp, function(data)
						{
	                		var json = $.parseJSON(data);

	                		if(json.success == undefined)
	                		{
	                			ownAlert('Chọn ảnh chính thất bại. Link Imgur = '+link);
	                		}

	                		if(json.success == false)
	                		{
	                			ownAlert('Chọn ảnh chính thất bại. Lỗi: '+json.message);
	                		}else{
	                			ownSuccess('Thành công.');

	                			$(".dz-active").removeClass('dz-active');
	                			$(that).parent().addClass('dz-active');
	                		}
	                    });
				    },
				    cancel: function(button) {
				        // do something
				    }
				});
			}

			function ownAlert(own_msg)
			{
				$.growl.error({ title: 'Thông báo', message: own_msg });
			}

			function ownSuccess(own_msg)
			{
				$.growl.notice({ title: 'Thông báo', message: own_msg });
			}

			function triggerFormImages()
			{
				$("#demo-upload").trigger('click');
			}
		</script>
	</body>
</html>