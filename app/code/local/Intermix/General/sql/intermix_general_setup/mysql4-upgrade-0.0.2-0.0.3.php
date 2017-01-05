<?php
$installer = $this;

$installer->startSetup();

$installer->run("
TRUNCATE TABLE {$this->getTable('cms_block_store')};
INSERT INTO {$this->getTable('cms_block')} (`block_id`, `title`, `identifier`, `content`, `creation_time`, `update_time`, `is_active`) VALUES
(NULL, 'Top Navigation Links', 'top_navigation', '<li onmouseover=\"toggleMenu(this,1)\" onmouseout=\"toggleMenu(this,0)\" class=\"level0 nav-parent\"><span><a href=\"{{store direct_url=''supplies''}}\">Supplies</span></a></li>\n        <li onmouseover=\"toggleMenu(this,1)\" onmouseout=\"toggleMenu(this,0)\" class=\"level0 nav-parent\"><span><a href=\"{{store direct_url=''ordering-info''}}\">Ordering Info</span></a></li>\n        <li onmouseover=\"toggleMenu(this,1)\" onmouseout=\"toggleMenu(this,0)\" class=\"level0 nav-parent\"><span><a href=\"{{store direct_url=''testimonials''}}\">Testimonials</span></a></li>\n        <li onmouseover=\"toggleMenu(this,1)\" onmouseout=\"toggleMenu(this,0)\" class=\"level0 nav-parent\"><span><a href=\"{{store direct_url=''contacts''}}\">Contact Us</span></a></li>\n', '2010-01-11 21:10:47', '2010-01-11 21:10:47', 1);
");
$store = Mage::getModel('core/website')->load('base');

$blockCollection = Mage::getResourceModel('cms/block_collection')->load();

foreach( $blockCollection->getItems() as $block ) {
    $arr = array(
        'block_id' => $block->getId(),
        'store_id' => $store->getId()
    );
    $installer->getConnection()->insert($this->getTable('cms_block_store'), $arr);
}

$installer->endSetup();