<?php

namespace PHPObjectSymbolResolver\MachO;

class ObjectFile implements \PHPObjectSymbolResolver\ObjectFile {
    const CLASS32 = 1;
    const CLASS64 = 2;
    public int $class;
    public bool $isLSB;

    const MH_OBJECT = 1;
    const MH_EXECUTE = 2;
    const MH_FVMLIB = 3;
    const MH_CORE = 4;
    const MH_PRELOAD = 5;
    const MH_DYLIB = 6;
    const MH_DYLINKER = 7;
    const MH_BUNDLE = 8;
    const MH_DYLIB_STUB = 9;
    const MH_DYSM = 10;
    const MH_KEXT_BUNDLE = 11;

    public int $cpuType;
    public int $cpuSubtype;
    public int $fileType;
    public int $numOfCmds;
    public int $sizeOfCmds;
    public int $flags;
    public int $reserved;

	/** @var Command[] */
    public array $commands = [];
	public array $segments = [];

    public function getAllSymbols(): array {
        $result = [];
        foreach ($this->commands as $command) {
			if ($command->parsed instanceof SymtabCommand) {
				foreach ($command->parsed->symbols as $symbol) {
					if (!$symbol->isGlobal()) {
						continue;
					}
					$result[] = $symbol->name;
				}
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
		return $this->isLSB;
	}
}