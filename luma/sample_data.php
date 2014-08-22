<?php
/**
 * Public alias for the application entry point
 *
 * {license_notice}
 *
 * @copyright  {copyright}
 * @license    {license_link}
 */
require __DIR__ . '/../app/bootstrap.php';

use Magento\Framework\App\Filesystem;

$params = $_SERVER;

$params[Filesystem::PARAM_APP_DIRS][Filesystem::PUB_DIR] = array('uri' => '');
$params[Filesystem::PARAM_APP_DIRS][Filesystem::MEDIA_DIR] = array('uri' => 'media');
$params[Filesystem::PARAM_APP_DIRS][Filesystem::STATIC_VIEW_DIR] = array('uri' => 'static');
$params[Filesystem::PARAM_APP_DIRS][Filesystem::UPLOAD_DIR] = array('uri' => 'media/upload');
$entryPoint = new \Magento\Framework\App\EntryPoint\EntryPoint(BP, $params);
$entryPoint->run('Magento\Framework\App\SampleData');
