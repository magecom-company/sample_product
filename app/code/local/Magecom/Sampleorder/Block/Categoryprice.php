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

/**
 * Magecom_Sampleorder_Block_Categoryprice class
 *
 * @category Magecom
 * @package Magecom_Sampleorder
 * @author  Magecom
 */
class Magecom_Sampleorder_Block_Categoryprice extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * Category renderer
     *
     * @var Magecom_Sampleorder_Block_Adminhtml_Form_Field_Category
     */
    protected $_categoryRenderer;

    /**
     * price type renderer
     *
     * @var Magecom_Sampleorder_Block_Adminhtml_Form_Field_Pricetype
     */
    protected $_priceTypeRenderer;

    /**
     * prepare Render
     *
     * @return void
     */
    public function _prepareToRender()
    {
        $this->addColumn('category_id', array(
            'label'     => Mage::helper('magecom_sampleorder')->__('Category'),
            'renderer'  => $this->_getRendererCategory(),
        ));
        $this->addColumn('pricetype_id', array(
            'label'     => Mage::helper('magecom_sampleorder')->__('Price Type'),
            'renderer'  => $this->_getRendererPriceType(),
        ));
        $this->addColumn('cost', array(
            'label'     => Mage::helper('magecom_sampleorder')->__('Product price'),
            'style'     => 'width:100px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('magecom_sampleorder')->__('Add');
    }

    /**
     * render category
     *
     * @return Mage_Core_Block_Abstract|Magecom_Sampleorder_Block_Adminhtml_Form_Field_Category
     */
    protected function  _getRendererCategory()
    {
        if (!$this->_categoryRenderer) {
            $this->_categoryRenderer = $this->getLayout()->createBlock(
                'magecom_sampleorder/adminhtml_form_field_category', '',
                array('is_render_to_js_template' => true)
            );
        }

        return $this->_categoryRenderer;
    }

    /**
     * Render price type
     *
     * @return Mage_Core_Block_Abstract|Magecom_Sampleorder_Block_Adminhtml_Form_Field_Pricetype
     */
    protected function  _getRendererPriceType()
    {
        if (!$this->_priceTypeRenderer) {
            $this->_priceTypeRenderer = $this->getLayout()->createBlock(
                'magecom_sampleorder/adminhtml_form_field_pricetype', '',
                array('is_render_to_js_template' => true)
            );
        }

        return $this->_priceTypeRenderer;
    }

    /**
     * array for select
     *
     * @param Varien_Object $row
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getRendererCategory()->calcOptionHash($row->getData('category_id')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->_getRendererPriceType()->calcOptionHash($row->getData('pricetype_id')),
            'selected="selected"'
        );
    }
}
