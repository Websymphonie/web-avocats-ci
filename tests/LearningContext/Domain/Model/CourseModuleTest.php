<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Domain\Model\CourseModule;

final class CourseModuleTest extends TestCase
{
    public function testUpdateTrimsEditableValues(): void
    {
        $module = new CourseModule(1, 'uuid', 10, 'Initial', 'Description', 1);

        $module->update('  Nouveau module  ', '  Texte court  ');

        self::assertSame('Nouveau module', $module->title);
        self::assertSame('Texte court', $module->description);
        self::assertSame(10, $module->trainingId);
        self::assertSame(1, $module->position);
    }
}
