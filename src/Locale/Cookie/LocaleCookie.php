<?php

namespace NetBull\TranslationBundle\Locale\Cookie;

use Symfony\Component\HttpFoundation\Cookie;

class LocaleCookie
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var int
     */
    private int $ttl;

    /**
     * @var string
     */
    private string $path;

    /**
     * @var bool
     */
    private bool $secure;

    /**
     * @var bool
     */
    private bool $httpOnly;

    /**
     * @var bool
     */
    private bool $setOnChange;

    /**
     * @var string|null
     */
    private ?string $domain;

    /**
     * @param string $name
     * @param int $ttl
     * @param string $path
     * @param bool $secure
     * @param bool $httpOnly
     * @param bool $setOnChange
     * @param string|null $domain
     */
    public function __construct(string $name, int $ttl, string $path, bool $secure, bool $httpOnly, bool $setOnChange, ?string $domain = null)
    {
        $this->name = $name;
        $this->ttl = $ttl;
        $this->path = $path;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->setOnChange = $setOnChange;
        $this->domain = $domain;
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
