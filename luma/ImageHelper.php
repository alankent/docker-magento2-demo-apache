<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\SampleData\Model\Setup\Catalog\ConfigurableProduct;

class ImageHelper
{
    protected $mediaConfig;

    protected $colorMap = [
        'YE' => 'Yellow',
        'GR' => 'Grey',
        'BL' => 'Blue',
        'BK' => 'Black',
        'OR' => 'Orange',
        'PK' => 'Pink',
        'WT' => 'White',
        'GN' => 'Green',
        'VL' => 'Violet',
        'MI' => 'Mint',
        'NA' => 'Navy',
        'GG' => 'Green',
        'LB' => 'Light Blue',
    ];

    public function __construct(
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig
    ) {
        $this->mediaConfig = $mediaConfig;
    }

    public function assignImages($data)
    {
        $colorFilesPair = [];
        $skuParts = explode('-', $data['sku']);

        $baseDir = '/home/dev/public_html/magento-dragons/pub/media/';
        $files = glob($baseDir . $this->mediaConfig->getBaseTmpMediaPath() . '/sample_data/' . $skuParts[0] . '-*');
        if ($files) {
            foreach ($files as $index => $file) {
                $fileName = basename($file);

                if (preg_match('/^([A-Z0-9]+)-([A-Z]+)_([a-z0-9]+)(_|\.)/', $fileName, $matches)) {
                    $colorMarker = $matches[2];
                    $imageType = $matches[3];
                    if (isset($this->colorMap[$colorMarker])) {
                        $colorFilesPair[$this->colorMap[$colorMarker]][$imageType]['full'] = $file;
                        $colorFilesPair[$this->colorMap[$colorMarker]][$imageType]['relative'] = '/sample_data/' . $fileName;
                    }
                }
            }
        }

        if ($colorFilesPair) {
            if (isset($this->colorMap[$skuParts[2]]) && isset($colorFilesPair[$this->colorMap[$skuParts[2]]])) {
                $mainImageFiles = $colorFilesPair[$this->colorMap[$skuParts[2]]];
                unset($colorFilesPair[$this->colorMap[$skuParts[2]]]);

                $mediaGalleryImages = [];
                foreach ($mainImageFiles as $type => $fileName) {
                    static $i = 0; $i++;
                    $mediaGalleryImages[$type] = [
                        'position' => $i,
                        'file' => $fileName['relative'] . '.tmp',
                        'value_id' => '',
                        'label' => '',
                        'disabled' => '0',
                        'removed' => '',
                    ];
                }
                if ($mediaGalleryImages) {
                    $data['media_gallery'] = array('images' => $mediaGalleryImages);
                    $data['image'] = $mediaGalleryImages['main']['file'];
                    $data['small_image'] = $mediaGalleryImages['main']['file'];
                    $data['thumbnail'] = $mediaGalleryImages['main']['file'];
                }
            }
        }

//        if (isset($data['variations_matrix'])) {
//            foreach ($data['variations_matrix'] as $key => $value) {
//                $pieces = explode('-', $value['sku']);
//                $color = end($pieces);
//
//                if (isset($colorFilesPair[$color])) {
//                    $data['variations_matrix'][$key]['image'] = $colorFilesPair[$color]['main']['relative'].'.tmp';
//                }
//            }
//        }

        return $data;
    }
}
