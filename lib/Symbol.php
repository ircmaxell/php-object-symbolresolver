<?php

namespace PHPObjectSymbolResolver;

interface Symbol {
	public function isLocal(): bool;
	public function isGlobal(): bool;
	public function isWeak(): bool;
}