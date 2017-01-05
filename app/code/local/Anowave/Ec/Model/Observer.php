<?php
class Anowave_Ec_Model_Observer
{
    private $debug = false;
     
	public function modify(Varien_Event_Observer $observer)
	{
		if (1)
		{
			$content = $observer->getTransport()->getHtml();
			
			$template = $this->append
			(
				$observer->getBlock()
			);
			
			if ($template)
			{
				/* Update content */
				$observer->getTransport()->setHtml($content . $template);
			}
			
			/* Re-fetch content */
			$content = $observer->getTransport()->getHtml();
			
			$observer->getTransport()->setHtml
			(
				$this->alter($observer->getBlock(), $content)
			);
		}

		return true;
	}
	
	private function append(Mage_Core_Block_Abstract $block)
	{	
		switch ($block->getType())
		{
			case 'page/html_head':						return $this->getHead();
			case 'page/html_footer': 					return $this->getQueue(); 
			
			case 'catalog/product_view_type_simple':
			case 'catalog/product_view_type_grouped':
			case 'catalog/product_view_type_configurable':
														return $this->trackProductViewDetails($block); 
			case 'catalog/product_list':				return $this->trackProductImpression($block);
			case 'checkout/cart':						return $this->getCart($block);
			case 'checkout/onepage':					return $this->getCheckout();
		}
		
		return null;
	}
	
	private function alter(Mage_Core_Block_Abstract $block, $content)
	{	
		switch ($block->getNameInLayout())
		{
			case 'product.info.addtocart': 				return $this->getAjax($block, $content);
			case 'checkout.onepage.review.button':		return $this->getPlaceOrder($block, $content);
				default:
					switch ($block->getType())
					{
						case 'catalog/product_list':		return $this->getClick($block, $content);
						case 'checkout/cart_item_renderer': 
						case 'checkout/cart_item_renderer_configurable':
															return $this->getDelete($block, $content);
					}
		}
		
		return $content;
	}
	
	private function getHead()
	{
		return Mage::app()->getLayout()->createBlock('ec/track')->setTemplate('ec/header.phtml')->setType('ec/track')->setData(array
		(
	       	'debug' => (int) $this->debug
		))->toHtml();
	}
	
	private function getQueue()
	{
		return Mage::app()->getLayout()->createBlock('ec/track')->setTemplate('ec/footer.phtml')->toHtml();
	}
	
	private function getCheckout()
	{
		return Mage::app()->getLayout()->createBlock('ec/track')->setTemplate('ec/checkout.phtml')->toHtml();
	}
	
	private function getCart(Mage_Checkout_Block_Cart $block)
	{
		return Mage::app()->getLayout()->createBlock('ec/track')->setTemplate('ec/cart.phtml')->setData(array
		(
			'items' => $block->getItems(),
			'quote' => $block->getQuote()
 		))->toHtml();
	}
	
	private function getAjax(Mage_Core_Block_Abstract $block, $content = null)
	{
		if(Mage::registry('current_category'))
		{
			$category = Mage::registry('current_category');
		}
		else 
		{
			$collection = $block->getProduct()->getCategoryIds();
			
			if (!$collection)
			{
				$collection[] = Mage::app()->getStore()->getRootCategoryId();
			}
			
			$category = Mage::getModel('catalog/category')->load
			(
				end($collection)
			);
			
		} 
		
		$doc = new DOMDocument('1.0','utf-8');
		$dom = new DOMDocument('1.0','utf-8');
		
		$dom->loadHTML
		(
			mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8')
		);
		
		foreach ($dom->getElementsByTagName('a') as $button)
		{
			$button->setAttribute('onclick',		'return AEC.ajaxDetail(this,dataLayer)');
			$button->setAttribute('data-id', 		$block->getProduct()->getSku());
			$button->setAttribute('data-name', 		Mage::helper('core')->jsQuoteEscape($block->getProduct()->getName()));
			$button->setAttribute('data-price', 	$block->getProduct()->getPrice());
			$button->setAttribute('data-category', 	Mage::helper('core')->jsQuoteEscape($category->getName()));
			$button->setAttribute('data-variant', 	Mage::helper('core')->jsQuoteEscape($block->getProduct()->getResource()->getAttribute('color')->getFrontend()->getValue($block->getProduct())));
			$button->setAttribute('data-brand', 	$block->getProduct()->getAttributeText('manufacturer'));
			
			if ('grouped' == $block->getProduct()->getTypeId())
			{
				$button->setAttribute('data-grouped',1);
			}
		}
	
		return $this->getDOMContent($dom, $doc);
	}
	
	private function getDelete(Mage_Core_Block_Abstract $block, $content = null)
	{
		$collection = $block->getProduct()->getCategoryIds();
			
		if (!$collection)
		{
			$collection[] = Mage::app()->getStore()->getRootCategoryId();
		}
		
		$category = Mage::getModel('catalog/category')->load
		(
			end($collection)
		);
			
		$doc = new DOMDocument('1.0','utf-8');
		$dom = new DOMDocument('1.0','utf-8');
		
		$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
		
		foreach ($dom->getElementsByTagName('a') as $a)
		{
			if (false !== strpos($a->getAttribute('href'),'delete'))
			{
				$a->setAttribute('onclick', 		'return AEC.remove(this, dataLayer)');
				$a->setAttribute('data-id', 		$block->getProduct()->getSku());
				$a->setAttribute('data-name', 		Mage::helper('core')->jsQuoteEscape($block->getProduct()->getName()));
				$a->setAttribute('data-price', 		$block->getProduct()->getPrice());
				$a->setAttribute('data-category', 	Mage::helper('core')->jsQuoteEscape($category->getName()));
				$a->setAttribute('data-quantity',	$block->getQty());
				$a->setAttribute('data-brand', $block->getProduct()->getAttributeText('manufacturer'));

				break;
			}
		}
		
		return $this->getDOMContent($dom, $doc);
	}
	
	private function getClick(Mage_Core_Block_Abstract $block, $content = null)
	{
		$doc = new DOMDocument('1.0','utf-8');
		$dom = new DOMDocument('1.0','utf-8');
		
		$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
		
		$products = array();
		

		foreach ($block->getLoadedProductCollection() as $product)
		{
			$products[] = $product;
		}

		$query = new DOMXPath($dom);
		

		foreach ($query->query('//table[@class="generic-product-grid"]/tr/td') as $key => $element)
		{
			if (isset($products[$key]))
			{
				if (Mage::registry('current_category'))
				{
					$category = Mage::registry('current_category');
				}
				else 
				{
					$collection = $products[$key]->getCategoryIds();
					
					if (!$collection)
					{
						$collection[] = Mage::app()->getStore()->getRootCategoryId();
					}
					
					$category = Mage::getModel('catalog/category')->load
					(
						end($collection)
					);
				}
				
				/* Find appropriate links */
				foreach ($query->query('p/a|h5/a', $element) as $a)
				{
					$a->setAttribute('data-id', 		$products[$key]->getSku());
					$a->setAttribute('data-name', 		Mage::helper('core')->jsQuoteEscape($products[$key]->getName()));
					$a->setAttribute('data-price', 		$products[$key]->getFinalPrice());
					$a->setAttribute('data-category', 	Mage::helper('core')->jsQuoteEscape($category->getName()));
					$a->setAttribute('data-quantity', 	1);
					$a->setAttribute('data-brand', 		$products[$key]->getAttributeText('manufacturer'));
					
					/* Click */
					$a->setAttribute('onclick',			'return AEC.click(this,dataLayer)');
					
				}
				
				/* Add To Cart Custom */
				foreach ($query->query('a[contains(@onclick, "setLocation")]', $element) as $a)
				{
					$click = $a->getAttribute('onclick');
					
					$a->setAttribute('data-id', 		$products[$key]->getSku());
					$a->setAttribute('data-name', 		Mage::helper('core')->jsQuoteEscape($products[$key]->getName()));
					$a->setAttribute('data-price', 		$products[$key]->getFinalPrice());
					$a->setAttribute('data-category', 	Mage::helper('core')->jsQuoteEscape($category->getName()));
					$a->setAttribute('data-quantity', 	1);
					$a->setAttribute('data-onclick', 	$click);
					$a->setAttribute('data-brand', 		$products[$key]->getAttributeText('manufacturer'));
					
					/* Click */
					$a->setAttribute('onclick',			'return AEC.ajax(this,dataLayer)');
				}
			}
		}
		
		return $this->getDOMContent($dom, $doc);
	}
	
	private function getPlaceOrder(Mage_Core_Block_Abstract $block, $content = null)
	{
		$doc = new DOMDocument('1.0','utf-8');
		$dom = new DOMDocument('1.0','utf-8');
		
		$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
		
		foreach ($dom->getElementsByTagName('button') as $button)
		{
			if ('button btn-checkout' == $button->getAttribute('class'))
			{
				break;
			}
		}
		
		return $this->getDOMContent($dom, $doc);
	}
	
	
	private function trackProductImpression(Mage_Core_Block_Abstract $block)
	{
		if(Mage::registry('current_category'))
		{
			$category = Mage::registry('current_category');
		}
		else 
		{
			if ($block && $block->getProduct())
			{
				$collection = $block->getProduct()->getCategoryIds();
			}
			else 
			{
				$collection = array();
			}
			
			if (!$collection)
			{
				$collection[] = Mage::app()->getStore()->getRootCategoryId();
			}
			
			$category = Mage::getModel('catalog/category')->load
			(
				end($collection)
			);
			
		} 
		
		return Mage::app()->getLayout()->createBlock('ec/track')->setTemplate('ec/impression.phtml')->setData(array
		(
			'collection' 	=> $block->getLoadedProductCollection(),
			'category'		=> $category
		))->toHtml();
	}
	
	private function trackProductClick(Mage_Core_Block_Abstract $block)
	{
		
	}
	
	private function trackProductViewDetails(Mage_Core_Block_Abstract $block)
	{
		if(Mage::registry('current_category'))
		{
			$category = Mage::registry('current_category');
		}
		else 
		{
			$collection = $block->getProduct()->getCategoryIds();
			
			if (!$collection)
			{
				$collection[] = Mage::app()->getStore()->getRootCategoryId();
			}
			
			$category = Mage::getModel('catalog/category')->load
			(
				end($collection)
			);
			
		} 
		
		$grouped = array();
		
		/* Check if product is configurable */
		if ('grouped' == $block->getProduct()->getTypeId())
		{
			foreach ($block->getProduct()->getTypeInstance(true)->getAssociatedProducts($block->getProduct()) as $product)
			{
				$grouped[] = $product;
			}
		}

		return Mage::app()->getLayout()->createBlock('ec/track')->setTemplate('ec/details.phtml')->setData(array
		(
			'product'  => $block->getProduct(),
			'grouped'  => $grouped,
			'category' => $category
		))->toHtml();
	}
	
	private function trackProductCartAdd(Mage_Core_Block_Abstract $block)
	{
		
	}
	
	private function trackProductCartRemove(Mage_Core_Block_Abstract $block)
	{
		
	}
	
	private function trackPromotionImpression(Mage_Core_Block_Abstract $block)
	{
		
	}
	
	private function trackPromotionClick(Mage_Core_Block_Abstract $block)
	{
		
	}
	
	private function trackCheckoutStep(Mage_Core_Block_Abstract $block)
	{
		
	}
	
	private function trackCheckoutOption(Mage_Core_Block_Abstract $block)
	{
		
	}
	
	private function trackPurchase(Mage_Core_Block_Abstract $block)
	{
	}
	
	private function trackRefund(Mage_Core_Block_Abstract $block)
	{
		
	}
	
	public function setOrder(Varien_Event_Observer $observer)
	{
		$orderIds = $observer->getEvent()->getOrderIds();
		
        if (empty($orderIds) || !is_array($orderIds)) 
        {
            return;
        }
        
        $block = Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('ec_purchase');
        
        if ($block) 
        {
            $block->setOrderIds($orderIds);
            $block->setAdwords(new Varien_Object(array
            (
            	'google_conversion_id' 			=> Mage::getStoreConfig('ec/adwords/conversion_id'),
            	'google_conversion_language' 	=> 'en_GB',
            	'google_conversion_format' 		=> Mage::getStoreConfig('ec/adwords/conversion_format'),
            	'google_conversion_label' 		=> Mage::getStoreConfig('ec/adwords/conversion_label'),
            	'google_conversion_color' 		=> Mage::getStoreConfig('ec/adwords/conversion_color'),
            	'google_conversion_currency' 	=> Mage::app()->getStore()->getCurrentCurrencyCode()
            )));
        }
        else 
        {
        	return true;
        }
	}
	
	private function getDOMContent(DOMDocument $dom, DOMDocument $doc)
	{ 
		$head = $dom->getElementsByTagName('head')->item(0);
		$body = $dom->getElementsByTagName('body')->item(0);
		if (isset($head)) {
		    foreach ($head->childNodes as $child)
		    {
			    $doc->appendChild($doc->importNode($child, true));
		    }
        }
		
        if (isset($body)) {
		    foreach ($body->childNodes as $child)
		    {
		        $doc->appendChild($doc->importNode($child, true));
		    }
        }

		$content = $doc->saveHTML();
		
		return $content;
	}
	
	public function getFooter(Varien_Event_Observer $observer)
	{
		$footer = $observer->getModel()->getFooter() . Mage::getStoreConfig('ec/config/code');
		
		$observer->getModel()->setFooter($footer);
		
		return true;
	}
}