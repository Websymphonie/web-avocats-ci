<?php

declare(strict_types=1);

namespace Websymphonie\Tests\SharedContext\Presenter\Form;

use Symfony\Component\Form\Test\TypeTestCase;
use Websymphonie\SharedContext\Presenter\Form\AmountType;

final class AmountTypeTest extends TypeTestCase
{
    public function testItProvidesVisibleDefaultInputStyles(): void
    {
        $view = $this->factory->create(AmountType::class)->createView();

        self::assertStringContainsString('border-input', $view->vars['attr']['class']);
        self::assertStringContainsString('bg-background', $view->vars['attr']['class']);
        self::assertStringContainsString('text-foreground', $view->vars['attr']['class']);
    }
}
