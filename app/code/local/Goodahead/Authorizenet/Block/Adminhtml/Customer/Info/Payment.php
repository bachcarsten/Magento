<?php

class Goodahead_Authorizenet_Block_Adminhtml_Customer_Info_Payment
    extends Mage_Adminhtml_Block_Template
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'goodahead/authorizenet/customer/info/payment.phtml';

    /**
     * Payment profile
     *
     * @var array
     */
    protected $_profile  = array();

    /**
     * Profile ID
     *
     * @var int
     */
    protected $_profileId;

    /**
     * Set profile
     *
     * @param int $profileId
     * @param array $profile
     * @return Goodahead_Authorizenet_Block_Adminhtml_Customer_Info_Profile
     */
    public function setProfile($profileId, array $profile)
    {
        $this->_profileId = $profileId;
        $this->_profile   = $profile;
        return $this;
    }

    /**
     * Get profile ID
     *
     * @return int
     */
    public function getProfileId()
    {
        return $this->_profileId;
    }

    /**
     * Get profile
     *
     * @return array
     */
    public function getProfile()
    {
        return $this->_profile;
    }
}