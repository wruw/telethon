<?php

/**
 * @package   Barn2\setup-wizard
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\WC_Product_Table\Dependencies\Setup_Wizard\Steps;

use Barn2\Plugin\WC_Product_Table\Dependencies\Setup_Wizard\Step;
/**
 * Handles the cross selling step of the wizard.
 */
class Cross_Selling extends Step
{
    /**
     * Initialize the step.
     */
    public function __construct()
    {
        $this->set_id('more');
        $this->set_name(esc_html__('More', 'woocommerce-product-table'));
        $this->set_title(esc_html__('Extra features', 'woocommerce-product-table'));
        $this->set_description(esc_html__('Enhance your site with these fantastic plugins from Barn2.', 'woocommerce-product-table'));
    }
    /**
     * {@inheritdoc}
     */
    public function setup_fields()
    {
        return [];
    }
    /**
     * {@inheritdoc}
     */
    public function submit($values)
    {
    }
}
