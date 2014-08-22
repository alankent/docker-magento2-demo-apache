<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\SampleData\Model\Setup\Catalog;

use Magento\Framework\File\Csv\ReaderFactory as CsvReaderFactory;
use Magento\Framework\Module\SampleData\SetupInterface;

class Category implements SetupInterface
{
    protected $writeService;

    protected $categoryDataBuilder;

    protected $csvReaderFactory;

    public function __construct(
        \Magento\Catalog\Service\V1\Category\WriteServiceInterface $writeService,
        \Magento\Catalog\Service\V1\Data\CategoryBuilder $categoryDataBuilder,
        CsvReaderFactory $csvReaderFactory
    ) {
        $this->writeService = $writeService;
        $this->categoryDataBuilder = $categoryDataBuilder;
        $this->csvReaderFactory = $csvReaderFactory;
    }

    public function run()
    {
        $fileName = realpath(__DIR__ . '/../fixtures/attributes.csv');
        $csvReader = $this->csvReaderFactory->create(array('fileName' => $fileName, 'mode' => 'r'));
        foreach($csvReader as $row) {
            $data = [
                'parent_id' => '?',
                'name' => '',
                'active' => '',
                'is_anchor' => ''
            ];
        }

        $categoryData = $this->categoryDataBuilder->populateWithArray($data);
        $this->writeService->create($categoryData);
    }
}
