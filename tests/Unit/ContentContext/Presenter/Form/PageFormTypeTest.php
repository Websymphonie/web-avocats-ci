<?php

declare(strict_types=1);

namespace Websymphonie\Tests\ContentContext\Presenter\Form;

use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\CreatePageCommand;
use Websymphonie\ContentContext\Application\Model\PagePersonGroupInput;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\ContentContext\Presenter\Form\Page\PageFormType;
use Websymphonie\ContentContext\Presenter\Form\Page\PagePersonEntryFormType;
use Websymphonie\ContentContext\Presenter\Form\Page\PagePersonGroupFormType;

final class PageFormTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        return [new ValidatorExtension(Validation::createValidator())];
    }

    public function testEditorialGroupIsOptionalAndUsesTheDomainEnum(): void
    {
        $form = $this->factory->create(PageFormType::class, new CreatePageCommand());

        self::assertTrue($form->has('group'));
        self::assertFalse($form->get('group')->getConfig()->getRequired());
        self::assertSame(PageGroup::cases(), $form->get('group')->getConfig()->getOption('choices'));
        self::assertTrue($form->has('sortOrder'));
        self::assertFalse($form->get('sortOrder')->getConfig()->getRequired());
    }

    public function testEditorialGroupCanBeCleared(): void
    {
        $command = new CreatePageCommand(group: PageGroup::LEGAL);
        $form = $this->factory->create(PageFormType::class, $command);

        $form->submit([
            'title' => 'Page institutionnelle',
            'slug' => 'page-institutionnelle',
            'content' => '<p>Contenu</p>',
            'group' => '',
            'cover' => null,
        ]);

        self::assertNull($command->group);
    }

    public function testNegativeEditorialOrderIsInvalid(): void
    {
        $form = $this->factory->create(PageFormType::class, new CreatePageCommand());

        $form->submit([
            'title' => 'Page institutionnelle',
            'slug' => 'page-institutionnelle',
            'content' => '<p>Contenu</p>',
            'group' => '',
            'sortOrder' => '-1',
            'cover' => null,
        ]);

        self::assertFalse($form->isValid());
    }

    public function testPersonGroupsCollectionExposesStructuredEntriesInPageForm(): void
    {
        $form = $this->factory->create(PageFormType::class, new CreatePageCommand());

        self::assertTrue($form->has('personGroups'));
        self::assertSame(PagePersonGroupFormType::class, $form->get('personGroups')->getConfig()->getOption('entry_type'));

        $groupForm = $this->factory->create(PagePersonGroupFormType::class, new PagePersonGroupInput());
        self::assertTrue($groupForm->has('entries'));
        self::assertSame(PagePersonEntryFormType::class, $groupForm->get('entries')->getConfig()->getOption('entry_type'));
        self::assertTrue($groupForm->get('entries')->getConfig()->getOption('allow_add'));
        self::assertTrue($groupForm->get('entries')->getConfig()->getOption('allow_delete'));
    }
}
