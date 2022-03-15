<?php

namespace PHPObjectSymbolResolver\MachO;

class Command {
    public int $cmd;
    public int $offset;

	const LC_REQ_DYLD = 0x80000000;

	const LC_SEGMENT = 0x1; // segment of this file to be mapped
	const LC_SYMTAB = 0x2; // link-edit stab symbol table info
	const LC_SYMSEG = 0x3; // link-edit gdb symbol table info (obsolete)
	const LC_THREAD = 0x4; // thread
	const LC_UNIXTHREAD = 0x5; // unix thread (includes a stack)
	const LC_LOADFVMLIB = 0x6; // load a specified fixed VM shared library
	const LC_IDFVMLIB = 0x7; // fixed VM shared library identification
	const LC_IDENT = 0x8; // object identification info (obsolete)
	const LC_FVMFILE = 0x9; // fixed VM file inclusion (internal use)
	const LC_PREPAGE = 0xa; // prepage command (internal use)
	const LC_DYSYMTAB = 0xb; // dynamic link-edit symbol table info
	const LC_LOAD_DYLIB = 0xc; // load a dynamically linked shared library
	const LC_ID_DYLIB = 0xd; // dynamically linked shared lib ident
	const LC_LOAD_DYLINKER = 0xe; // load a dynamic linker
	const LC_ID_DYLINKER = 0xf; // dynamic linker identification
	const LC_PREBOUND_DYLIB = 0x10; // modules prebound for a dynamically
	const LC_ROUTINES = 0x11; // image routines
	const LC_SUB_FRAMEWORK = 0x12; // sub framework
	const LC_SUB_UMBRELLA = 0x13; // sub umbrella
	const LC_SUB_CLIENT = 0x14; // sub client
	const LC_SUB_LIBRARY = 0x15; // sub library
	const LC_TWOLEVEL_HINTS = 0x16; // two-level namespace lookup hints
	const LC_PREBIND_CKSUM = 0x17; // prebind checksum
	const LC_LOAD_WEAK_DYLIB = (0x18 | self::LC_REQ_DYLD); // load a dynamically linked shared library that is allowed to be missing (all symbols are weak imported).
	const LC_SEGMENT_64 = 0x19; // 64-bit segment of this file to be mapped
	const LC_ROUTINES_64 = 0x1a; // 64-bit image routines
	const LC_UUID = 0x1b; // the uuid
	const LC_RPATH = (0x1c | self::LC_REQ_DYLD); // runpath additions
	const LC_CODE_SIGNATURE = 0x1d; // local of code signature
	const LC_SEGMENT_SPLIT_INFO = 0x1e; // local of info to split segments
	const LC_REEXPORT_DYLIB = (0x1f | self::LC_REQ_DYLD); // load and re-export dylib
	const LC_LAZY_LOAD_DYLIB = 0x20; // delay load of dylib until first use
	const LC_ENCRYPTION_INFO = 0x21; // encrypted segment information
	const LC_DYLD_INFO = 0x22; // compressed dyld information
	const LC_DYLD_INFO_ONLY = (0x22 | self::LC_REQ_DYLD); // compressed dyld information only
	const LC_LOAD_UPWARD_DYLIB = (0x23 | self::LC_REQ_DYLD); // load upward dylib
	const LC_VERSION_MIN_MACOSX = 0x24; // build for MacOSX min OS version
	const LC_VERSION_MIN_IPHONEOS = 0x25; // build for iPhoneOS min OS version
	const LC_FUNCTION_STARTS = 0x26; // compressed table of function start addresses
	const LC_DYLD_ENVIRONMENT = 0x27; // string for dyld to treat like environment variable
	const LC_MAIN = (0x28|self::LC_REQ_DYLD); // replacement for LC_UNIXTHREAD
	const LC_DATA_IN_CODE = 0x29; // table of non-instructions in __text
	const LC_SOURCE_VERSION = 0x2A; // source version used to build binary
	const LC_DYLIB_CODE_SIGN_DRS = 0x2B; // Code signing DRs copied from linked dylibs
	const LC_ENCRYPTION_INFO_64 = 0x2C; // 64-bit encrypted segment information
	const LC_LINKER_OPTION = 0x2D; // linker options in MH_OBJECT files
	const LC_LINKER_OPTIMIZATION_HINT = 0x2E; // optimization hints in MH_OBJECT files
	const LC_VERSION_MIN_TVOS = 0x2F; // build for AppleTV min OS version
	const LC_VERSION_MIN_WATCHOS = 0x30; // build for Watch min OS version
	const LC_NOTE = 0x31; // arbitrary data included within a Mach-O file
	const LC_BUILD_VERSION = 0x32; // build for platform min OS version
	const LC_DYLD_EXPORTS_TRIE = (0x33 | self::LC_REQ_DYLD); // used with linkedit_data_command, payload is trie
	const LC_DYLD_CHAINED_FIXUPS = (0x34 | self::LC_REQ_DYLD); // used with linkedit_data_command

	public $parsed;
}