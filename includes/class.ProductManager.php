<?php

class ProductManager
{
    /**
     * @var string $encodeData
     */
    protected $encodeData;

    /**
     * @var Array $imgs
     */
    protected $imgs;

    /**
     * @var MysqliDb $db
     */
    protected $db;

    /**
     * @var Array $product
     */
    protected $product;

    /**
     * Constructor
     * @param MysqliDb $db
     * @param $product
     */
    public function __construct(MysqliDb $db, $product)
    {
        $this->db = $db;
        $this->product = $product;

        $this->setEncodeData($product['imgs']);
        $this->parseEncodeDataToImgs();
    }

    /**
     * Set json data
     * @param $encodeData
     * @return $this
     */
    public function setEncodeData($encodeData)
    {
        $this->encodeData = $encodeData;

        return $this;
    }

    /**
     * Get json data
     * @return string
     */
    public function getEncodeData()
    {
        return $this->encodeData;
    }

    /**
     * Parse json data to imgs
     */
    public function parseEncodeDataToImgs()
    {
        $this->imgs = unserialize($this->getEncodeData());
    }

    /**
     * Update json data
     */
    public function updateEncodeData()
    {
        $newEncodeData = serialize($this->imgs);

        $this->setEncodeData($newEncodeData);
    }

    /**
     * Get main img
     * @return string
     */
    public function getImgMain()
    {
        return $this->product['img_main'];
    }

    /**
     * @param string $img_main
     * @return $this
     */
    public function setImgMain($img_main)
    {
        $this->product['img_main'] = $img_main;

        return $this;
    }

    /**
     * Show imgur thumbnail
     * @param $imgurLink
     * @return string
     */
    public function showImgThumb($imgurLink)
    {
        $imgurLink = str_replace('.jpg', 's.jpg', $imgurLink);
        $imgurLink = str_replace('.png', 's.png', $imgurLink);

        return $imgurLink;
    }

    /**
     * Check imgur link
     * @param $imgurLink
     * @throws Exception
     */
    public function checkImgurLink($imgurLink)
    {
        $source = parse_url($imgurLink);

        if (!isset($source['host'])) {
            throw new Exception('Đường link không hợp lệ.', 600);
        }

        if (strpos($source['host'], 'imgur.com') === FALSE) {
            throw new Exception('Đường link không hợp lệ.', 600);
        }

        $source_path = $source['path'];

        if (!preg_match("/\/([a-zA-z0-9]){7}\.(png|jpg)/", $source_path)) {
            throw new Exception('Đường link imgur không hợp lệ.', 600);
        }
    }

    /**
     * Get all imgs
     * @return Array
     */
    public function getImgs()
    {
        return $this->imgs;
    }

    /**
     * Count imgs
     * @return int
     */
    public function countImgs()
    {
        return count($this->imgs);
    }

    /**
     * @param string $img
     * @return $this
     * @throws Exception
     */
    public function addImg($img)
    {
        if ($this->imgs) {
            # Prevent from duplication.
            $listImgs = array_values($this->imgs);

            if (in_array($img, $listImgs)) {
                throw new Exception("Ảnh này đã được upload lên hệ thống.", 600);
            }
        }

        $this->imgs[] = $img;

        return $this;
    }

    /**
     * @param $imgs
     * @return $this
     */
    public function addImgs($imgs)
    {
        foreach ($imgs as $img) {
            try {
                $this->checkImgurLink($img);

                $this->addImg($img);
            } catch (Exception $e) {
                continue;
            }
        }

        return $this;
    }

    /**
     * @param string $img
     * @return $this
     */
    public function deleteImg($img)
    {
        if (count($this->imgs) > 0) {
            foreach ($this->imgs as $index => $item) {
                if ($item == $img) {
                    unset($this->imgs[$index]);
                }
            }
        }

        return $this;
    }

    /**
     * Delete all imgs
     * @return $this
     */
    public function deleteAllImgs()
    {
        $this->imgs = array();

        return $this;
    }

    public function save()
    {
        $this->updateEncodeData();

        $data = array(
            'imgs' => $this->getEncodeData(),
            'img_main' => $this->getImgMain(),
        );
        
        $this->db->where('idsp', $this->product['idsp']);

        // Update success
        if ($this->db->update('sanpham', $data)) {
            return true;
        }

        // Update failed
        return false;
    }
}