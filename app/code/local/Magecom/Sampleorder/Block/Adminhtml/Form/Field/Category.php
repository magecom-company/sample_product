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
 * Magecom_Sampleorder_Block_Adminhtml_Form_Field_Category class
 *
 * @category Magecom
 * @package Magecom_Sampleorder
 * @author  Magecom
 */
class Magecom_Sampleorder_Block_Adminhtml_Form_Field_Category extends Mage_Core_Block_Html_Select
{
    /**
     * to html
     *
     * @return string
     */
    public function _toHtml()
    {
        $options = $this->_getCategoriesTreeView();
        foreach ($options as $option) {
            $this->addOption($option['value'], $option['label']);
        }

        return parent::_toHtml();
    }

    /**
     * set name
     *
     * @param $value
     * @return Magecom_Sampleorder_Block_Adminhtml_Form_Field_Category
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * set options
     *
     * @return array
     */
    private function _getCategoriesTreeView()
    {
        $categoryList = array();
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('level')
            ->addAttributeToSort('path', 'asc')
            ->addFieldToFilter('is_active', array('eq' => '1'));

        $categories->setPageSize(100);
        $pages = $categories->getLastPageNumber();
        $currentPage = 1;
        do {
            $categories->setCurPage($currentPage);
            $categories->load();
            foreach ($categories as $category) {
                if ($category->getName()) {
                    $categoryList[] = array(
                        'label' => str_repeat('-', $category->getLevel()-1) . $category->getName(),
                        'value' => $category->getId()
                    );
                }
            }
            $currentPage++;
            $categories->clear();
        } while ($currentPage <= $pages);

        return $categoryList;
    }
}
