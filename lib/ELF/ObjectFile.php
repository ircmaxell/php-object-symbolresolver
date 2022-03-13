<?php

namespace PHPObjectSymbolResolver\ELF;

class ObjectFile implements \PHPObjectSymbolResolver\ObjectFile {
    const CLASS32 = 1;
    const CLASS64 = 2;
    public int $class;

    const BYTEORDER_LSB = 1;
    const BYTEORDER_MSB = 2;
    public int $byteOrder;

    public int $version;

    public int $abi;

    public int $abiVersion;

    const TYPE_NONE = 0;
    const TYPE_RELOCATABLE = 1;
    const TYPE_EXECUTABLE = 2;
    const TYPE_SHARD_OBJECT = 3;
    const TYPE_CORE = 4;

    public int $type;
    public int $machine;
    public int $eversion;
    public int $entry;
    public int $phoff;
    public int $shoff;
    public int $flags;
    public int $phentsize;
    public int $phnum;
    public int $shentsize;
    public int $shnum;
    public int $shstrndx;

    public array $sections;

    public function getAllSymbols(): array {
        $result = [];
        foreach ($this->sections as $section) {
            foreach ($section->symbols as $symbol) {
                if (!$symbol->isOther(Symbol::VISIBILITY_DEFAULT)) {
                    continue;
                }
                if (!$symbol->isGlobal()) {
                    continue;
                }
                $result[] = $symbol->nameString;
            }
        }
        return $result;
    }

	public function is32Bit(): bool {
		return $this->class === self::CLASS32;
	}

	public function is64Bit(): bool {
		return $this->class === self::CLASS64;
	}

	public function hasLowestByteFirst(): bool {
		return $this->byteOrder === self::BYTEORDER_LSB;
	}
}