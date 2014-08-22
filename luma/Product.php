<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\ConfigurableProduct\SampleData\Setup;

use Magento\Framework\File\Csv\ReaderFactory as CsvReaderFactory;
use Magento\Framework\Module\SampleData\SetupInterface;

class Product implements SetupInterface
{
    protected $productFactory;

    protected $attributeSetId;

    protected $configurableProductType;

    protected $converter;

    protected $csvReaderFactory;

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProductType,
        Product\Converter $converter,
        CsvReaderFactory $csvReaderFactory
    ) {
        $this->productFactory = $productFactory;
        $this->configurableProductType = $configurableProductType;
        $this->converter = $converter;
        $this->csvReaderFactory = $csvReaderFactory;
    }

    public function run()
    {
        /** @var \Magento\Framework\File\Csv\Reader $csvReader */
        $fileName = realpath(__DIR__ . '/../fixtures/products_men_tops.csv');
        $csvReader = $this->csvReaderFactory->create(array('fileName' => $fileName, 'mode' => 'r'));
        foreach($csvReader as $row) {

            $data = $this->converter->convertRow($row);

            /** @var $product \Magento\Catalog\Model\Product */
            $product = $this->productFactory->create();
            $product->setData($data);
            $product
                ->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
                ->setAttributeSetId(4)
                ->setWebsiteIds(array(1))
                ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
                ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                ->setStockData(array('is_in_stock' => 1, 'manage_stock' => 0));

            $simpleIds = $this->configurableProductType->generateSimpleProducts($product, $data['variations_matrix']);
            $product->setAssociatedProductIds($simpleIds);
            $product->setCanSaveConfigurableAttributes(true);

            $product->save();

            echo '.';
            exit();
        }
    }
}
