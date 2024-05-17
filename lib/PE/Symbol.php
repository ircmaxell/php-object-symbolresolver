<?php

namespace PHPObjectSymbolResolver\PE;

class Symbol implements \PHPObjectSymbolResolver\Symbol {
    public string $nameString;

    /**
     * This is unused.
     */
    public function isOther(int $other): bool {
        return false;
    }

    /**
     * At this point, we do not scan for local symbols.
     */
	public function isLocal(): bool {
        return false;
    }

    /**
     * At this point, we only scan for global symbols.
     */
	public function isGlobal(): bool {
        return true;
    }

    /**
     * Symbols are only weak if they are delay-loaded.
     */
	public function isWeak(): bool {
        return false;
    }
}
