<?php

namespace NetBull\TranslationBundle\Locale\Cookie;

use Symfony\Component\HttpFoundation\Cookie;

class LocaleCookie
{
    /**
     * @param string $name
     * @param int $ttl
     * @param string $path
     * @param bool $secure
     * @param bool $httpOnly
     * @param bool $setOnChange
     * @param string|null $domain
     */
    public function __construct(
        private string $name,
        private int $ttl,
        private string $path,
        private bool $secure,
        private bool $httpOnly,
        private bool $setOnChange,
        private ?string $domain = null
    ) {
    }

    /**
     * @param string $locale
     * @return Cookie
     */
    public function getLocaleCookie(string $locale): Cookie
    {
        $expire = $this->computeExpireTime();
        return new Cookie($this->name, $locale, $expire, $this->path, $this->domain, $this->secure, $this->httpOnly);
    }

    /**
     * @return bool
     */
    public function setCookieOnChange(): bool
    {
        return $this->setOnChange;
    }

    /**
     * @return int
     */
    private function computeExpireTime(): int
    {
        return time() + $this->ttl;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
