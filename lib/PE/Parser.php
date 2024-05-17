<?php
/**
 * Defines a parser for processing Windows Portable Executable files.
 *
 * @author Shane Thompson
 */
namespace PHPObjectSymbolResolver\PE;

/**
 * Useful URLs:
 *
 * - https://github.com/cubiclesoft/php-winpefile/blob/master/support/win_pe_file.php
 * - https://upload.wikimedia.org/wikipedia/commons/1/1b/Portable_Executable_32_bit_Structure_in_SVG_fixed.svg
 */

/**
 * @see https://learn.microsoft.com/en-gb/windows/win32/debug/pe-format
 */
class Parser extends \PHPObjectSymbolResolver\Parser {
    const HEADER = "MZ";

    public function parse(string $file): ObjectFile {
        $this->data = file_get_contents($file);

        if (substr($this->data, 0, 2) !== self::HEADER) {
            throw new \LogicException("File is not in PE format");
        }

        $this->obj = new ObjectFile;
        $this->obj->filepath = $file;
        $this->parsePeHeaders();
        $this->parsePeSectionHeaders();
        $this->parseImportTable();
        $this->parseDelayedLoadTable();
        $this->parseExportTable();

        $this->data = '';
        return $this->obj;
    }

    public static function isPortableExecutable(string $file): bool {
        $fh = fopen($file, 'rb');

        // Must contain MS-DOS magic number.
        if ('MZ' !== fread($fh, 2)) {
            fclose($fh);
            return false;
        }

        // Get lfanew from offset 0x3c to test PE header.
        fseek($fh, 0x3c);
        $peHeaderAddress = fread($fh, 4);
        $peHeaderAddress = unpack('Vaddr', $peHeaderAddress)['addr'];

        fseek($fh, $peHeaderAddress);

        $peSignature = fread($fh, 4);
        fclose($fh);
        $peSignature = unpack('Vsig', $peSignature)['sig'];

        return 0x4550 === $peSignature;
    }

    protected function parsePeHeaders(): void {
        /**
         * Skip the MS-DOS header, only getting the lfanew parameter.
         */
        $offset = 0x3c;
        $this->obj->lfanew = $this->parseWord($offset);

        /**
         * Now we must parse the COFF header.
         */
        $offset = $this->obj->lfanew;
        $peSignature = $this->parseWord($offset);

        if (0x4550 !== $peSignature) {
            throw new \LogicException(
                "PE header missing. File is not a valid PE object."
            );
        }

        $this->obj->machine = $this->parseHalf($offset);
        $this->obj->numberOfSections = $this->parseHalf($offset);
        $this->obj->timeDateStamp = $this->parseWord($offset);
        $this->obj->pointerToSymbolTable = $this->parseWord($offset);
        $this->obj->numberOfSymbols = $this->parseWord($offset);
        $this->obj->sizeOfOptionalHeader = $this->parseHalf($offset);
        $this->obj->characteristics = $this->parseHalf($offset);

        /**
         * Let us calculate the position of the COFF string table while we are
         * here. The string table is always directly after the COFF symbol
         * table. The first 4 bytes of the string table are the size of the
         * string table, so we will skip that and assign it to its own variable.
         */
        $this->obj->pointerToStringTable = $this->obj->pointerToSymbolTable +
            ($this->obj->numberOfSymbols * 18) + 4;
        $pointerToSize = $this->obj->pointerToStringTable - 4;
        $this->obj->stringTableSize = $this->parseWord($pointerToSize);

        /**
         * We have no optional headers to pass. Although this is a valid COFF
         * executable, it's not really helpful for us...
         */
        if (! $this->obj->sizeOfOptionalHeader) {
            throw new \LogicException(
                "Missing Optional PE Headers. File can not be parsed."
            );
        }

        $this->obj->peFormat = $this->parseHalf($offset);
        $this->obj->majorLinkerVersion = ord($this->data[$offset+0]);
        $this->obj->minorLinkerVersion = ord($this->data[$offset+1]);
        $offset += 2;
        $this->obj->sizeOfCode = $this->parseWord($offset);
        $this->obj->sizeOfInitializedData = $this->parseWord($offset);
        $this->obj->sizeOfUninitializedData = $this->parseWord($offset);
        $this->obj->addressOfEntryPoint = $this->parseWord($offset);
        $this->obj->baseOfCode = $this->parseWord($offset);

        // This field only exists on 32-bit object files.
        if ($this->obj->is64Bit()) {
            $this->obj->baseOfData = -1;
        } else {
            $this->obj->baseOfData = $this->parseWord($offset);
        }

        $this->obj->imageBase = $this->parseXWord($offset);
        $this->obj->sectionAlignment = $this->parseWord($offset);
        $this->obj->fileAlignment = $this->parseWord($offset);
        $this->obj->majorOperatingSystemVersion = $this->parseHalf($offset);
        $this->obj->minorOperatingSystemVersion = $this->parseHalf($offset);
        $this->obj->majorImageVersion = $this->parseHalf($offset);
        $this->obj->minorImageVersion = $this->parseHalf($offset);
        $this->obj->majorSubsystemVersion = $this->parseHalf($offset);
        $this->obj->minorSubsystemVersion = $this->parseHalf($offset);
        $this->obj->win32VersionValue = $this->parseWord($offset);
        $this->obj->sizeOfImage = $this->parseWord($offset);
        $this->obj->sizeOfHeaders = $this->parseWord($offset);
        $this->obj->checksum = $this->parseWord($offset);
        $this->obj->subsystem = $this->parseHalf($offset);
        $this->obj->dllCharacteristics = $this->parseHalf($offset);
        $this->obj->sizeOfStackReserve = $this->parseXWord($offset);
        $this->obj->sizeOfStackCommit = $this->parseXWord($offset);
        $this->obj->sizeOfHeapReserve = $this->parseXWord($offset);
        $this->obj->sizeOfHeapCommit = $this->parseXWord($offset);
        $this->obj->loaderFlags = $this->parseWord($offset);
        $this->obj->numberOfRvaAndSizes = $this->parseWord($offset);

        /**
         * Optional Header Data Directories. There are 16 possible values, we
         * must check with numberOfRvaAndSizes prior to each one to ensure we
         * don't overflow into the next section.
         */
        if ($this->obj->numberOfRvaAndSizes > 0) {
            $this->obj->exportTableRva = $this->parseWord($offset);
            $this->obj->exportTableSize = $this->parseWord($offset);
        } else {
            $this->obj->exportTableRva = 0;
            $this->obj->exportTableSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 1) {
            $this->obj->importTableRva = $this->parseWord($offset);
            $this->obj->importTableSize = $this->parseWord($offset);
        } else {
            $this->obj->importTableRva = 0;
            $this->obj->importTableSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 2) {
            $this->obj->resourceTableRva = $this->parseWord($offset);
            $this->obj->resourceTableSize = $this->parseWord($offset);
        } else {
            $this->obj->resourceTableRva = 0;
            $this->obj->resourceTableSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 3) {
            $this->obj->exceptionTableRva = $this->parseWord($offset);
            $this->obj->exceptionTableSize = $this->parseWord($offset);
        } else {
            $this->obj->exceptionTableRva = 0;
            $this->obj->exceptionTableSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 4) {
            $this->obj->certificateTableRva = $this->parseWord($offset);
            $this->obj->certificateTableSize = $this->parseWord($offset);
        } else {
            $this->obj->certificateTableRva = 0;
            $this->obj->certificateTableSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 5) {
            $this->obj->baseRelocationTableRva = $this->parseWord($offset);
            $this->obj->baseRelocationTableSize = $this->parseWord($offset);
        } else {
            $this->obj->baseRelocationTableRva = 0;
            $this->obj->baseRelocationTableSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 6) {
            $this->obj->debugRva = $this->parseWord($offset);
            $this->obj->debugSize = $this->parseWord($offset);
        } else {
            $this->obj->debugRva = 0;
            $this->obj->debugSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 7) {
            $this->obj->architectureRva = $this->parseWord($offset);
            $this->obj->architectureSize = $this->parseWord($offset);
        } else {
            $this->obj->architectureRva = 0;
            $this->obj->architectureSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 8) {
            $this->obj->globalPtrRva = $this->parseWord($offset);
            $this->obj->globalPtrSize = $this->parseWord($offset);
        } else {
            $this->obj->globalPtrRva = 0;
            $this->obj->globalPtrSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 9) {
            $this->obj->tlsTableRva = $this->parseWord($offset);
            $this->obj->tlsTableSize = $this->parseWord($offset);
        } else {
            $this->obj->tlsTableRva = 0;
            $this->obj->tlsTableSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 10) {
            $this->obj->loadConfigTableRva = $this->parseWord($offset);
            $this->obj->loadConfigTableSize = $this->parseWord($offset);
        } else {
            $this->obj->loadConfigTableRva = 0;
            $this->obj->loadConfigTableSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 11) {
            $this->obj->boundImportRva = $this->parseWord($offset);
            $this->obj->boundImportSize = $this->parseWord($offset);
        } else {
            $this->obj->boundImportRva = 0;
            $this->obj->boundImportSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 12) {
            $this->obj->iatRva = $this->parseWord($offset);
            $this->obj->iatSize = $this->parseWord($offset);
        } else {
            $this->obj->iatRva = 0;
            $this->obj->iatSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 13) {
            $this->obj->delayImportDescriptorRva = $this->parseWord($offset);
            $this->obj->delayImportDescriptorSize = $this->parseWord($offset);
        } else {
            $this->obj->delayImportDescriptorRva = 0;
            $this->obj->delayImportDescriptorSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 14) {
            $this->obj->clrRuntimeHeaderRva = $this->parseWord($offset);
            $this->obj->clrRuntimeHeaderSize = $this->parseWord($offset);
        } else {
            $this->obj->clrRuntimeHeaderRva = 0;
            $this->obj->clrRuntimeHeaderSize = 0;
        }
        if ($this->obj->numberOfRvaAndSizes > 15) {
            $reserved = $this->parseWidth(8, $offset);
        } else {
            $reserved = 0;
        }

        // The following must all be zero. If any of them are not, we error.
        if (
            $this->obj->pointerToSymbolTable +
            $this->obj->numberOfSymbols +
            $this->obj->win32VersionValue +
            $this->obj->loaderFlags +
            $this->obj->architectureRva +
            $this->obj->architectureSize +
            $this->obj->globalPtrSize +
            $reserved
        ) {
            throw new \LogicException(
                'File is an invalid PE object. Data found in reserved field.'
            );
        }
    }

    protected function parsePeSectionHeaders() {
        // PE header position + PE header length + optional headers length
        $offset = $this->obj->lfanew + 24 + $this->obj->sizeOfOptionalHeader;
        $numEntries = $this->obj->numberOfSections;
        $sectionEntryLength = 40;

        for ($i = 0; $i < $numEntries; $i++) {
            $this->parsePeSection($offset + $sectionEntryLength * $i);
        }
    }

    /**
     * @see https://learn.microsoft.com/en-us/windows/win32/debug/pe-format#section-table-section-headers
     */
    protected function parsePeSection($offset) {
        $section = new Section();

        $section->offset = $offset;
        $section->name = $this->parseString($offset, 8);
        $section->virtualSize = $this->parseWord($offset);
        $section->virtualAddress = $this->parseWord($offset);
        $section->sizeOfRawData = $this->parseWord($offset);
        $section->pointerToRawData = $this->parseWord($offset);
        $section->pointerToRelocations = $this->parseWord($offset);
        $section->pointerToLineNumbers = $this->parseWord($offset);
        $section->numberOfRelocations = $this->parseHalf($offset);
        $section->numberOfLineNumbers = $this->parseHalf($offset);
        $section->characteristics = $this->parseWord($offset);

        $this->obj->sections[] = $section;
    }

    protected function parseImportTable(): void {
        // If we have no import table, skip.
        if (! $this->obj->importTableSize) {
            return;
        }

        $importTableOffset = $this->obj->translateRva($this->obj->importTableRva);
        $directoryEntrySize = ImportDirectoryEntry::SIZEOF;

        $section = new Section;
        $section->name = '.idata';
        $section->virtualAddress = $this->obj->importTableRva;
        $section->virtualSize = $this->obj->importTableSize;
        $section->sizeOfRawData = $this->obj->importTableSize;
        $section->pointerToRawData = $importTableOffset;

        $symbolCount = $section->sizeOfRawData / $directoryEntrySize - 1;
        $directories = [];

        // Loop through
        for ($i = 0; $i < $symbolCount; $i++) {
            $offset = ($i * $directoryEntrySize) + $importTableOffset;
            $directory = $this->parseImportDirectoryEntry($offset);
            $directories[] = $directory;
        }

        $lastOffset = ($symbolCount * $directoryEntrySize) + $importTableOffset;
        $expect = str_repeat("\x0", $directoryEntrySize);
        $found = substr($this->data, $lastOffset, $directoryEntrySize);

        // We should now be at a NUL symbol (all fields are 0). We will check.
        if ($found !== $expect) {
            throw new \RuntimeException(sprintf(
                'Import Data (.idata) section expected %d entries, however ' .
                'a NUL section was not encountered, indicating there may be ' .
                'more data.',
                $symbolCount
            ));
        }

        /**
         * Now we loop through each import directory, parse the lookup table and
         * merge in the new symbols.
         */
        foreach ($directories as $directory) {
            $symbols = $this->parseImportDirectoryEntryLookupTable($directory);
            $section->symbols = array_merge($section->symbols, $symbols);
        }

        $this->obj->sections[] = $section;
    }

    protected function parseDelayedLoadTable(): void {
        if (! $this->obj->delayImportDescriptorSize) {
            return;
        }

        throw new \Exception(sprintf(
            'Delayed Load Table parsing has not been implemented. Dll "%s" ' .
            'contains a delayed load import table. Please lodge a ticket.',
            basename($this->objFile->filepath)
        ));
    }

    protected function parseExportTable(): void {
        if (! $this->obj->exportTableSize) {
            return;
        }

        $edtOffset = $this->obj->translateRva($this->obj->exportTableRva);

        $section = new Section;
        $section->name = '.edata';
        $section->virtualAddress = $this->obj->exportTableRva;
        $section->virtualSize = $this->obj->exportTableSize;
        $section->sizeOfRawData = $this->obj->exportTableSize;
        $section->pointerToRawData = $edtOffset;

        // Parse the Export Directory Table.
        $edt = new \stdClass;
        $edt->flags = $this->parseWord($edtOffset);
        $edt->timeDateStamp = $this->parseWord($edtOffset);
        $edt->majorVersion = $this->parseHalf($edtOffset);
        $edt->minorVersion = $this->parseHalf($edtOffset);
        $edt->nameRva = $this->parseWord($edtOffset);
        $edt->ordinalBase = $this->parseWord($edtOffset);
        $edt->addressTableEntries = $this->parseWord($edtOffset);
        $edt->numberOfNamePointers = $this->parseWord($edtOffset);
        $edt->exportAddressTableRva = $this->parseWord($edtOffset);
        $edt->namePointerRva = $this->parseWord($edtOffset);
        $edt->ordinalTableRva = $this->parseWord($edtOffset);

        // Resolve the relative virtual addresses.
        $edt->nameAddress = $this->obj->translateRva($edt->nameRva);
        $edt->exportAddressTableAddress =
            $this->obj->translateRva($edt->exportAddressTableRva);
        $edt->namePointerAddress =
            $this->obj->translateRva($edt->namePointerRva);
        $edt->ordinalTableAddress =
            $this->obj->translateRva($edt->ordinalTableRva);

        for ($i = 0; $i < $edt->numberOfNamePointers; $i++) {
            $symbol = $this->parseExportTableEntry($edt, $i);
            $section->symbols[] = $symbol;
        }

        $this->obj->sections[] = $section;
    }

    protected function parseImportDirectoryEntry(int $offset) {
        $entry = new ImportDirectoryEntry;

        $entry->importLookupTableRva = $this->parseWord($offset);
        $entry->timeDateStamp = $this->parseWord($offset);
        $entry->forwarderChain = $this->parseWord($offset);
        $entry->nameRva = $this->parseWord($offset);
        $entry->importAddressTableRva = $this->parseWord($offset);

        // Resolve DLL name
        $offset = $this->obj->translateRva($entry->nameRva);
        $entry->dllFilename = $this->parseString($offset);

        return $entry;
    }

    protected function parseImportDirectoryEntryLookupTable(
        ImportDirectoryEntry $directory
    ): array {
        $offset = 0;
        $entrySize = $this->obj->is32Bit() ? 4 : 8;
        $lookupTableOffset = $this->obj->translateRva($directory->importLookupTableRva);

        /**
         * @var ImportLookupTableEntry[]
         */
        $symbols = [];

        for ($i = 0;; $i++) {
            $offset = ($i * $entrySize) + $lookupTableOffset;
            $symbol = $this->parseImportTableEntry($directory, $offset);

            // We have reached the end of our lookup table.
            if (is_null($symbol)) {
                break;
            }

            $symbol->dllFilename = $directory->dllFilename;
            $symbols[] = $symbol;
        }

        return $symbols;
    }

    protected function parseImportTableEntry(
        ImportDirectoryEntry $directory,
        int $offset
    ): ?Symbol {
        // Get the lookup table entry.
        $lookupTable = $this->parseXWord($offset);

        // We have reached the end of the import table.
        if (0 === $lookupTable) {
            return null;
        }

        $entry = new ImportLookupTableEntry;

        /**
         * The input lookup table is 32-bits on PE32 or 64-bits on PE32+. The
         * most significant bit (31 or 63) denotes whether we are importing by
         * an ordinal number, or a hint/name RVA.
         */
        $flagShift = ($this->obj->is32Bit() ? 31 : 63);
        $entry->isOrdinal = (bool) ($lookupTable >> $flagShift) & 1;

        if ($entry->isOrdinal) {
            $entry->nameString = $this->parseHalf($lookupTable);
        } else {
            $offset = $this->obj->translateRva($lookupTable);
            $hint = $this->parseHalf($offset);
            $name = $this->parseNullTerminatedString($offset);
            $entry->nameString = $name;
        }

        return $entry;
    }

    protected function parseExportTableEntry($entryDirectoryTable, $index) {
        $entry = new ExportTableEntry;

        /**
         * Calculate the offsets for each the name and ordinal, based upon
         * the $index parameter and the byte width of each the name and ordinal.
         */
        $nameOffset = $entryDirectoryTable->namePointerAddress + ($index * 4);
        $ordOffset = $entryDirectoryTable->ordinalTableAddress + ($index * 2);

        $entry->nameRva = $this->parseWord($nameOffset);
        $entry->ordinal = $this->parseWord($ordOffset);

        $eATA = $entryDirectoryTable->exportAddressTableAddress;
        $exportedAddressRva = $eATA + $entry->ordinal;

        $exportedNameAddress = $this->obj->translateRva($entry->nameRva);

        $entry->nameString = $this->parseString($exportedNameAddress);
        $entry->value = $this->parseWord($exportedAddressRva);

        /**
         * The exported address can be one of two formats. If the exported
         * address is outside the Export section, the field is an Export RVA,
         * referencing the RVA of a function. If the address is within the
         * Export section, it is a forwarder RVA, which names a symbol within
         * another DLL.
         */
        $startExportSection = $this->obj->exportTableRva;
        $endExportSection = $startExportSection + $this->obj->exportTableSize;

        if (
            $exportedAddressRva >= $startExportSection &&
            $exportedAddressRva <= $endExportSection
        ) {
            // We have a forwarder RVA.
            $forwarderStringAddress = $this->obj->translateRva($exportedAddressRva);
            $entry->isForwarder = true;
            $entry->forwarderString = $this->parseString($forwarderStringAddress);
        }

        return $entry;
    }

    protected function parseCoffSymbol(int $offset): Symbol {
        $symbol = new Symbol;

        $symbol->shortName = $this->parseString($offset, 8);
        $symbol->value = $this->parseWord($offset);
        $symbol->sectionNumber = $this->parseHalf($offset);
        $symbol->type = $this->parseHalf($offset);
        $symbol->storageClass = ord($this->data[$offset]);
        $symbol->numberOfAuxSymbols = ord($this->data[$offset+1]);

        // We must test if the shortname is a reference to the string table.
        $reference = unpack('vmask/vaddress', $symbol->shortName);

        if ("\x0" === $reference['mask']) {
            $stringOffset = $this->parseString($reference['address']);
            $stringAddress = $this->obj->coffStringTableOffset + $stringOffset;
            $symbol->name = $this->parseString($stringAddress);
        } else {
            $symbol->name = $symbol->shortName;
        }

        return $symbol;
    }

    protected function parseString(int &$offset, int $width = 0): string {
        $string = $this->parseNullTerminatedString($offset, $width);

        // Trim any NUL characters from the end of the string.
        $string = rtrim($string, "\x0");

        return $string;
    }
}
