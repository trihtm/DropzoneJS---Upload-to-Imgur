<?php

require_once('./includes/class.MySQL.php');
require_once('./includes/class.ProductManager.php');

class Api
{
	/**
	 * @var string $imgurLink
	 */
	protected $imgurLink;

	/**
	 * @var array $config
	 */
	protected $config;

	/**
	 * @var ProductManager $productManager
	 */
	protected $productManager;

	public function __construct()
	{
		$this->config = array(
			'limitUploadImgs' => 10,
		);

		# Xử lý query sản phẩm
		$productId = (int) $_GET['idsp'];

		if ($productId == 0) {
			throw new Exception('ID sản phẩm không hợp lệ.', 600);
		}

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

		$this->productManager = new ProductManager($oMySQL, $product);

		switch ($_GET['mode']) {
			case 'add':
				$this->checkImgurLink();
				$this->add();
				break;
			case 'delete':
				$this->checkImgurLink();
				$this->delete($product);
				break;
			case 'makeMain':
				$this->checkImgurLink();
				$this->makeMain();
				break;
			case 'updatePosition':
				$this->updatePosition();
				break;
			default:
				throw new Exception('Thao tác thất bại.', 600);
				break;
		}
	}

	public function add()
	{
		# Kiểm tra xem bao nhiêu ảnh đã được upload
		$maxImgs = $this->config['limitUploadImgs'];
		$currentImgs = $this->productManager->countImgs();

		if ($currentImgs > $maxImgs) {
			throw new Exception('Bạn chỉ có thể tải lên tối đa '.$maxImgs.' hình ảnh. Không thể tải thêm.', 600);
		}

		$this->productManager->addimg($this->imgurLink);

		if ($this->productManager->save()) {
			die(json_encode(array(
				'success' => true,
				'message' => 'Cập nhật thành công.'
			)));
		}

		throw new Exception('Cập nhật thất bại.', 600);
	}

	/**
	 * @param $product
	 * @throws Exception
	 */
	public function delete($product)
	{
		$this->productManager->deleteImg($this->imgurLink);

		if ($product['img_main'] == $this->imgurLink) {
			// Set Img Main null
			$this->productManager->setImgMain(NULL);
		}

		if ($this->productManager->save()) {
			die(json_encode(array(
				'success' => true,
				'message' => 'Xóa thành công.'
			)));
		}

		throw new Exception('Xóa thất bại.', 600);
	}

	/**
	 * @throws Exception
	 */
	public function makeMain()
	{
		$this->productManager->setImgMain($this->imgurLink);

		if ($this->productManager->save()) {
			die(json_encode(array(
				'success' => true,
				'message' => 'Chọn ảnh chính thành công.'
			)));
		}

		throw new Exception('Chọn ảnh chính thất bại.', 600);
	}

	public function updatePosition()
	{
		$linksData = $_POST['linksData'];
		$links = json_decode($linksData);

		$success = false;

		if (count($links) > 0) {
			$this->productManager->deleteAllImgs();
			$this->productManager->addImgs($links);

			if ($this->productManager->save()) {
				$success = true;
			}
		} else {
			$success = true;
		}

		if ($success) {
			die(json_encode(array(
				'success' => true,
				'message' => 'Sắp xếp ảnh thành công.'
			)));
		}

		throw new Exception('Sắp xếp ảnh thất bại.', 600);
	}

	protected function checkImgurLink()
	{
		# Xử lý đường link
		if (!isset($_GET['link'])) {
			throw new Exception('Vui lòng nhập đường link', 600);
		}

		$this->imgurLink = $_GET['link'];

		$this->productManager->checkImgurLink($this->imgurLink);
	}
}

try {
	$api = new Api();
} catch(Exception $e) {
	$message = $e->getMessage();

	die(json_encode(array(
		'success' => false,
		'message' => $message)
	));
}