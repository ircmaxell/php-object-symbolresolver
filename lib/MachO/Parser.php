<?php

namespace PHPObjectSymbolResolver\MachO;

class Parser extends \PHPObjectSymbolResolver\Parser {
	const HEADER_32 = "\xce\xfa\xed\xfe";
	const HEADER_64 = "\xcf\xfa\xed\xfe";

	public static function isValidMagic($magic) {
		return $magic === self::HEADER_32 || $magic === self::HEADER_64 || $magic === strrev(self::HEADER_32) || $magic === strrev(self::HEADER_64);
	}

	public function parse(string $file): ObjectFile {
		$this->data = file_get_contents($file);
		if (strlen($this->data) < 16) {
			throw new \LogicException("File is too short to be an ELF file");
		}
		$this->obj = new ObjectFile;
		$offset = $this->parseMachOHeader();
		for ($i = 0; $i < $this->obj->numOfCmds; ++$i) {
			$this->obj->commands[] = $this->parseCommand($offset);
		}
		foreach ($this->obj->commands as $command) {
			$this->evaluateCommand($command);
		}

		$this->data = '';
		return $this->obj;
	}

	protected function parseMachOHeader() {
		$magic = substr($this->data, 0, 4);
		if (!self::isValidMagic($magic)) {
			throw new \LogicException("File is not in Mach-O format");
		}
		$this->obj->isLSB = $magic[1] === "\xfa";
		$this->obj->class = $magic === self::HEADER_64 || $magic === strrev(self::HEADER_64) ? ObjectFile::CLASS64 : ObjectFile::CLASS32;

		$offset = 4;
		$this->obj->cpuType = $this->parseWord($offset);
		$this->obj->cpuSubtype = $this->parseWord($offset);
		$this->obj->fileType = $this->parseWord($offset);
		$this->obj->numOfCmds = $this->parseWord($offset);
		$this->obj->sizeOfCmds = $this->parseWord($offset);
		$this->obj->flags = $this->parseWord($offset);

		if ($this->obj->is64Bit()) {
			$this->obj->reserved = $this->parseWord($offset);
		}

		return $offset;
	}

	protected function parseCommand(int &$offset) {
		$cmd = new Command;
		$cmd->cmd = $this->parseWord($offset);
		$size = $this->parseWord($offset);
		$cmd->offset = $offset;
		$offset += $size - 8;
		return $cmd;
	}

	protected function evaluateCommand(Command $command) {
		switch ($command->cmd) {
			case Command::LC_SYMTAB:
				$symtab = new SymtabCommand;
				$command->parsed = $symtab;

				$offset = $command->offset;
				$symtab->symOff = $this->parseWord($offset);
				$symtab->nSyms = $this->parseWord($offset);
				$symtab->strOff = $this->parseWord($offset);
				$symtab->strSize = $this->parseWord($offset);

				$offset = $symtab->symOff;
				for ($i = 0; $i < $symtab->nSyms; ++$i) {
					$symbol = new Symbol;
					$symtab->symbols[] = $symbol;
					$symbol->nStrx = $this->parseWord($offset);
					$symbol->nType = $this->parseUChar($offset);
					$symbol->nSect = $this->parseUChar($offset);
					$symbol->nDesc = $this->parseHalf($offset);
					$symbol->nValue = $this->parseXWord($offset);

					if ($symbol->nStrx) {
						$nameOffset = $symtab->strOff + $symbol->nStrx;
						$nameDelimiter = strpos($this->data, "\0", $nameOffset);
						$symbol->name = substr($this->data, $nameOffset, $nameDelimiter - $nameOffset);
					} else {
						$symbol->name = "";
					}
				}
				break;

			case Command::LC_SEGMENT:
			case Command::LC_SEGMENT_64:
				$offset = $command->offset;
				$segment = new Segment;
				$command->parsed = $segment;
				$this->obj->segments[] = $segment;
				$segment->name = $this->parseNullTerminatedString(16, $offset);
				$segment->vmaddr = $this->parseXWord($offset);
				$segment->vmsize = $this->parseXWord($offset);
				$segment->fileoff = $this->parseXWord($offset);
				$segment->filesize = $this->parseXWord($offset);
				$segment->maxprot = $this->parseWord($offset);
				$segment->initprot = $this->parseWord($offset);
				$segment->nSects = $this->parseWord($offset);
				$segment->flags = $this->parseWord($offset);

				for ($i = 0; $i < $segment->nSects; ++$i) {
					$section = new Section;
					$segment->sections[] = $section;
					$section->name = $this->parseNullTerminatedString(16, $offset);
					$section->segname = $this->parseNullTerminatedString(16, $offset);
					$section->addr = $this->parseXWord($offset);
					$section->size = $this->parseXWord($offset);
					$section->offset = $this->parseWord($offset);
					$section->align = $this->parseWord($offset);
					$section->reloff = $this->parseWord($offset);
					$section->nreloc = $this->parseWord($offset);
					$section->flags = $this->parseWord($offset);
					$section->reserved1 = $this->parseWord($offset);
					$section->reserved2 = $this->parseWord($offset);
				}
				break;
		}
	}
}