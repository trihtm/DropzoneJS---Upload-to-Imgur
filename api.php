<?php

include('./class.MySQL.php');

try{
	# Xử lý đường link
	if(!isset($_GET['link']))
	{
		throw new Exception('Vui lòng nhập đường link', 600);
	}

	$link = $_GET['link'];

	$source = parse_url($link);

	if(!isset($source['host']))
	{
		throw new Exception('Đường link không hợp lệ.', 600);
	}

	if(strpos($source['host'], 'imgur.com') === FALSE)
	{
		throw new Exception('Đường link không hợp lệ.', 600);
	}

	$source_path = $source['path'];

	if(!preg_match("/\/([a-zA-z0-9]){7}\.(png|jpg)/", $source_path))
	{
		throw new Exception('Đường link imgur không hợp lệ.', 600);
	}

	# Xử lý query sản phẩm
	$id_sp = (int) $_GET['idsp'];

	if($id_sp == 0)
	{
		throw new Exception('ID sản phẩm không hợp lệ.', 600);
	}

	$oMySQL = new MySQL('dropzone', 'root', 'root', '127.0.0.1');

	$query  = "SELECT * FROM sanpham WHERE idsp = ".$id_sp.";";

	$record = $oMySQL->ExecuteSQL($query);

	if(!is_array($record))
	{
		throw new Exception('Không lấy được sản phẩm này.', 600);
	}

	# Xử lý mode
	$mode = $_GET['mode'];

	if($mode == 'add')
	{
		# Kiểm tra xem bao nhiêu ảnh đã được upload
		$maxImgs = 10;
		$currentImgs = count(explode(',', $record['imgs']));

		if($currentImgs > $maxImgs)
		{
			throw new Exception('Bạn chỉ có thể tải lên tối đa '.$maxImgs.' hình ảnh. Không thể tải thêm.', 600);
		}

		# Kiểm tra trùng lặp
		if(strpos($record['imgs'], $link) !== FALSE)
		{
			throw new Exception("Ảnh này đã được upload lên hệ thống.", 600);
		}

		# Insert
		$query = "UPDATE sanpham SET imgs = CONCAT(IFNULL(imgs, ''), '".mysql_real_escape_string($link).",') WHERE idsp = ".$id_sp;

		if($oMySQL->ExecuteSQL($query))
		{
			die(json_encode(array('success' => true, 'message' => 'Cập nhật thành công.')));
		}

		throw new Exception('Cập nhật thất bại.', 600);
	}elseif($mode == 'delete')
	{
		$query = "UPDATE sanpham SET imgs = REPLACE(imgs, '".mysql_real_escape_string($link).",', '') WHERE idsp = ".$id_sp.";";

		if($oMySQL->ExecuteSQL($query))
		{
			if($record['img_main'] == $link)
			{
				$query = "UPDATE sanpham SET img_main = NULL WHERE idsp = ".$id_sp.";";

				$oMySQL->ExecuteSQL($query);
			}

			die(json_encode(array('success' => true, 'message' => 'Xóa thành công.')));
		}

		throw new Exception('Xóa thất bại.', 600);
	}elseif($mode == 'makeMain')
	{
		$query = "UPDATE sanpham SET img_main = '".mysql_real_escape_string($link)."' WHERE idsp = ".$id_sp.";";

		if($oMySQL->ExecuteSQL($query))
		{
			die(json_encode(array('success' => true, 'message' => 'Chọn ảnh chính thành công.')));
		}

		throw new Exception('Chọn ảnh chính thất bại.', 600);
	}

	throw new Exception('Thao tác thất bại.', 600);
}catch(Exception $e){
	$message = $e->getMessage();

	die(json_encode(array('success' => false, 'message' => $message)));
}