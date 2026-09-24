<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Presenter\Form;

use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\CreateLessonCommand;
use Websymphonie\LearningContext\Domain\Enum\VideoProvider;
use Websymphonie\LearningContext\Presenter\Form\CourseModule\LessonFormType;

final class LessonFormTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        return [new ValidatorExtension(Validation::createValidator())];
    }

    public function testMuxProviderAndPlaybackIdAreAccepted(): void
    {
        $command = new CreateLessonCommand(1, 2);
        $form = $this->factory->create(LessonFormType::class, $command);

        self::assertSame([VideoProvider::YOUTUBE, VideoProvider::MUX], array_values($form->get('videoProvider')->getConfig()->getOption('choices')));
        $form->submit([
            'title' => 'Leçon Mux',
            'content' => '',
            'videoProvider' => VideoProvider::MUX->value,
            'videoReference' => 'AbCdEf0123456789_-',
            'resourceFiles' => [],
        ]);

        self::assertTrue($form->isValid());
        self::assertSame(VideoProvider::MUX, $command->videoProvider);
        self::assertSame('AbCdEf0123456789_-', $command->videoReference);
    }
}
