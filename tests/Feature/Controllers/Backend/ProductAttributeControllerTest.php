<?php

namespace Tests\Feature\Controllers\Backend;

use Tests\TestCase;
use App\Http\Controllers\Backend\ProductAttributeController;

class ProductAttributeControllerTest extends TestCase
{
    public function test_product_attribute_controller_exists(): void
    {
        $this->assertTrue(class_exists(ProductAttributeController::class));
    }

    public function test_product_attribute_controller_has_update_method(): void
    {
        $controller = new ProductAttributeController();
        $this->assertTrue(method_exists($controller, 'update'));
    }
}

