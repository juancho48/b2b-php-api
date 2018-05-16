<?php

use Arkitecht\B2B\B2B;
use Arkitecht\B2B\PurchaseOrder;

class B2BIntegrationTest extends PHPUnit_Framework_TestCase
{

    private $config;

    public function setUp()
    {
        if (!$this->config) {
            $this->config = require dirname(__FILE__) . '/config.php';
        }
    }

    private function getConfig()
    {
        $this->config = require_once dirname(__FILE__) . '/config.php';
    }

    /** @test */
    function can_create_b2b_service_object()
    {
        $b2b = new B2B($this->config['auth_token']);
    }

    /** @test */
    function can_add_scopes_by_array()
    {
        $b2b = new B2B($this->config['auth_token']);
        $b2b->addScope(['wsTransactionAPI', 'wsTransactionAPI.Read']);

        $this->assertEquals([
            'wsTransactionAPI',
            'wsTransactionAPI.Read',
        ], $b2b->scopes());

        $b2b->addScope(['wsCouponAPI']);
        $this->assertEquals([
            'wsTransactionAPI',
            'wsTransactionAPI.Read',
            'wsCouponAPI',
        ], $b2b->scopes());

        $b2b->addScope(['wsCouponAPI.Read', 'wsCouponAPI.Write']);
        $this->assertEquals([
            'wsTransactionAPI',
            'wsTransactionAPI.Read',
            'wsCouponAPI',
            'wsCouponAPI.Read',
            'wsCouponAPI.Write',
        ], $b2b->scopes());

    }

    /** @test */
    function can_add_scopes_by_string()
    {
        //'wsTransactionAPI wsTransactionAPI.Read wsCouponAPI wsCouponAPI.Read wsCouponAPI.Write wsProductAPI wsProductAPI.Reserve wsProductAPI.ReadDict',
        $b2b = new B2B($this->config['auth_token']);
        $b2b->addScope('wsTransactionAPI wsTransactionAPI.Read');

        $this->assertEquals([
            'wsTransactionAPI',
            'wsTransactionAPI.Read',
        ], $b2b->scopes());

        $b2b->addScope('wsCouponAPI');
        $this->assertEquals([
            'wsTransactionAPI',
            'wsTransactionAPI.Read',
            'wsCouponAPI',
        ], $b2b->scopes());

        $b2b->addScope('wsCouponAPI.Read wsCouponAPI.Write');
        $this->assertEquals([
            'wsTransactionAPI',
            'wsTransactionAPI.Read',
            'wsCouponAPI',
            'wsCouponAPI.Read',
            'wsCouponAPI.Write',
        ], $b2b->scopes());
    }

    /** @test */
    function can_get_scopes_as_string()
    {
        $b2b = new B2B($this->config['auth_token'], 'wsTransactionAPI wsTransactionAPI.Read wsCouponAPI wsCouponAPI.Read wsCouponAPI.Write');
        $this->assertEquals([
            'wsTransactionAPI',
            'wsTransactionAPI.Read',
            'wsCouponAPI',
            'wsCouponAPI.Read',
            'wsCouponAPI.Write',
        ], $b2b->scopes());

        $this->assertEquals('wsTransactionAPI wsTransactionAPI.Read wsCouponAPI wsCouponAPI.Read wsCouponAPI.Write', $b2b->scopes(true));
    }

    /** @test */
    function can_get_a_list_of_stores()
    {
        $b2b = new B2B($this->config['auth_token'], $this->config['scopes'], false);
        $b2b->setOlrId($this->config['olrid'])->getStores();
    }

    /** @test */
    function can_add_a_purchase_order()
    {
        $b2b = new B2B($this->config['auth_token'], $this->config['scopes'], false);

        $purchaseOrder = (new  PurchaseOrder())
            ->setStoreId(4)
            ->setDate(\Carbon\Carbon::now())
            ->setReferenceNum('TESTREST123')
            ->setShippingCost(5.00)
            ->setVendorCode('REF#ONDIGO');

        $purchaseOrder->addProduct((new \Arkitecht\B2B\ComplexTypes\Product())
            ->setCost(1.25)
            ->setQuantity(4)
            ->setName('HD360')
            ->setClass(\Arkitecht\B2B\ComplexTypes\ProductClass::StandardAccessory)
            ->setManufacturer('REF#ONDIGO')
            ->setSku('819907010001'));

        $response = $b2b->setOlrId($this->config['olrid'])->addPurchaseOrder($purchaseOrder);
        if (is_a($response, \GuzzleHttp\Exception\ClientException::class)) {
            $this->fail($response->getMessage());
        }

        print_r($response);
    }
}
