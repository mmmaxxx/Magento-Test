<?php

namespace Samuraimax\Donation\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface {

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        // TODO: Implement upgrade() method.
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $fieldList = [
                'price',
                'special_price',
                'special_from_date',
                'special_to_date',
                'minimal_price',
                'cost',
                'tier_price',
            ];

            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array('donation_product', $applyTo)) {
                    $applyTo[] = 'donation_product';
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }
        }
    }

}
