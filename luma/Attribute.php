<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Catalog\SampleData\Setup;

use Magento\Framework\File\Csv\ReaderFactory as CsvReaderFactory;
use Magento\Framework\Module\SampleData\SetupInterface;

class Attribute implements SetupInterface
{
    protected $attributeFactory;

    protected $attrOptionCollectionFactory;

    protected $productHelper;

    protected $eavConfig;

    protected $csvReaderFactory;

    public function __construct(
        \Magento\Catalog\Model\Resource\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Eav\Model\Config $eavConfig,
        CsvReaderFactory $csvReaderFactory
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->productHelper = $productHelper;
        $this->eavConfig = $eavConfig;
        $this->csvReaderFactory = $csvReaderFactory;
    }

    public function run()
    {
        $attributePrototype = $this->attributeFactory->create();

        $fileName = realpath(__DIR__ . '/../fixtures/attributes.csv');
        $csvReader = $this->csvReaderFactory->create(array('fileName' => $fileName, 'mode' => 'r'));
        foreach($csvReader as $row) {
            static $i = 0; $i++;

            $data = $row;

            $attribute = $this->eavConfig->getAttribute('catalog_product', $data['attribute_code']);
            if (!$attribute) {
                $attribute = $attributePrototype;
                $attribute->unsetData();
            }

            $data['option'] = $this->getOption($attribute, $data);
            $data['source_model'] = $this->productHelper->getAttributeSourceModelByInputType($data['frontend_input']);
            $data['backend_model'] = $this->productHelper->getAttributeBackendModelByInputType($data['frontend_input']);
            $data += array('is_filterable' => 0, 'is_filterable_in_search' => 0, 'apply_to' => array());
            $data['backend_type'] = $attribute->getBackendTypeByInput($data['frontend_input']);

            $attribute->addData($data);
            $attribute->setEntityTypeId(4); //@todo remove hard-coded id
            $attribute->setAttributeSetId(4); //@todo remove hard-coded id
            $attribute->setAttributeGroupId(7); //@todo remove hard-coded id
            $attribute->setSortOrder($i + 999);
            $attribute->setIsUserDefined(1);

            $attribute->save();
            echo '.';
        }

        $this->eavConfig->clear();
    }

    protected function getOption($attribute, $data)
    {
        $result = [];
        $data['option'] = explode("\n", $data['option']);
        /** @var \Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection $options */
        $options = $this->attrOptionCollectionFactory->create()
            ->setAttributeFilter($attribute->getId())
            ->setPositionOrder('asc', true)
            ->load();
        foreach ($data['option'] as $value) {
            if (!$options->getItemByColumnValue('value', $value)) {
                $result[] = $value;
            }
        }
        return $result ? $this->convertOption($result) : $result;
    }

    protected function convertOption($values)
    {
        $result = ['order' => [], 'value' => []];
        $i = 0;
        foreach ($values as $value) {
            $result['order']['option_' . $i] = (string)$i;
            $result['value']['option_' . $i] = [0 => $value, 1 => ''];
            $i++;
        }
        return $result;
    }
}
