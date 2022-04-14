<?php

namespace PHPObjectSymbolResolver;

interface Symbol {
	public function isOther(int $other): bool;
	public function isLocal(): bool;
	public function isGlobal(): bool;
	public function isWeak(): bool;
}
