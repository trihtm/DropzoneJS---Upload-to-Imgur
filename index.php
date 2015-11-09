<?php
	require_once('./includes/class.MySQL.php');
	require_once('./includes/class.ProductManager.php');

	$config = array(
		'imgurAPI' => "https://api.imgur.com/3/image",
		'imgurAccount' => '28aaa2e823b03b1',
	);
	$productId = 1;

	$oMySQL = new MysqliDb(
		'127.0.0.1',
		'root',
		'root',
		'dropzone'
	);

	$oMySQL->where('idsp', $productId);
	$product = $oMySQL->getOne('sanpham');

	if (!is_array($product)) {
		throw new Exception('Không lấy được sản phẩm này.', 600);
	}

	$productManager = new ProductManager($oMySQL, $product);

	$imgs 	 = $productManager->getImgs();
	$mainImg = $productManager->getImgMain();
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

		<link rel="stylesheet" href="./js/jquery-ui-1.11.4.custom/jquery-ui.min.css">
		<link rel="stylesheet" href="./js/jquery-ui-1.11.4.custom/jquery-ui.structure.min.css">

		<link rel="stylesheet" href="./css/style2.css">
	</head>

	<body>
		<div class="container" id="container">
			<a href="javascript:triggerFormImages();" class="btn btn-primary btn-large">Chọn hình ảnh</a>

		    <div id="dropzone">
		    	<form class="dropzone" id="demo-upload">
					<?php if (is_array($imgs)) {?>
						<?php foreach ($imgs as $img) { ?>
							<?php if ($img) {?>
								<div class="dz-preview dz-processing dz-image-preview <?php if ($img == $mainImg) {?>dz-active<?php }?>">
									<div class="dz-details" style="margin-bottom: 5px;">
										<a class="fancybox-thumb" rel="fancybox-thumb" href="<?php echo $img;?>">
											<img src="<?php echo $productManager->showImgThumb($img);?>" width="160" />
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

			<?php if (is_array($imgs)) {?>
				<hr />

				<button type="button" onClick="updateAllImgDetails();" class="btn btn-danger">Cập nhật tất cả</button>

				<div id="update-container" style="width: 500px; padding: 20px 0;">
					<?php foreach ($imgs as $img) { ?>
						<?php if ($img) {?>
							<?php $imgDetails = $productManager->getDetailsByImg($img); ?>

							<form action="api.php?idsp=<?php echo $productId;?>&mode=updateDescription" class="form-horizontal" method="POST">
								<input type="hidden" name="link" value="<?php echo $img;?>" />

								<div class="row">
									<div class="col-lg-5">
										<img src="<?php echo $productManager->showImgThumb($img);?>" width="160" />
									</div>

									<div class="col-lg-6">
										<div class="form-group">
											<label for="tieude">Tiêu đề:</label>

											<input type="text" name="tieude" class="form-control" value="<?php echo $imgDetails['tieude'];?>" />
										</div>

										<div class="form-group">
											<label for="tieude">Giá bán:</label>

											<input type="text" name="giaban" class="form-control" value="<?php echo $imgDetails['giaban'];?>" />
										</div>

										<div class="form-group">
											<button type="button" onClick="updateImgDetails(this);" class="btn btn-primary btn-update-details">Đồng ý</button>
										</div>
									</div>
								</div>
							</form>

							<hr />
						<?php }?>
					<?php }?>
				</div>
			<?php }?>
		</div>

		<script src="./js/base64.js"></script>
		<script src="./js/jquery-1.11.1.min.js"></script>
		<script src="./js/jquery-migrate-1.2.1.min.js"></script>
		<script src="./js/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>
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
		<script src="./baivong/jquery.watermark.js"></script>

		<script type="text/javascript">
			/**
			 * Config watermark
			 * @type {{path: string}}
			 */
			var watermarkConfig = {
				gravity: 'center',
				path: 'http://i.imgur.com/LcpZHu5.png'
			};

			var idsp = <?php echo $productId; ?>;
			Dropzone.autoDiscover = false;

			var myDropzone = new Dropzone($("#demo-upload")[0],{ // Make the whole body a dropzone
				url: "<?php echo $config['imgurAPI'];?>", // API Imgur
				headers: {
                    'Authorization': 'Client-ID <?php echo $config['imgurAccount'];?>', // Tài khoản Imgur
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                paramName: 'image',
                success: function(file, result) {
                    var imgurLink = result.data.link;
					var url = buildApiUrl('add', imgurLink);
					
                    $.get(url, function(data) {
                		var json = $.parseJSON(data);

                		if (json.success == undefined) {
                			ownAlert('Cập nhật dữ liệu vào database thất bại. Link Imgur = '+imgurLink);

                			if (file.previewElement) {
                				$(file.previewElement)
									.find('.dz-remove')
									.attr('href', 'javascript:void(0);')
									.attr('onClick', 'deleteImg("'+result.data.link+'", this, 1); return false;')
									.removeAttr('data-dz-remove');

	                            return file.previewElement.classList.add("dz-error");
	                        }
                		}

                		if (json.success == false) {
                			ownAlert('Cập nhật dữ liệu vào database thất bại. '+json.message);

                			if (file.previewElement) {
                				$(file.previewElement)
									.find('.dz-remove')
									.attr('href', 'javascript:void(0);')
									.attr('onClick', 'deleteImg("'+result.data.link+'", this, 1); return false;')
									.removeAttr('data-dz-remove');

	                            return file.previewElement.classList.add("dz-error");
	                        }
                		} else {
                			ownSuccess('Tải ảnh thành công.');

		                	if (file.previewElement) {
		                        $(file.previewElement)
									.find('.dz-details img')
									.wrap('<a class="fancybox-thumb" rel="fancybox-thumb" href="'+result.data.link+'"></a>');

								$(file.previewElement)
									.find('.dz-remove')
									.attr('href', 'javascript:void(0);').attr('onClick', 'deleteImg("'+result.data.link+'", this); return false;')
									.removeAttr('data-dz-remove');

								$(file.previewElement)
									.find('.dz-remove')
									.after('<a class="dz-make-main" href="javascript:void(0);" onClick="makeMainImg(\''+result.data.link+'\', this);">Ảnh chính</a>');
		                    }

		                    if (file.previewElement) {
		                        var status = file.previewElement.classList.add("dz-success");

								return status;
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

				// form dropzone sortable
				var form = $("form.dropzone");

				form.find('.dz-message').addClass('ui-state-disabled').removeClass('ui-sortable-handle');
				form.sortable({
					cancel: ".ui-state-disabled",
					update: function( event, ui ) {
						updateImg();
					}
				});
				form.disableSelection();
			});

			function buildApiUrl(mode, link) {
				return 'api.php?mode='+mode+'&link='+link+'&idsp='+idsp;
			}

			function updateImg()
			{
				var url = buildApiUrl('updatePosition');

				var links = [];
				$(".dz-image-preview").each(function() {
					var imgurLink = $(this).find('a.fancybox-thumb').attr('href');

					links.push(imgurLink);
				});

				links = links.join(',');
				links = Base64.encode(links);

				$.post(url, {
					'linksData': links
				}, function(data) {
					var json = $.parseJSON(data);

					if (json.success == undefined) {
						ownAlert('Sắp xếp ảnh thất bại. Lỗi: '+json.message);
					}

					if (json.success == false) {
						ownAlert('Sắp xếp ảnh thất bại. Lỗi: '+json.message);
					}
				});
			}

			function updateImgDetails(button)
			{
				var form = $(button).closest('form');

				$.post(form.attr('action'), form.serialize(), function (data) {
					var json = $.parseJSON(data);

					console.log(json);

					if (json.success == undefined) {
						ownAlert('Cập nhật chi tiết ảnh thất bại. Lỗi: '+json.message);

						return;
					}

					if (json.success == false) {
						ownAlert('Cập nhật chi tiết ảnh thất bại. Lỗi: '+json.message);

						return;
					}

					ownSuccess(json.message);
				});
			}

			function updateAllImgDetails()
			{
				$(".btn-update-details").each(function () {
					updateImgDetails(this);
				});
			}

			function deleteImg(imgurLink, that, noConfirm) {
				if (noConfirm != undefined && noConfirm == 1) {
					return _deleteImg(imgurLink, that);
				}

				$.confirm({
				    text: "Bạn có chắc chắn muốn xóa ảnh này không ?",
				    confirm: function(button) {
				        _deleteImg(imgurLink, that);
				    }
				});
			}

			function _deleteImg(imgurLink, that) {
				var url = buildApiUrl('delete', imgurLink);

				$.get(url, function(data) {
            		var json = $.parseJSON(data);

            		if (json.success == undefined) {
            			ownAlert('Xóa ảnh thất bại. Link Imgur = '+imgurLink);
            		}

            		if (json.success == false) {
            			ownAlert('Xóa ảnh thất bại. Lỗi: '+json.message);
            		} else {
            			ownSuccess('Xóa thành công.');

            			$(that).parent().remove();
            		}
                });
			}

			function makeMainImg(link, that) {
				$.confirm({
				    text: "Bạn có chắc chắn muốn chọn ảnh này làm ảnh chính không ?",
				    confirm: function(button) {
						var url = buildApiUrl('makeMain', link);

						$.get(url, function(data) {
	                		var json = $.parseJSON(data);

	                		if (json.success == undefined) {
	                			ownAlert('Chọn ảnh chính thất bại. Link Imgur = '+link);
	                		}

	                		if (json.success == false) {
	                			ownAlert('Chọn ảnh chính thất bại. Lỗi: '+json.message);
	                		} else {
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

			function ownAlert(own_msg) {
				$.growl.error({ title: 'Thông báo', message: own_msg });
			}

			function ownSuccess(own_msg) {
				$.growl.notice({ title: 'Thông báo', message: own_msg });
			}

			function triggerFormImages() {
				$("#demo-upload").trigger('click');
			}
		</script>
	</body>
</html>