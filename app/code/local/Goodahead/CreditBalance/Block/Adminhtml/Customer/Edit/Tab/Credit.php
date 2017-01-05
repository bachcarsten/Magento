<?php
class Goodahead_CreditBalance_Block_Adminhtml_Customer_Edit_Tab_Credit extends Mage_Adminhtml_Block_Widget_Form
        implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function __construct()
    {
        parent::__construct();
        $this->setSaveParametersInSession(false);
    }

    /**
     * Defines after which tab, this tab should be rendered
     *
     * @return string
     */
    public function getAfter()
    {
        return 'orders';
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Store Credit Balance');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Store Credit Balance');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $customer = Mage::registry('current_customer');
        return (bool)$customer->getId();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        //TODO: hide when the module is disabled
        return false;
    }

    protected function _prepareForm()
    {
        $customer = Mage::registry('current_customer');
        $credit = Mage::getModel('goodahead_creditbalance/credit')->load($customer->getId(), 'customer_id');
        $form   = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('goodahead_creditbalance', array(
            'legend'    => Mage::helper('newsletter')->__('Store Credit Information'),
        ));

        if ($credit->getId()) {
            $fieldset->addField('credit_id', 'hidden', array(
                'name'      => 'credit_id',
                'value'     => $credit->getId(),
            ));
        }

        $fieldset->addField('credit_enabled', 'select', array(
            'name'      => 'credit_enabled',
            'options'   => array(
                1 => 'Yes',
                0 => 'No',
            ),
            'label'     => Mage::helper('goodahead_creditbalance')->__('Enabled'),
            'title'     => Mage::helper('goodahead_creditbalance')->__('Enabled'),
            'value'     => $credit->getEnabled(),
        ));

        $fieldset->addField('credit_balance', 'text', array(
            'name'      => 'credit_balance',
            'label'     => Mage::helper('goodahead_creditbalance')->__('Balance'),
            'title'     => Mage::helper('goodahead_creditbalance')->__('Balance'),
            'value'     => sprintf('%.2f', $credit->getBalance()),
        ));

        $fieldset->addField('credit_notify', 'checkbox', array(
            'name'      => 'credit_notify',
            'label'     => Mage::helper('goodahead_creditbalance')->__('Notify customer'),
            'title'     => Mage::helper('goodahead_creditbalance')->__('Notify customer'),
            'value'     => 1,
        ));

        $form->setUseContainer(false);
        $this->setForm($form);

        parent::_prepareForm();

    }
}