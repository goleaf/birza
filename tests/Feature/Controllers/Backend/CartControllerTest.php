<?php

namespace Tests\Feature\Controllers\Backend;

use Tests\TestCase;
use App\Http\Controllers\Backend\CartController;

class CartControllerTest extends TestCase
{
    public function test_cart_controller_exists(): void
    {
        $this->assertTrue(class_exists(CartController::class));
    }

    public function test_cart_controller_has_index_method(): void
    {
        $controller = new CartController();
        $this->assertTrue(method_exists($controller, 'index'));
    }

    public function test_cart_controller_has_add_method(): void
    {
        $controller = new CartController();
        $this->assertTrue(method_exists($controller, 'add'));
    }

    public function test_cart_controller_has_update_method(): void
    {
        $controller = new CartController();
        $this->assertTrue(method_exists($controller, 'update'));
    }

    public function test_cart_controller_has_remove_method(): void
    {
        $controller = new CartController();
        $this->assertTrue(method_exists($controller, 'remove'));
    }
}

