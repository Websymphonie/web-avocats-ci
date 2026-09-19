<?php

declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Presenter\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Test\TypeTestCase;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\AddUserCommand;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Presenter\Form\User\AddUserFormType;
use Websymphonie\IdentityContext\Presenter\Form\User\ProfileFormType;
use Websymphonie\IdentityContext\Presenter\Form\User\UpdateUserFormType;
use Websymphonie\IdentityContext\Presenter\Form\User\UserFilterType;

final class UserNameFieldTest extends TypeTestCase
{
    /** @return iterable<string, array{class-string<AbstractType<mixed>>, bool}> */
    public static function accountFormTypes(): iterable
    {
        yield 'creation' => [AddUserFormType::class, true];
        yield 'administrative update' => [UpdateUserFormType::class, true];
        yield 'profile' => [ProfileFormType::class, true];
        yield 'filter' => [UserFilterType::class, false];
    }

    /**
     * @dataProvider accountFormTypes
     * @param class-string<AbstractType<mixed>> $formType
     */
    public function testNameIsAvailableInEveryAccountFlow(string $formType, bool $required): void
    {
        $form = $this->factory->create($formType);

        self::assertTrue($form->has('name'));
        self::assertSame($required, $form->get('name')->getConfig()->getRequired());
    }

    public function testAssignableRolesContainOnlyCanonicalBusinessRoles(): void
    {
        foreach ([AddUserFormType::class, UpdateUserFormType::class] as $formType) {
            $form = $this->factory->create($formType);

            self::assertSame([
                UserRolesEnum::ADMIN->value,
                UserRolesEnum::AVOCAT->value,
                UserRolesEnum::USER->value,
            ], $form->get('roles')->getConfig()->getOption('choices'));
        }
    }

    public function testKleRolesHaveTheExpectedFrenchLabels(): void
    {
        self::assertSame('Manager', UserRolesEnum::ADMIN->label());
        self::assertSame('Avocat(e)', UserRolesEnum::AVOCAT->label());
        self::assertSame('Utilisateur', UserRolesEnum::USER->label());
    }

    public function testRoleFilterContainsOnlyCanonicalRoles(): void
    {
        $form = $this->factory->create(UserFilterType::class);
        $choices = $form->get('role')->getConfig()->getOption('choices');

        self::assertSame(UserRolesEnum::allRoles(), array_values($choices));
        self::assertSame([
            UserRolesEnum::SUPER_ADMIN->label(),
            UserRolesEnum::ADMIN->label(),
            UserRolesEnum::AVOCAT->label(),
            UserRolesEnum::USER->label(),
        ], array_keys($choices));
    }

    public function testRoleSubmissionKeepsStringRoleCodesInTheCommand(): void
    {
        $command = new AddUserCommand();
        $form = $this->factory->create(AddUserFormType::class, $command);

        $form->submit([
            'name' => 'Awa Koné',
            'email' => 'awa.kone@example.test',
            'password' => 'mot-de-passe-securise',
            'confirmPassword' => 'mot-de-passe-securise',
            'roles' => [UserRolesEnum::AVOCAT->value, UserRolesEnum::USER->value],
            'enabled' => true,
        ]);

        self::assertSame([UserRolesEnum::AVOCAT->value, UserRolesEnum::USER->value], $command->roles);
    }
}
