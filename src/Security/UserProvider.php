<?php
declare(strict_types=1);

namespace App\Security;

use App\Cache\Users\UserCacheDto;
use App\Utils\Token\TokenDto;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 用户提供器
 */
class UserProvider implements UserInterface
{
    private string $credentials = '';

    private ?TokenDto $tokenDto = null;

    private ?UserCacheDto $userCache = null;

    public function getRoles(): array
    {
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        if (is_null($this->tokenDto)) {
            return '';
        }

        return (string) $this->tokenDto->id;
    }

    public function getCredentials(): string
    {
        return $this->credentials;
    }

    public function setCredentials(string $credentials): self
    {
        $this->credentials = $credentials;
        return $this;
    }

    /** @uses getTokenDto */
    public function getTokenDto(): ?TokenDto
    {
        return $this->tokenDto;
    }

    public function setTokenDto(TokenDto $tokenDto): self
    {
        $this->tokenDto = $tokenDto;
        return $this;
    }

    /** @uses getUserCache */
    public function getUserCache(): ?UserCacheDto
    {
        return $this->userCache;
    }

    public function setUserCache(UserCacheDto $userCache): self
    {
        $this->userCache = $userCache;
        return $this;
    }
}
