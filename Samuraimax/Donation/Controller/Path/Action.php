<?php

namespace Samuraimax\Donation\Controller\Path;

use Magento\Framework\App\Action\Context;

class Action extends \Magento\Framework\App\Action\Action {

    protected $_jsonEncoder;
    protected $checkoutSession;
    protected $scopeConfig;
    protected $cart;
    private $cartRepository;
    protected $formKey;


    public function __construct(
       \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
       \Magento\Framework\Json\EncoderInterface $encoder,
       \Magento\Checkout\Model\SessionFactory $checkoutSession,
       \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
       \Magento\Checkout\Model\Cart $cart,
       \Magento\Framework\Data\Form\FormKey $formKey,
       Context $context)
   {
       $this->_jsonEncoder = $encoder;
       $this->scopeConfig = $scopeConfig;
       $this->checkoutSession = $checkoutSession;
       $this->cartRepository = $cartRepository;
       $this->cart = $cart;
       $this->formKey = $formKey;
       parent::__construct($context);
   }

    public function execute()
    {

        $donationAmount = $this->getRequest()->getParam('amount');
        $donationTitle =  $this->scopeConfig->getValue('donations_config_section/donations_config_group/donation_product_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $donationSKU =  $this->scopeConfig->getValue('donations_config_section/donations_config_group/donation_product_sku', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $donationDescription =  $this->scopeConfig->getValue('donations_config_section/donations_config_group/donation_product_description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        // Programmatically create the donation product with amount passed in as param
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create('\Magento\Catalog\Model\Product');

        // todo get sku prefix from config
        $product->setSku($donationSKU . '_' . time());

        // todo get name from config
        $product->setName($donationTitle);
        $product->setDescription($donationDescription);
        $product->setAttributeSetId(4);
        $product->setStatus(1);
        $product->setTypeId('donation_product');
        $product->setPrice($donationAmount);
        $product->setStockData([
            'use_config_manage_stock' => 0,
            'manage_stock' => 1,
            'is_in_stock' => 1,
            'qty' => 1,
        ]);
        $product->save();

        // Add the newly created product to cart

        $session = $this->checkoutSession->create();
        $quote = $session->getQuote();
        $quote->addProduct($product, 1);

        $this->cartRepository->save($quote);
        $session->replaceQuote($quote)->unsLastRealOrderId();

//        $params = [
//            'form_key' => $this->formKey->getFormKey(),
//            'product' => $product->getId(),
//            'qty'   => 1
//        ];
//
//        $this->cart->addProduct($product, $params);
//
//
//        $this->cart->save();




        $this->getResponse()->representJson($this->_jsonEncoder->encode(['result' => 'new product created', 'id' => $product->getId()]));
    }

}
