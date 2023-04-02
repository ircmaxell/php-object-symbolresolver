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
    public int $ehsize;
    public int $phentsize;
    public int $phnum;
    public int $shentsize;
    public int $shnum;
    public int $shstrndx;

    /** @var Section[] */
    public array $sections;

    /** @var ObjectFile[] */
    public ?array $dependentObjects = null;

    /** @var string[] */
    private static ?array $sharedLibrarySearchDirectories = null;

    private static function collectSharedSearchPaths($file): array {
        $paths = [];
        foreach (file($file) as $line) {
            $line = trim($line);
            if ($line != "" && $line[0] != "#") {
                if ($line[0] === "/") {
                    $paths[] = $line;
                } elseif (preg_match('(^include\s\K.*)', $line, $m)) {
                    foreach (glob($m[0]) as $path) {
                        array_push($paths, ...self::collectSharedSearchPaths($path));
                    }
                }
            }
        }
        return $paths;
    }

    public static function getSharedSearchPaths(): array {
        if (self::$sharedLibrarySearchDirectories === null) {
            self::$sharedLibrarySearchDirectories = [
                "/lib",
                "/usr/lib",
                "/lib64",
                "/usr/lib64",
	    ];
            if (file_exists("/etc/ld.so.conf")) {
                array_unshift(self::$sharedLibrarySearchDirectories, ...self::collectSharedSearchPaths("/etc/ld.so.conf"));
            }
        }
        return self::$sharedLibrarySearchDirectories;
    }

    public static function addSharedSearchDirectory($path) {
        self::getSharedSearchPaths();
        self::$sharedLibrarySearchDirectories[] = rtrim($path, "/");
    }

    public function resolveDependentObjectsRecursively(&$objects = []) {
        $dependencies = [];

        foreach ($this->sections as $section) {
            foreach ($section->dynamic as $dynamic) {
                if ($dynamic->tag === DynamicEntry::DT_NEEDED) {
                    $dependencies[] = $dynamic->string;
                }
                if ($dynamic->tag === DynamicEntry::DT_SONAME) {
                    $objects[$dynamic->string] = $this;
                }
            }
        }
        foreach ($dependencies as $dependency) {
            foreach (self::getSharedSearchPaths() as $searchPath) {
                $realpath = realpath($searchPath . "/" . $dependency);
                if (!isset($objects[$dependency]) && $realpath && !isset($objects[basename($realpath)])) {
                    \PHPObjectSymbolResolver\Parser::parseFor($realpath)->resolveDependentObjectsRecursively($objects);
                }
            }
        }
        return $objects;
    }

    public function getAllSymbolsRecursively(): array {
        if ($this->dependentObjects === null) {
            $this->dependentObjects = $this->resolveDependentObjectsRecursively();
        }

        $symbols = $this->getAllSymbols();
        foreach ($this->dependentObjects as $object) {
            array_push($symbols, ...$object->getAllSymbols());
        }

        return array_unique($symbols);
    }

    public function getAllSymbols(): array {
        $result = [];
        foreach ($this->sections as $section) {
            foreach ($section->symbols as $symbol) {
                if (!$symbol->isOther(Symbol::VISIBILITY_DEFAULT)) {
                    continue;
                }
                if (!$symbol->isGlobal() && !$symbol->isWeak()) {
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
