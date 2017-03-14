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
 * Magecom_Study_IndexController class
 *
 * @category Magecom
 * @package Magecom_Sampleorder
 * @author  Magecom
 */
class Magecom_Sampleorder_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * create sample product
     *
     * @param $product
     * @return mixed
     */
    private function _createSampleProduct($product)
    {
        $previousStore = Mage::app()->getStore()->getStoreId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $name = $product->getName();
        $sku = $product->getSku();
        $sampleProduct = $product->duplicate();
        $sampleProduct = $sampleProduct->load($sampleProduct->getId());
        $sampleProductPrice = $this->getSampleProductPrice($product);
        $sampleProduct->setSku($sku . '_sample')
            ->setPrice($sampleProductPrice)
            ->setName($name . ' (Sample)')
            ->setStatus(1)
            ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
            ->setStockData(array(
                    'use_config_manage_stock' => 0,
                    'manage_stock'            => 1,
                    'min_sale_qty'            => 1,
                    'max_sale_qty'            => 2,
                    'is_in_stock'             => 1,
                    'qty'                     => 999
                )
            )
            ->setCategoryIds(array());

        if ($sampleProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
            && $sampleProduct->getHasOptions() == 1) {
            $this->unsetCustomOptionsPrice($sampleProduct);
        }

        try {
            $sampleProduct->save();
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot save sample product.'));
            Mage::logException($e);
        }
        Mage::app()->setCurrentStore($previousStore);

        return $sampleProduct;
    }

    /**
     * add sample product to cart
     *
     * @param $sampleProduct
     * @throws Exception
     */
    private function _addSampleToCart($sampleProduct)
    {
        $params = $this->getRequest()->getParams();
        if (!isset($params['options'])) {
            $params['options'] = array();
        } else {
            $params['options'] = $this->_adaptOptions($params);
        }
        $cart = Mage::getModel('checkout/cart');
        $cart->init();
        try {
            $cart->addProduct($sampleProduct,
                new Varien_Object(array(
                    'product'   => $sampleProduct->getId(),
                    'qty'       => 1,
                    'options'   => $params['options'],
                ))
            );
            $cart->save();
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
        }

    }

    /**
     * index action
     */
    public function indexAction()
    {
        $productId = $this->getRequest()->getParam('prodId');
        $product = Mage::getModel('catalog/product')->load($productId);
        $sku = $product->getSku();
        $newSku = $sku . '_sample';
        $sampleProduct = Mage::getModel('catalog/product');
        $id = Mage::getModel('catalog/product')->getResource()->getIdBySku($newSku);
        if ($id) {
            $sampleProduct->load($id);
            $sampleProductPrice = $this->getSampleProductPrice($product);
            if (!$sampleProduct->getStockItemGetQty()) {
                $sampleProduct->setStockData(array(
                    'is_in_stock' => 1,
                    'qty'         => 999,
                ));
                $previousStore = Mage::app()->getStore()->getStoreId();
                Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                try {
                    $sampleProduct->setPrice($sampleProductPrice);
                    $sampleProduct->save();
                } catch (Mage_Core_Exception $e) {
                    if ($this->_getSession()->getUseNotice(true)) {
                        $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
                    } else {
                        $messages = array_unique(explode("\n", $e->getMessage()));
                        foreach ($messages as $message) {
                            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                        }
                    }
                } catch (Exception $e) {
                    $this->_getSession()->addException($e, $this->__('Cannot save sample product.'));
                    Mage::logException($e);
                }
                Mage::app()->setCurrentStore($previousStore);
            }
        } else {
            $sampleProduct = $this->_createSampleProduct($product);
        }
        $this->_addSampleToCart($sampleProduct);
        $this->_redirect('checkout/cart');
    }

    /**
     * test action
     */
    public function checkSampleAction()
    {
        $productId = $this->getRequest()->getParam('productId');
        $product = Mage::getModel('catalog/product')->load($productId);
        if ($product->getHasSample()) {
            $response['message'] = 'ok';
        } else {
            $response['message'] = 'no';
        }
        $json = json_encode($response);
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'application/json')
            ->setBody($json);
    }

    /**
     * Duplicate choose of simples custom options for
     * sample product, witch is duplicate from parent simple product
     *
     * @param  array $param          serialize product form data
     *
     * @return array $newOptionArray option for new sample product. created on the basis of custom options of parent product
     */
    protected function _adaptOptions($param)
    {
        $newOptionArray = array();
        $optionValues = array();
        if (!empty($param) && isset($param['product']) && isset($param['options'])) {
            $parentProduct = Mage::getModel('catalog/product')->load($param['product']);
            $parentOptions = Mage::getModel('catalog/product_option')->getProductOptionCollection($parentProduct);
            foreach ($parentOptions as $parentOption) {
                if ($param['options'][$parentOption->getOptionId()]) {
                    $optionValues[$parentOption->getTitle()]
                        = $parentOption->getValues()[$param['options'][$parentOption->getOptionId()]]->getTitle();
                } else {
                    $optionValues[$parentOption->getTitle()] = null;
                }

            }

            $sampleSku = $parentProduct->getSku() . '_sample';
            $sampleProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $sampleSku);
            $sampleOptions = Mage::getModel('catalog/product_option')->getProductOptionCollection($sampleProduct);
            foreach ($optionValues as $optionParentName => $optionParentValue)
            {
                foreach ($sampleOptions as $sampleOption) {
                    if ($optionParentName == $sampleOption->getTitle()) {
                        $newOptionArray = $this->_findAndAddToArrAppropriateValueOfOption(
                            $newOptionArray, $sampleOption, $optionParentValue
                        );
                    }
                }
            }
            return $newOptionArray;
        } else {
            return array();
        }
    }

    /**
     * find appropriate value in sample custom options
     *
     * @param $newOptionArray
     * @param $sampleOption
     * @param $optionParentValue
     * @return array option for new sample product. created on the basis of custom options of parent product
     */
    protected function _findAndAddToArrAppropriateValueOfOption($newOptionArray, $sampleOption, $optionParentValue)
    {
        foreach ($sampleOption->getValues() as $optionItemKey => $optionItem) {
            if ($optionItem->getTitle() == $optionParentValue) {
                $newOptionArray[$sampleOption->getOptionId()] = $optionItemKey;
            }
        }
        return $newOptionArray;
    }

    /**
     * get price for new sample
     *
     * @param $parentProduct
     *
     * @return float|mixed
     */
    public function getSampleProductPrice($parentProduct)
    {
        $finalPrice = 0.00;
        if (Mage::getStoreConfig('sampleorder_section/general_group/price')) {
            $finalPrice = Mage::getStoreConfig('sampleorder_section/general_group/price');
        }
        $priceDependingCategory = unserialize(
            Mage::getStoreConfig('sampleorder_section/category_price/price_depending_category')
        );
        if ($priceDependingCategory) {
            $categoryIds = $parentProduct->getCategoryIds();
            if (!empty($categoryIds)) {
                $firstCategory = array_shift($categoryIds);
                foreach ($priceDependingCategory as $rowPriceCategory) {
                    if ($firstCategory == $rowPriceCategory['category_id'] && isset($rowPriceCategory['cost'])) {
                        if ($rowPriceCategory['pricetype_id'] == 1) {
                            $finalPrice = $rowPriceCategory['cost'];
                            break;
                        } elseif ($rowPriceCategory['pricetype_id'] == 2) {
                            if ($this->getRequest()->getParam('price')) {
                                $finalPrice
                                    = ($this->getRequest()->getParam('price') * $rowPriceCategory['cost']) / 100;
                            } else {
                                $finalPrice = ($parentProduct->getPrice() * $rowPriceCategory['cost']) / 100;
                            }
                            break;
                        }
                    }
                }
            }
        }

        return $finalPrice;
    }

    /**
     * Direct remove custom options price for new sample
     *
     * @param $product sample product
     */
    public function unsetCustomOptionsPrice($product)
    {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $options = $product->getOptionInstance()->getProductOptionCollection($product);
        foreach ($options as $option) {
            foreach ($option->getValues() as $id => $value) {
                $writeConnection->delete(
                    Mage::getSingleton('core/resource')->getTableName('catalog/product_option_type_price'),
                    array('option_type_id = ?' => $id)
                );
            }
        }
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
}
