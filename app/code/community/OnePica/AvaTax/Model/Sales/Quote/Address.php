<?php
/**
 * OnePica_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2009 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * The Sales Quote Address model.
 */
class OnePica_AvaTax_Model_Sales_Quote_Address extends Mage_Sales_Model_Quote_Address
{
	/**
	 * Avatax address validator instance
	 *
	 * @var OnePica_AvaTax_Model_Avatax_Address
	 */
	protected $_avataxValidator = null;
	
	/**
	 * Avatax address validator accessor method
	 *
	 * @return OnePica_AvaTax_Model_Avatax_Address
	 */
	public function getAvataxValidator() { 
		return $this->_avataxValidator;
	}
	
	/**
	 * Avatax address validator mutator method
	 *
	 * @return OnePica_AvaTax_Model_Avatax_Address
	 * @return self
	 */
	public function setAvataxValidator(OnePica_AvaTax_Model_Avatax_Address $object) { 
		$this->_avataxValidator = $object; 
		return $this;
	}
	
	/**
	 * Creates a hash key based on only address data for caching
	 *
	 * @return string
	 */
	public function getCacheHashKey() {
		if(!$this->getData('cache_hash_key')) {
			$this->setData('cache_hash_key', hash('md4', $this->format('text')));
		}
		return $this->getData('cache_hash_key');
	}
	
	/**
	 * Validates the address.  AvaTax validation is invoked if the this is a ship-to address.
	 * Returns true on success and an array with an error on failure.
	 *
	 * @return true|array
	 */
	public function validate () {
		$result = parent::validate();
		
		//if base validation fails, don't bother with additional validation
		if ($result !== true) {  
			return $result;
		}
		
		//if ship-to address, do AvaTax validation
		$data = Mage::app()->getRequest()->getPost('billing', array());
		$useForShipping = isset($data['use_for_shipping']) ? (int)$data['use_for_shipping'] : 0;
		
		if($this->getAddressType() == self::TYPE_SHIPPING || $this->getUseForShipping() /* <1.9 */ || $useForShipping /* >=1.9 */) {
			if(!$this->getAvataxValidator()) {
				$validator = Mage::getModel('avatax/avatax_address')->setAddress($this);
				$this->setAvataxValidator($validator);
			}
			return $this->getAvataxValidator()->validate();
		}
		
		return $result;
	}	
    
    
    /* BELOW ARE MAGE CORE PROPERTIES AND METHODS ADDED FOR OLDER VERSION COMPATABILITY */

    protected $_totalAmounts = array();
    protected $_baseTotalAmounts = array();
    
    /**
     * Add amount total amount value
     *
     * @param   string $code
     * @param   float $amount
     * @return  Mage_Sales_Model_Quote_Address
     */
    public function addTotalAmount($code, $amount)
    {
        $amount = $this->getTotalAmount($code)+$amount;
        $this->setTotalAmount($code, $amount);
        return $this;
    }

    /**
     * Add amount total amount value in base store currency
     *
     * @param   string $code
     * @param   float $amount
     * @return  Mage_Sales_Model_Quote_Address
     */
    public function addBaseTotalAmount($code, $amount)
    {
        $amount = $this->getBaseTotalAmount($code)+$amount;
        $this->setBaseTotalAmount($code, $amount);
        return $this;
    }

    /**
     * Set total amount value
     *
     * @param   string $code
     * @param   float $amount
     * @return  Mage_Sales_Model_Quote_Address
     */
    public function setTotalAmount($code, $amount)
    {
        $this->_totalAmounts[$code] = $amount;
        if ($code != 'subtotal') {
            $code = $code.'_amount';
        }
        $this->setData($code, $amount);
        return $this;
    }

    /**
     * Set total amount value in base store currency
     *
     * @param   string $code
     * @param   float $amount
     * @return  Mage_Sales_Model_Quote_Address
     */
    public function setBaseTotalAmount($code, $amount)
    {
        $this->_baseTotalAmounts[$code] = $amount;
        if ($code != 'subtotal') {
            $code = $code.'_amount';
        }
        $this->setData('base_'.$code, $amount);
        return $this;
    }

    /**
     * Get total amount value by code
     *
     * @param   string $code
     * @return  float
     */
    public function getTotalAmount($code)
    {
        if (isset($this->_totalAmounts[$code])) {
            return  $this->_totalAmounts[$code];
        }
        return 0;
    }

    /**
     * Get total amount value by code in base store curncy
     *
     * @param   string $code
     * @return  float
     */
    public function getBaseTotalAmount($code)
    {
        if (isset($this->_baseTotalAmounts[$code])) {
            return  $this->_baseTotalAmounts[$code];
        }
        return 0;
    }
    
    /**
     * Validate minimum amount
     *
     * @return bool
     */
    public function validateMinimumAmount() {
        $storeId = $this->getQuote()->getStoreId();
        if (!Mage::getStoreConfigFlag('sales/minimum_order/active', $storeId)) {
            return true;
        }

        /* added to check customer groups */
        /* check if customer validation is enabled */
        $customer_group_validate = Mage::getStoreConfig('sales/minimum_order/low_order_fee_customer_group_enable', $storeId);
        /* get customer groups */
        if ($customer_group_validate) {
            $customer_groups = Mage::getStoreConfig('sales/minimum_order/low_order_fee_customer_group', $storeId);
            $customer_groups = explode(",", $customer_groups);
            /* get quote customer group */
            $group_id = $this->getQuote()->getCustomerGroupId();
            /* if quote customer group not in list, return true */
            if (!in_array($group_id, $customer_groups))
                return true;
        }
        
        if ($this->getQuote()->getIsVirtual() && $this->getAddressType() == self::TYPE_SHIPPING) {
            return true;
        } elseif (!$this->getQuote()->getIsVirtual() && $this->getAddressType() != self::TYPE_SHIPPING) {
            return true;
        }

        $amount = Mage::getStoreConfig('sales/minimum_order/amount', $storeId);
        $_reference = Mage::getStoreConfig('sales/minimum_order/low_order_fee_reference', $storeId);
        $_amount_to_compare = 0;

        switch ($_reference) {
            case Mango_Loworderfee_Model_System_Config_Source_Reference::BaseSubtotal:
                $_amount_to_compare = $this->getBaseSubtotal();
                break;
            case Mango_Loworderfee_Model_System_Config_Source_Reference::BaseSubtotalWithDiscount:
                $_amount_to_compare = $this->getBaseSubtotalWithDiscount();
                break;
            case Mango_Loworderfee_Model_System_Config_Source_Reference::SubtotalInclTax:
                $_amount_to_compare = $this->getSubtotalInclTax() ;
                break;
                case Mango_LowOrderFee_Model_System_Config_Source_Reference::SubtotalInclTaxWithDiscount:
                $_amount_to_compare = $this->getSubtotalInclTax() + $this->getDiscountAmount();
                break;
        }

        if ($_amount_to_compare < $amount) {
            return false;
        }

        return true;
    }
}
