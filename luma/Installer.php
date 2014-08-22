<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Framework\Module\SampleData;

use Magento\Framework\App\State;

class Installer
{
    protected $appState;

    protected $resources;

    protected $setupFactory;

    /**
     * @param State $appState
     * @param SetupFactory $setupFactory
     * @param array $resources
     */
    public function __construct(
        State $appState,
        SetupFactory $setupFactory,
        array $resources = []
    ) {
        $this->appState = $appState;
        $this->resources = $resources;
        $this->setupFactory = $setupFactory;
    }

    /**
     * @return void
     */
    public function install()
    {
        foreach ($this->resources as $resourceType) {
            $this->setupFactory->create($resourceType)->run();
        }
    }
}
