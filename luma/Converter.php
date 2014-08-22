<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\ConfigurableProduct\SampleData\Setup\Product;

class Converter
{
    protected $categoryFactory;

    protected $eavConfig;

    protected $attributeCollectionFactory;

    protected $attrOptionCollectionFactory;

    protected $variationMatrix;

    protected $categoryNameIdPair;

    protected $attributeCodeOptionsPair;

    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix $variationMatrix
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->variationMatrix = $variationMatrix;
    }

    public function convertRow($row)
    {
        $data = [];
        $configurableAttributes = [];
        foreach ($row as $field => $value) {
            if ('category' == $field) {
                $data['category_ids'] = $this->getCategoryIds($this->getArrayValue($value));
                continue;
            }
            if (in_array($field, array('color', 'size'))) {
                $configurableAttributes[$field] = $this->getArrayValue($value);
                continue;
            }

            $options = $this->getAttributeOptionIds($field);
            if ($options) {
                $value = $this->getArrayValue($value);
                $result = [];
                foreach ($value as $v) {
                    if (isset($options[$v])) {
                        $result[] = $options[$v];
                    }
                }
                $value = count($result) == 1 ? current($result) : $result;
            }
            $data[$field] = $value;
        }
        if ($configurableAttributes) {
            $data['configurable_attributes_data'] = $this->convertAttributesData($configurableAttributes);
            $data['variations_matrix'] = $this->getVariationsMatrix($data);
            $data['new_variations_attribute_set_id'] = 4;
        }
        return $data;
    }

    protected function getArrayValue($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if (false !== strpos($value, "\n")) {
            $value = array_filter(explode("\n", $value));
        }
        return !is_array($value) ? [$value] : $value;
    }

    protected function getCategoryIds($categories)
    {
        $ids = [];
        foreach ($categories as $name) {
            if (isset($this->categoryNameIdPair[$name])) {
                $ids[] = $this->categoryNameIdPair[$name];
            } else {
                $collection = $this->categoryFactory->create()->getCollection();
                $collection->addAttributeToFilter('name', $name);
                $collection->load();

                if ($collection->getFirstItem()) {
                    $ids[] = $collection->getFirstItem()->getId();
                }
            }
        }
        return $ids;
    }

    protected function convertAttributesData($configurableAttributes)
    {
        $attributesData = [];
        foreach ($configurableAttributes as $attributeCode => $values) {
            $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
            if (!$attribute->getId()) {
                continue;
            }
            $options = $this->attrOptionCollectionFactory->create()
                ->setAttributeFilter($attribute->getId())
                ->setPositionOrder('asc', true)
                ->load();
            $attributeValues = [];
            $attributeOptions = [];
            foreach ($options as $option) {
                $attributeValues[] = array(
                    'value_index' => $option->getId(),
                    'is_percent' => false,
                    'pricing_value' => '',
                    'include' => (int)in_array($option->getValue(), $values)
                );
                $attributeOptions[] = array(
                    'value' => $option->getId(),
                    'label' => $option->getValue()
                );
            }
            $attributesData[$attribute->getId()] = array(
                'id' => '',
                'label' => $attribute->getFrontend()->getLabel(),
                'use_default' => '',
                'position' => '',
                'attribute_id' => $attribute->getId(),
                'attribute_code' => $attribute->getAttributeCode(),
                'code' => $attribute->getAttributeCode(),
                'values' => $attributeValues,
                'options' => $attributeOptions,
            );
        }
        return $attributesData;
    }

    protected function getVariationsMatrix($data)
    {
        $variations = $this->variationMatrix->getVariations($data['configurable_attributes_data']);
        $result = [];
        $productPrice = 100;
        $productName = $data['name'];
        $productSku = $data['sku'];
        foreach ($variations as $variation) {
            $attributeValues = array();
            $attributeLabels = array();
            $price = $productPrice;
            foreach ($data['configurable_attributes_data'] as $attributeData) {
                $attributeValues[$attributeData['attribute_code']] = $variation[$attributeData['attribute_id']]['value'];
                $attributeLabels[$attributeData['attribute_code']] = $variation[$attributeData['attribute_id']]['label'];
                if (isset($variation[$attributeData['attribute_id']]['price'])) {
                    $priceInfo = $variation[$attributeData['attribute_id']]['price'];
                    $price += ($priceInfo['is_percent'] ? $productPrice / 100.0 : 1.0) * $priceInfo['pricing_value'];
                }
            }
            $key = implode('-', $attributeValues);
            $result[$key] = [
                'image' => '',
                'name'   => $productName . '-' . implode('-', $attributeLabels),
                'sku'    => $productSku . '-' . implode('-', $attributeLabels),
                'configurable_attribute' => \json_encode($attributeValues),
                'quantity_and_stock_status' => ['qty' => ''],
                'weight' => '1',
            ];
        }
        return $result;
    }

    public function getAttributeOptionIds($attributeCode)
    {
        if (null === $this->attributeCodeOptionsPair) {
            $collection = $this->attributeCollectionFactory->create();
            $collection->addFieldToSelect(array('attribute_code', 'attribute_id'));
            $collection->setAttributeSetFilter(4);
            $collection->setFrontendInputTypeFilter(array('in' => array('select', 'multiselect')));
            foreach ($collection as $item) {
                $options = $this->attrOptionCollectionFactory->create()
                    ->setAttributeFilter($item->getAttributeId())->setPositionOrder('asc', true)->load();
                foreach ($options as $option) {
                    $this->attributeCodeOptionsPair[$item->getAttributeCode()][$option->getValue()] = $option->getId();
                }
            }
        }
        if (isset($this->attributeCodeOptionsPair[$attributeCode])) {
            return $this->attributeCodeOptionsPair[$attributeCode];
        }
        return null;
    }
}
