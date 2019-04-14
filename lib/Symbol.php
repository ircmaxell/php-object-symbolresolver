<?php

namespace PHPELFSymbolResolver;

class Symbol {
    const VISIBILITY_DEFAULT = 0;
    const VISIBILITY_INTERNAL = 1;
    const VISIBILITY_HIDDEN = 2;
    const VISIBILITY_PROTECTED = 3;

    const BINDING_LOCAL = 0;
    const BINDING_GLOBAL = 1;
    const BINDING_WEAK = 2;

    public int $name;
    public string $nameString;
    public int $value;
    public int $size;
    public int $info;
    public int $other;
    public int $shndx;

    public function isOther(int $other): bool {
        return ($this->other & 0x3) === $other;
    }

    public function isLocal(): bool {
        return ($this->info >> 4) === self::BINDING_LOCAL;
    }

    public function isGlobal(): bool {
        return ($this->info >> 4) === self::BINDING_GLOBAL;
    }

    public function isWeak(): bool {
        return ($this->info >> 4) === self::BINDING_WEAK;
    }
}