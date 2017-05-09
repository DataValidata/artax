<?php

namespace Amp\Artax\Cookie;

interface CookieJar {
    public function get(string $domain, string $path = '', string $name = null): array;
    public function getAll(): array;

    /**
     * Note: Implicit domains MUST NOT start with a dot, explicitly set domains MUST start with a dot.
     *
     * @param Cookie $cookie
     */
    public function store(Cookie $cookie);
    public function remove(Cookie $cookie);
    public function removeAll();
}
