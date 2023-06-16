<?php

namespace App\DocumentConversion;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;

class ConversionRequest implements JsonSerializable, Arrayable
{

    /**
     * Create a new conversion request.
     *
     * @param  string  $job
     * @return void
     */
    public function __construct(
        public string $key,
        public string $url,
        public string $mimetype,
        public string $title,
    )
    {
        
    }

    /**
     * Get the JSON serializable representation of the object.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return [
            'key' => $this->key,
            "filetype" => "docx",
            'url' => $this->url,
        ];
    }
}
