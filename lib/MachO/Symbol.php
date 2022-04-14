<?php

namespace PHPObjectSymbolResolver\MachO;

class Symbol implements \PHPObjectSymbolResolver\Symbol {
	const N_STAB = 0xe0;
	const N_PEXT = 0x10;
	const N_TYPE = 0x0e;
	const N_EXT = 0x01; // exposed symbol

	const N_UDF = 0x0; // undefined
	const N_ABS = 0x2; // absolute
	const N_SECT = 0xe; // in section nSect
	const N_PBUD = 0xc; // undefined, prebound, in section nSect
	const N_INDR = 0xa; // indirect, inherits other symbol by name (string offset nValue)

	const REFERENCE_MASK = 0xF;
	const REFERENCE_FLAG_UNDEFINED_NON_LAZY = 0x0;
	const REFERENCE_FLAG_UNDEFINED_LAZY = 0x1;
	const REFERENCE_FLAG_DEFINED = 0x2;
	const REFERENCE_FLAG_PRIVATE_DEFINED = 0x3;
	const REFERENCE_FLAG_PRIVATE_UNDEFINED_NON_LAZY = 0x4;
	const REFERENCE_FLAG_PRIVATE_UNDEFINED_LAZY = 0x5;

	const REFERENCED_DYNAMICALLY = 0x10;
	const N_DESC_DISCARDED = 0x20;
	const N_NO_DEAD_STRIP = 0x20;
	const N_WEAK_REF = 0x40;
	const N_WEAK_DEF = 0x80;

	public int $nStrx;
	public int $nType;
	public int $nSect;
	public int $nDesc;
	public int $nValue;

    public string $name;

    public function isOther(int $other): bool {
        return false;
    }

    public function isLocal(): bool {
		$type = $this->nType & ((self::N_TYPE & ~self::N_PEXT) | self::N_EXT);
		return $type === self::N_ABS || $type === self::N_SECT;
    }

    public function isGlobal(): bool {
		$type = $this->nType & (self::N_TYPE | self::N_EXT);
		return $type === (self::N_ABS | self::N_EXT) || $type === (self::N_SECT | self::N_EXT);
    }

    public function isWeak(): bool {
        return ($this->nType & (self::N_TYPE | self::N_EXT)) === self::N_EXT;
    }
}
