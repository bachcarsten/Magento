<?php

$installer = $this;
$installer->startSetup();
$_oldUpdateMode = Mage::app()->getUpdateMode();
Mage::app()->setUpdateMode(false);
Mage::app()->setCurrentStore(Mage::app()->getStore(0));

//
$block = Mage::getModel('cms/block');
// $block->load('side-logo-promo ', 'identifier');
$block->setStores(array(5));
$block->setTitle('side-logo-promo ');
$block->setIdentifier('side-logo-promo ');
$block->setIsActive(1);
$block->setContent(<<<HTML

<p><img src="{{skin url=images/free-shipping-top.png}}" alt="" /></p>
HTML
);
$block->save();

$block = Mage::getModel('cms/block');
// $block->load('side-tollfree', 'identifier');
$block->setStores(array(5));
$block->setTitle('side-tollfree');
$block->setIdentifier('side-tollfree');
$block->setIsActive(1);
$block->setContent(<<<HTML

<p><img src="{{skin url=images/call-to-free.png}}" alt="" /></p>

HTML
);
$block->save();

$block = Mage::getModel('cms/block');
// $block->load('side-tollfree', 'identifier');
$block->setStores(array(1,2,3,4));
$block->setTitle('side-tollfree');
$block->setIdentifier('side-tollfree');
$block->setIsActive(1);
$block->setContent(<<<HTML

<p><img src="{{skin url=images/call-to-free.png}}" alt="" /></p>

HTML
);
$block->save();

$block = Mage::getModel('cms/block');
// $block->load('side-logo-promo', 'identifier');
$block->setStores(array(1,2,3,4));
$block->setTitle('side-logo-promo');
$block->setIdentifier('side-logo-promo');
$block->setIsActive(1);
$block->setContent(<<<HTML

<p><img src="{{skin url=images/shipping.png}}" alt="" /></p>

HTML
);
$block->save();

$block = Mage::getModel('cms/block');
// $block->load('top-banner', 'identifier');
$block->setStores(array(0));
$block->setTitle('top-banner');
$block->setIdentifier('top-banner');
$block->setIsActive(1);
$block->setContent(<<<HTML

<p><img src="{{skin url=images/top-banner.png}}" alt="" /></p>

HTML
);
$block->save();

Mage::app()->setUpdateMode($_oldUpdateMode);

$installer->endSetup();