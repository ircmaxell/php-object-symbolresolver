<?php

namespace PHPObjectSymbolResolver\PE;

class ObjectFile implements \PHPObjectSymbolResolver\ObjectFile {
    // The path to the ObjectFile.
    public string $filepath;

    public int $oemid;
    public int $oeminfo;
    public int $lfanew;


    /**
     * The number that identifies the type of target machine. See the machine
     * type constants.
     */
    public int $machine;

    /**
     * Machine Type constants.
     *
     * The Machine field has one of the following values, which specify the CPU
     * type. An image file can be run only on the specified machine or on a
     * system that emulates the specified machine.
     *
     * @see https://learn.microsoft.com/en-gb/windows/win32/debug/pe-format#machine-types
     *
     * @var int
     */
    const IMAGE_FILE_MACHINE_UNKNOWN = 0x0;
    const IMAGE_FILE_MACHINE_ALPHA = 0x184;
    const IMAGE_FILE_MACHINE_ALPHA64 = 0x284;
    const IMAGE_FILE_MACHINE_AM33 = 0x1d3;
    const IMAGE_FILE_MACHINE_AMD64 = 0x8664;
    const IMAGE_FILE_MACHINE_ARM = 0x1c0;
    const IMAGE_FILE_MACHINE_ARM64 = 0xaa64;
    const IMAGE_FILE_MACHINE_ARMNT = 0x1c4;
    const IMAGE_FILE_MACHINE_AXP64 = 0x284;
    const IMAGE_FILE_MACHINE_EBC = 0xebc;
    const IMAGE_FILE_MACHINE_I386 = 0x14c;
    const IMAGE_FILE_MACHINE_IA64 = 0x200;
    const IMAGE_FILE_MACHINE_LOONGARCH32 = 0x6232;
    const IMAGE_FILE_MACHINE_LOONGARCH64 = 0x6264;
    const IMAGE_FILE_MACHINE_M32R = 0x9041;
    const IMAGE_FILE_MACHINE_MIPS16 = 0x266;
    const IMAGE_FILE_MACHINE_MIPSFPU = 0x366;
    const IMAGE_FILE_MACHINE_MIPSFPU16 = 0x466;
    const IMAGE_FILE_MACHINE_POWERPC = 0x1f0;
    const IMAGE_FILE_MACHINE_POWERPCFP = 0x1f1;
    const IMAGE_FILE_MACHINE_R4000 = 0x166;
    const IMAGE_FILE_MACHINE_RISCV32 = 0x5032;
    const IMAGE_FILE_MACHINE_RISCV64 = 0x5064;
    const IMAGE_FILE_MACHINE_RISCV128 = 0x5128;
    const IMAGE_FILE_MACHINE_SH3 = 0x1a2;
    const IMAGE_FILE_MACHINE_SH3DSP = 0x1a3;
    const IMAGE_FILE_MACHINE_SH4 = 0x1a6;
    const IMAGE_FILE_MACHINE_SH5 = 0x1a8;
    const IMAGE_FILE_MACHINE_THUMB = 0x1c2;
    const IMAGE_FILE_MACHINE_WCEMIPSV2 = 0x169;

    /**
     * The number of sections. This indicates the size of the section table,
     * which immediately follows the headers.
     */
    public int $numberOfSections;

    /**
     * The low 32 bits of the number of seconds since 00:00 January 1, 1970
     * (a C run-time time_t value), which indicates when the file was created.
     */
    public int $timeDateStamp;

    /**
     * The file offset of the COFF symbol table, or zero if no COFF symbol table
     * is present. This value should be zero for an image because COFF debugging
     * information is deprecated.
     */
    public int $pointerToSymbolTable;

    /**
     * The number of entries in the symbol table. This data can be used to
     * locate the string table, which immediately follows the symbol table. This
     * value should be zero for an image because COFF debugging information is
     * deprecated.
     */
    public int $numberOfSymbols;

    /**
     * The size of the optional header, which is required for executable files
     * but not for object files. This value should be zero for an object file.
     */
    public int $sizeOfOptionalHeader;

    /**
     * The flags that indicate the attributes of the file. See Characteristics
     * constants.
     */
    public int $characteristics;

    /**
     * Characteristics constants.
     *
     * The Characteristics field contains flags that indicate attributes of the
     * object or image file.
     *
     * @see https://learn.microsoft.com/en-gb/windows/win32/debug/pe-format?redirectedfrom=MSDN#characteristics
     *
     * @var int
     */
    const IMAGE_FILE_RELOCS_STRIPPED = 0x0001;
    const IMAGE_FILE_EXECUTABLE_IMAGE = 0x0002;
    const IMAGE_FILE_LINE_NUMS_STRIPPED = 0x0004;
    const IMAGE_FILE_LOCAL_SYMS_STRIPPED = 0x0008;
    const IMAGE_FILE_AGGRESSIVE_WS_TRIM = 0x0010;
    const IMAGE_FILE_LARGE_ADDRESS_AWARE = 0x0020;
    const IMAGE_FILE_BYTES_REVERSED_LO = 0x0080;
    const IMAGE_FILE_32BIT_MACHINE = 0x0100;
    const IMAGE_FILE_DEBUG_STRIPPED = 0x0200;
    const IMAGE_FILE_REMOVABLE_RUN_FROM_SWAP = 0x0400;
    const IMAGE_FILE_NET_RUN_FROM_SWAP = 0x0800;
    const IMAGE_FILE_SYSTEM = 0x1000;
    const IMAGE_FILE_DLL = 0x2000;
    const IMAGE_FILE_UP_SYSTEM_ONLY = 0x4000;
    const IMAGE_FILE_BYTES_REVERSED_HI = 0x8000;

    /**
     * This is NOT part of the PE format. It is a helper variable to find the
     * COFF string table.
     */
    public int $pointerToStringTable;

    /**
     * This is NOT part of the PE format. It is a helper variable to contain the
     * size of the COFF string table.
     */
    public int $stringTableSize;

    /**
     * Begin Optional Header definition.
     */

    /**
     * The Optional Header Magic Number determines whether an image is PE32 or
     * PE32+ (64-bit). Se PE Format constants for more details.
     */
    public int $peFormat;

    /**
     * PE Format constants.
     */
    const PE32 = 0x10b;
    const PE32_PLUS = 0x20b;
    const ROM_IMAGE = 0x107;

    /**
     * The linker major version number.
     */
    public int $majorLinkerVersion;

    /**
     * The linker minor version number.
     */
    public int $minorLinkerVersion;

    /**
     * The size of the code (text) section, or the sum of all code sections if
     * there are multiple sections.
     */
    public int $sizeOfCode;

    /**
     * The size of the initialized data section, or the sum of all such sections
     * if there are multiple data sections.
     */
    public int $sizeOfInitializedData;

    /**
     * The size of the uninitialized data section (BSS), or the sum of all such
     * sections if there are multiple BSS sections.
     */
    public int $sizeOfUninitializedData;

    /**
     * The address of the entry point relative to the image base when the
     * executable file is loaded into memory. For program images, this is the
     * starting address. For device drivers, this is the address of the
     * initialization function. An entry point is optional for DLLs. When no
     * entry point is present, this field must be zero.
     */
    public int $addressOfEntryPoint;

    /**
     * The address that is relative to the image base of the beginning-of-code
     * section when it is loaded into memory.
     */
    public int $baseOfCode;

    /**
     * The address that is relative to the image base of the beginning-of-data
     * section when it is loaded into memory. This field is only present for
     * PE32, and is absent in PE32+.
     */
    public int $baseOfData;

    /**
     * The preferred address of the first byte of image when loaded into memory;
     * must be a multiple of 64 K. The default for DLLs is 0x10000000. The
     * default for Windows CE EXEs is 0x00010000. The default for Windows NT,
     * Windows 2000, Windows XP, Windows 95, Windows 98, and Windows Me is
     * 0x00400000.
     */
    public int $imageBase;

    /**
     * The alignment (in bytes) of sections when they are loaded into memory. It
     * must be greater than or equal to FileAlignment. The default is the page
     * size for the architecture.
     */
    public int $sectionAlignment;

    /**
     * The alignment factor (in bytes) that is used to align the raw data of
     * sections in the image file. The value should be a power of 2 between 512
     * and 64 K, inclusive. The default is 512. If the SectionAlignment is less
     * than the architecture's page size, then FileAlignment must match
     * SectionAlignment.
     */
    public int $fileAlignment;

    /**
     * The major version number of the required operating system.
     */
    public int $majorOperatingSystemVersion;

    /**
     * The minor version number of the required operating system.
     */
    public int $minorOperatingSystemVersion;

    /**
     * The major version number of the image.
     */
    public int $majorImageVersion;

    /**
     * The minor version number of the image.
     */
    public int $minorImageVersion;

    /**
     * The major version number of the subsystem.
     */
    public int $majorSubsystemVersion;

    /**
     * The minor version number of the subsystem.
     */
    public int $minorSubsystemVersion;

    /**
     * Reserved, must be zero.
     */
    public int $win32VersionValue;

    /**
     * The size (in bytes) of the image, including all headers, as the image is
     * loaded in memory. It must be a multiple of SectionAlignment.
     */
    public int $sizeOfImage;

    /**
     * The combined size of an MS-DOS stub, PE header, and section headers
     * rounded up to a multiple of FileAlignment.
     */
    public int $sizeOfHeaders;

    /**
     * The image file checksum.
     */
    public int $checksum;

    /**
     * The subsystem that is required to run this image. See Windows Subsystem
     * constants.
     */
    public int $subsystem;

    /**
     * Windows Subsystem constants.
     *
     * @see https://learn.microsoft.com/en-us/windows/win32/debug/pe-format#windows-subsystem
     */
    const IMAGE_SUBSYSTEM_UNKNOWN = 0;
    const IMAGE_SUBSYSTEM_NATIVE = 1;
    const IMAGE_SUBSYSTEM_WINDOWS_GUI = 2;
    const IMAGE_SUBSYSTEM_WINDOWS_CUI = 3;
    const IMAGE_SUBSYSTEM_OS2_CUI = 5;
    const IMAGE_SUBSYSTEM_POSIX_CUI = 7;
    const IMAGE_SUBSYSTEM_NATIVE_WINDOWS = 8;
    const IMAGE_SUBSYSTEM_WINDOWS_CE_GUI = 9;
    const IMAGE_SUBSYSTEM_EFI_APPLICATION = 10;
    const IMAGE_SUBSYSTEM_EFI_BOOT_SERVICE_DRIVER = 11;
    const IMAGE_SUBSYSTEM_EFI_RUNTIME_DRIVER = 12;
    const IMAGE_SUBSYSTEM_EFI_ROM = 13;
    const IMAGE_SUBSYSTEM_XBOX = 14;
    const IMAGE_SUBSYSTEM_WINDOWS_BOOT_APPLICATION = 16;

    /**
     * Defines different characteristics of the image, if it is a DLL. See DLL
     * Characteristics constants.
     */
    public int $dllCharacteristics;

    /**
     * DLL Characteristics constants.
     *
     * @see https://learn.microsoft.com/en-us/windows/win32/debug/pe-format#dll-characteristics
     */
    const IMAGE_DLLCHARACTERISTICS_HIGH_ENTROPY_VA = 0x0020;
    const IMAGE_DLLCHARACTERISTICS_DYNAMIC_BASE = 0x0040;
    const IMAGE_DLLCHARACTERISTICS_FORCE_INTEGRITY = 0x0080;
    const IMAGE_DLLCHARACTERISTICS_NX_COMPAT = 0x0100;
    const IMAGE_DLLCHARACTERISTICS_NO_ISOLATION = 0x0200;
    const IMAGE_DLLCHARACTERISTICS_NO_SEH = 0x0400;
    const IMAGE_DLLCHARACTERISTICS_NO_BIND = 0x0800;
    const IMAGE_DLLCHARACTERISTICS_APPCONTAINER = 0x1000;
    const IMAGE_DLLCHARACTERISTICS_WDM_DRIVER = 0x2000;
    const IMAGE_DLLCHARACTERISTICS_GUARD_CF = 0x4000;
    const IMAGE_DLLCHARACTERISTICS_TERMINAL_SERVER_AWARE = 0x8000;

    /**
     * The size of the stack to reserve. Only SizeOfStackCommit is committed;
     * the rest is made available one page at a time until the reserve size is
     * reached.
     */
    public int $sizeOfStackReserve;

    /**
     * The size of the stack to commit.
     */
    public int $sizeOfStackCommit;

    /**
     * The size of the local heap space to reserve. Only SizeOfHeapCommit is
     * committed; the rest is made available one page at a time until the
     * reserve size is reached.
     */
    public int $sizeOfHeapReserve;

    /**
     * The size of the local heap space to commit.
     */
    public int $sizeOfHeapCommit;

    /**
     * Reserved, must be zero.
     */
    public int $loaderFlags;

    /**
     * The number of data-directory entries in the remainder of the optional
     * header. Each describes a location and size.
     */
    public int $numberOfRvaAndSizes;

    /**
     * The export table (.edata) address.
     */
    public int $exportTableRva;

    /**
     * The export data (.edata) size.
     */
    public int $exportTableSize;

    /**
     * The import data (.idata) address.
     */
    public int $importTableRva;

    /**
     * The import data (.idata) size.
     */
    public int $importTableSize;

    /**
     * The resource table (.rsrc) address.
     */
    public int $resourceTableRva;

    /**
     * The resource table (.rsrc) size.
     */
    public int $resourceTableSize;

    /**
     * The exception table address.
     */
    public int $exceptionTableRva;

    /**
     * The exception table size.
     */
    public int $exceptionTableSize;

    /**
     * The attribute certificate table address.
     */
    public int $certificateTableRva;

    /**
     * The attribute certificate table size.
     */
    public int $certificateTableSize;

    /**
     * The base relocation table address.
     */
    public int $baseRelocationTableRva;

    /**
     * The base relocation table size.
     */
    public int $baseRelocationTableSize;

    /**
     * The debug data starting address.
     */
    public int $debugRva;

    /**
     * The debug data size.
     */
    public int $debugSize;

    /**
     * Reserved, must be 0.
     */
    public int $architectureRva;

    /**
     * Reserved, must be 0.
     */
    public int $architectureSize;

    /**
     * The address of the value to be stored in the global pointer register.
     */
    public int $globalPtrRva;

    /**
     * Reserved, must be 0.
     */
    public int $globalPtrSize;

    /**
     * The thread local storage (TLS) table address.
     */
    public int $tlsTableRva;

    /**
     * The thread local storage (TLS) table size.
     */
    public int $tlsTableSize;

    /**
     * The load configuration table address.
     */
    public int $loadConfigTableRva;

    /**
     * The load configuration table size.
     */
    public int $loadConfigTableSize;

    /**
     * The bound import table address.
     */
    public int $boundImportRva;

    /**
     * The bound import table size.
     */
    public int $boundImportSize;

    /**
     * The import address table address.
     */
    public int $iatRva;

    /**
     * The import address table size.
     */
    public int $iatSize;

    /**
     * The delay import descriptor address.
     */
    public int $delayImportDescriptorRva;

    /**
     * The delay import descriptor size.
     */
    public int $delayImportDescriptorSize;

    /**
     * The CLR runtime header address.
     */
    public int $clrRuntimeHeaderRva;

    /**
     * The CLR runtime header size.
     */
    public int $clrRuntimeHeaderSize;

    /**
     * @var Section[]
     */
    public array $sections;

    /**
     * @var ?ObjectFile[]
     */
    public ?array $importedObjects = null;

    /**
     * @var string[]
     */
    private static ?array $sharedLibrarySearchDirectories = null;

    public function is32Bit(): bool {
        return $this->peFormat === self::PE32;
    }

    public function is64Bit(): bool {
        return $this->peFormat === self::PE32_PLUS;
    }

    public function hasLowestByteFirst(): bool {
        return true;
    }

    protected function findSection($name) {
        foreach ($this->sections as $section) {
            if ($name === $section->name) {
                return $section;
            }
        }

        throw new \LogicException(sprintf(
            'Unable to find the specified section "%s".',
            $name
        ));
    }

    public static function getSharedSearchPaths(): array {
        $sysdrive = getenv('SystemDrive') or 'C:';
        $sysdir = getenv('SystemRoot') or "$sysdrive\\Windows";

        if (self::$sharedLibrarySearchDirectories === null) {
            self::$sharedLibrarySearchDirectories = ["$sysdir\\System32"];

            if (is_dir("$sysdir\\SysWOW64")) {
                self::$sharedLibrarySearchDirectories[] = "$sysdir\\SysWOW64";
            }

            self::$sharedLibrarySearchDirectories[] = $sysdir;
            self::$sharedLibrarySearchDirectories[] = '%filepath%';

            $envPaths = explode(PATH_SEPARATOR, getenv('PATH'));
            self::$sharedLibrarySearchDirectories =
                array_merge(self::$sharedLibrarySearchDirectories, $envPaths);
        }

        return self::$sharedLibrarySearchDirectories;
    }

    public static function addSharedSearchDirectory($path) {
        self::getSharedSearchPaths();
        self::$sharedLibrarySearchDirectories[] = rtrim($path, DIRECTORY_SEPARATOR);
    }

    protected function findDependencyPath($filename) {
        foreach (self::getSharedSearchPaths() as $path) {
            if ('%filepath%' === $path) {
                $path = rtrim(dirname($this->filepath), DIRECTORY_SEPARATOR);
            }

            if ($realpath = realpath($path . DIRECTORY_SEPARATOR . $filename)) {
                return $realpath;
            }
        }

        throw new \RuntimeException(sprintf(
            'Location of dependency "%s" could not be found in search paths.',
            $filename
        ));
    }

    public function resolveDependentObjectsRecursively(&$objects = []) {
        $dependencies = [];
        $importSection = $this->findSection('.idata');
        $exportSection = $this->findSection('.edata');

        if (! is_null($importSection)) {
            foreach ($importSection->symbols as $symbol) {
                $dependencies[] = $symbol->dllFilename;
            }
        }

        if (! is_null($exportSection)) {
            foreach ($exportSection->symbols as $symbol) {
                $objects[$symbol->nameString] = $this;
            }
        }

        foreach ($dependencies as $dependency) {
            $path = $this->findDependencyPath($dependency);

            // This dependency has already been resolved.
            if (isset($objects[$dependency]) || isset($objects[$path])) {
                continue;
            }

            $parser = \PHPObjectSymbolResolver\Parser::parseFor($path);
            $parser->resolveDependentObjectsRecursively($objects);
        }

        return $objects;
    }

    /**
     * @return string[]
     */
	public function getAllSymbols(): array {
        $result = [];

        foreach ($this->sections as $section) {
            foreach ($section->symbols as $symbol) {
                $result[] = $symbol->nameString;
            }
        }

        return $result;
    }

	/**
     * @return string[]
     */
	public function getAllSymbolsRecursively(): array {
        if ($this->importedObjects === null) {
            $this->importedObjects = $this->resolveDependentObjectsRecursively();
        }

        $symbols = $this->getAllSymbols();

        foreach ($this->importedObjects as $object) {
            array_push($symbols, ...$object->getAllSymbols());
        }

        return array_unique($symbols);
    }

    /**
     * Translates a Relative Virtual Address to a physical file offset.
     *
     * @param int $relativeVirtualAddress The RVA to translate.
     *
     * @return ?int The translated address, or NULL on failure.
     */
    public function translateRva(int $relativeVirtualAddress): int {
        foreach ($this->sections as $section) {
            $startVirtualAddress = $section->virtualAddress;
            $endVirtualAddress = $startVirtualAddress + $section->virtualSize;

            if ($relativeVirtualAddress < $startVirtualAddress) {
                continue;
            }

            if ($relativeVirtualAddress > $endVirtualAddress) {
                continue;
            }

            // Remove the virtual offset and base offset and add the physical one.
            $sectionOffset = $relativeVirtualAddress - $startVirtualAddress;
            return $section->pointerToRawData + $sectionOffset;
        }

        throw new \RuntimeException(sprintf(
            'Could not translate RVA 0x%x as we could not determine which ' .
            'section it is contained within.',
            $relativeVirtualAddress
        ));
    }
}
