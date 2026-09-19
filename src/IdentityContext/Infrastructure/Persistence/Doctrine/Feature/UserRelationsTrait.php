<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Feature;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Websymphonie\AuthContext\Infrastructure\Persistence\Doctrine\Entity\ResetPassword;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\Log\Logs;

trait UserRelationsTrait
{

    /** @var Collection<int, Logs> */
    #[ORM\OneToMany(targetEntity: Logs::class, mappedBy: 'user', cascade: ["remove"])]
    private Collection $logs;

    #[ORM\OneToOne(targetEntity: ResetPassword::class, mappedBy: 'user', cascade: ['remove'])]
    private ?ResetPassword $resetPassword = null;

    public function __constructUserRelations(): void
    {
        $this->logs = new ArrayCollection();
    }

    /**
     * @return Collection<int, Logs>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Logs $log): static
    {
        if (!$this->logs->contains($log)) {
            $this->logs->add($log);
            $log->setUser($this);
        }

        return $this;
    }

    public function removeLog(Logs $log): static
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getUser() === $this) {
                $log->setUser(null);
            }
        }

        return $this;
    }

    public function getResetPassword(): ?ResetPassword
    {
        return $this->resetPassword;
    }

    public function setResetPassword(?ResetPassword $resetPassword): self
    {
        // unset the owning side of the relation if necessary
        if ($resetPassword === null && $this->resetPassword !== null) {
            $this->resetPassword->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($resetPassword !== null && $resetPassword->getUser() !== $this) {
            $resetPassword->setUser($this);
        }

        $this->resetPassword = $resetPassword;

        return $this;
    }
}
