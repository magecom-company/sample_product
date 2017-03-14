<?php
/**
 * Magecom
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magecom.net so we can send you a copy immediately.
 *
 * @category Magecom
 * @package Magecom_Sampleorder
 * @copyright Copyright (c) 2016 Magecom, Inc. (http://www.magecom.net)
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;
$installer->startSetup();

$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'has_sample',
    array(
        'type'              => 'int',
        'input'             => 'select',
        'label'             => 'Has sample',
        'required'          => false,
        'user_defined'      => true,
        'default'           => '',
        'visible_on_front'  => true,
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'source'            => 'eav/entity_attribute_source_boolean',
        'backend'           => '',
        'apply_to'          => 'simple',
        'is_configurable'   => false
    )
);

$attributeSetId = $this->getAttributeSetId('catalog_product', 'Default');
$attributeGroupId = $this->getAttributeGroupId('catalog_product', $attributeSetId, 'General');
$attributeId = $this->getAttributeId('catalog_product', 'has_sample');

$this->addAttributeToSet('catalog_product', $attributeSetId, $attributeGroupId, $attributeId);

$installer->endSetup();
