<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Password;

class ChangePassword
{
    private ?string $current_password = null;

    private ?string $password = null;

    private ?string $confirm_password = null;

    public function getCurrentPassword(): ?string
    {
        return $this->current_password;
    }

    public function setCurrentPassword(?string $current_password): ChangePassword
    {
        $this->current_password = $current_password;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): ChangePassword
    {
        $this->password = $password;
        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirm_password;
    }

    public function setConfirmPassword(?string $confirm_password): ChangePassword
    {
        $this->confirm_password = $confirm_password;
        return $this;
    }


}
