<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Framework\App;

use Magento\Framework\AppInterface;
use Magento\Framework\App\Console\Response;
use \Magento\Framework\Module\SampleData\Installer as SampleDataInstaller;

class SampleData implements AppInterface
{
    /**
     * @var \Magento\Framework\App\Console\Response
     */
    protected $response;

    /**
     * @var SampleDataInstaller
     */
    protected $installer;

    /**
     * @param SampleDataInstaller $installer
     * @param Response $response
     */
    public function __construct(SampleDataInstaller $installer, Response $response)
    {
        $this->installer = $installer;
        $this->response = $response;
    }

    /**
     * Launch application
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function launch()
    {
        $this->installer->install();
        $this->response->setCode(0);
        return $this->response;
    }
}
