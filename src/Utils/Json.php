<?php

namespace CommentService\Utils;

class Json
{
    /**
     * @param string $data
     * @return array
     * @throws \JsonException
     */
    public static function decode(string $data): array
    {
        return \json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array $data
     * @return string
     * @throws \JsonException
     */
    public static function encode(array $data): string
    {
        return \json_encode($data, JSON_THROW_ON_ERROR);
    }
}