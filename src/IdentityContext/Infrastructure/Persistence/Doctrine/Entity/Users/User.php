<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users;

use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Feature\UserRelationsTrait;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Feature\UserSecurityTrait;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Repository\Users\UserRepository;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventEmitterFeature;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity("email", message: "Cet utilisateur existe déjà, veuillez réessayer")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface
{
    use IdTrait;
    use UuidTrait;
    use DatesTrait;
    use EventEmitterFeature;
    use SoftDeleteableEntity;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $name = null;

    use UserSecurityTrait;
    use UserRelationsTrait;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $accountMustBeVerifedBefore;

    public function __construct()
    {
        $this->uuid = Uuid::v7();
        $this->accountMustBeVerifedBefore = (new DateTimeImmutable('now'))->add(new DateInterval("P1D"));
        $this->roles = ['ROLE_USER'];
        $this->__constructUserRelations();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): User
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // No temporary credentials are stored on the entity.
    }

    public function getAccountMustBeVerifedBefore(): ?DateTimeImmutable
    {
        return $this->accountMustBeVerifedBefore;
    }

    public function setAccountMustBeVerifedBefore(?DateTimeImmutable $accountMustBeVerifedBefore): User
    {
        $this->accountMustBeVerifedBefore = $accountMustBeVerifedBefore;
        return $this;
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        $roles = $this->getRoles();
        $otherRoles = $user->getRoles();
        sort($roles);
        sort($otherRoles);

        return $this->getUuidAsString() === $user->getUuidAsString()
            && $this->getUserIdentifier() === $user->getUserIdentifier()
            && $this->getPassword() === $user->getPassword()
            && $this->getEnabled() === $user->getEnabled()
            && $roles === $otherRoles
            && $this->getSecurityVersion() === $user->getSecurityVersion();
    }
}
