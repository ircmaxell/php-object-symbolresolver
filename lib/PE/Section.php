<?php

namespace PHPObjectSymbolResolver\PE;

/**
 * @see https://learn.microsoft.com/en-us/windows/win32/debug/pe-format#section-table-section-headers
 */
class Section {
    /**
     * An 8-byte, null-padded UTF-8 encoded string. If the string is exactly 8
     * characters long, there is no terminating null. For longer names, this
     * field contains a slash (/) that is followed by an ASCII representation
     * of a decimal number that is an offset into the string table. Executable
     * images do not use a string table and do not support section names longer
     * than 8 characters. Long names in object files are truncated if they are
     * emitted to an executable file.
     */
    public string $nameString;

    /**
     * The total size of the section when loaded into memory. If this value is
     * greater than SizeOfRawData, the section is zero-padded. This field is
     * valid only for executable images and should be set to zero for object
     * files.
     */
    public int $virtualSize;

    /**
     * For executable images, the address of the first byte of the section
     * relative to the image base when the section is loaded into memory. For
     * object files, this field is the address of the first byte before
     * relocation is applied; for simplicity, compilers should set this to zero.
     * Otherwise, it is an arbitrary value that is subtracted from offsets
     * during relocation.
     */
    public int $virtualAddress;

    /**
     * The size of the section (for object files) or the size of the initialized
     * data on disk (for image files). For executable images, this must be a
     * multiple of FileAlignment from the optional header. If this is less than
     * VirtualSize, the remainder of the section is zero-filled. Because the
     * SizeOfRawData field is rounded but the VirtualSize field is not, it is
     * possible for SizeOfRawData to be greater than VirtualSize as well. When
     * a section contains only uninitialized data, this field should be zero.
     */
    public int $sizeOfRawData;

    /**
     * The file pointer to the first page of the section within the COFF file.
     * For executable images, this must be a multiple of FileAlignment from the
     * optional header. For object files, the value should be aligned on a
     * 4-byte boundary for best performance. When a section contains only
     * uninitialized data, this field should be zero.
     */
    public int $pointerToRawData;

    /**
     * The file pointer to the beginning of relocation entries for the section.
     * This is set to zero for executable images or if there are no relocations.
     */
    public int $pointerToRelocations;

    /**
     * The file pointer to the beginning of line-number entries for the section.
     * This is set to zero if there are no COFF line numbers. This value should
     * be zero for an image because COFF debugging information is deprecated.
     */
    public int $pointerToLineNumbers;

    /**
     * The number of relocation entries for the section. This is set to zero for
     * executable images.
     */
    public int $numberOfRelocations;

    /**
     * The number of line-number entries for the section. This value should be
     * zero for an image because COFF debugging information is deprecated.
     */
    public int $numberOfLineNumbers;

    /**
     * The flags that describe the characteristics of the section.
     */
    public int $characteristics;

    /**
     * Section Flags for the Characteristics member.
     */
    const IMAGE_SCN_TYPE_NO_PAD = 0x00000008;
    const IMAGE_SCN_CNT_CODE = 0x00000020;
    const IMAGE_SCN_CNT_INITIALIZED_DATA = 0x00000040;
    const IMAGE_SCN_CNT_UNINITIALIZED_DATA = 0x00000080;
    const IMAGE_SCN_LNK_OTHER = 0x00000100;
    const IMAGE_SCN_LNK_INFO = 0x00000200;
    const IMAGE_SCN_LNK_REMOVE = 0x00000800;
    const IMAGE_SCN_LNK_COMDAT = 0x00001000;
    const IMAGE_SCN_GPREL = 0x00008000;
    const IMAGE_SCN_MEM_PURGEABLE = 0x00020000;
    const IMAGE_SCN_MEM_16BIT = 0x00020000;
    const IMAGE_SCN_MEM_LOCKED = 0x00040000;
    const IMAGE_SCN_MEM_PRELOAD = 0x00080000;
    const IMAGE_SCN_ALIGN_1BYTES = 0x00100000;
    const IMAGE_SCN_ALIGN_2BYTES = 0x00200000;
    const IMAGE_SCN_ALIGN_4BYTES = 0x00300000;
    const IMAGE_SCN_ALIGN_8BYTES = 0x00400000;
    const IMAGE_SCN_ALIGN_16BYTES = 0x00500000;
    const IMAGE_SCN_ALIGN_32BYTES = 0x00600000;
    const IMAGE_SCN_ALIGN_64BYTES = 0x00700000;
    const IMAGE_SCN_ALIGN_128BYTES = 0x00800000;
    const IMAGE_SCN_ALIGN_256BYTES = 0x00900000;
    const IMAGE_SCN_ALIGN_512BYTES = 0x00A00000;
    const IMAGE_SCN_ALIGN_1024BYTES = 0x00B00000;
    const IMAGE_SCN_ALIGN_2048BYTES = 0x00C00000;
    const IMAGE_SCN_ALIGN_4096BYTES = 0x00D00000;
    const IMAGE_SCN_ALIGN_8192BYTES = 0x00E00000;
    const IMAGE_SCN_LNK_NRELOC_OVFL = 0x01000000;
    const IMAGE_SCN_MEM_DISCARDABLE = 0x02000000;
    const IMAGE_SCN_MEM_NOT_CACHED = 0x04000000;
    const IMAGE_SCN_MEM_NOT_PAGED = 0x08000000;
    const IMAGE_SCN_MEM_SHARED = 0x10000000;
    const IMAGE_SCN_MEM_EXECUTE = 0x20000000;
    const IMAGE_SCN_MEM_READ = 0x40000000;
    const IMAGE_SCN_MEM_WRITE = 0x80000000;

    /**
     * @var Symbol[]
     */
    public $symbols = [];
}
