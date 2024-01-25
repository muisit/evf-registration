<?php

namespace App\Models\Schemas;

/**
 * Basic return value
 *
 * @OA\Schema()
 */
class AccreditationTemplatePicture
{
    /**
     * File id
     *
     * @var string
     * @OA\Property()
     *
     */
    public string $file_id;

    /**
     * File extension
     *
     * @var string
     * @OA\Property()
     *
     */
    public string $file_ext;

    /**
     * File mime type
     *
     * @var string
     * @OA\Property()
     *
     */
    public string $file_mimetype;

    /**
     * Client side file name
     *
     * @var string
     * @OA\Property()
     *
     */
    public string $file_name;

    /**
     * Image width
     *
     * @var int
     * @OA\Property()
     *
     */
    public int $width;

    /**
     * Image height
     *
     * @var int
     * @OA\Property()
     *
     */
    public int $height;


    public function __construct($pictureSettings)
    {
        $this->file_id = $pictureSettings->file_id;
        $this->file_ext = $pictureSettings->file_ext;
        $this->file_name = $pictureSettings->file_name;
        $this->file_mimetype = $pictureSettings->file_mimetype ?? 'image/jpeg';
        $this->width = $pictureSettings->width;
        $this->height = $pictureSettings->height;
    }
}
