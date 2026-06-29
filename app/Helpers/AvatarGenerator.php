<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Imagick;
use ImagickDraw;
use ImagickDrawException;
use ImagickException;
use ImagickPixel;

class AvatarGenerator
{
    private const int   MIN_SIZE = 32;

    private const float FONT_SIZE_RATIO = 0.5;

    private const float TEXT_VERTICAL_OFFSET_RATIO = 0.25;

    private const array COLOR_SCHEMES = [
        ['color' => 'F39C12', 'background' => 'FDEBD0'],
        ['color' => 'E74C3C', 'background' => 'FADBD8'],
        ['color' => '2ECC71', 'background' => 'D5F5E3'],
        ['color' => '3498DB', 'background' => 'D6EAF8'],
        ['color' => '9B59B6', 'background' => 'E8DAEF'],
        ['color' => '34495E', 'background' => 'D5DBDB'],
        ['color' => 'E67E22', 'background' => 'F9E79F'],
        ['color' => '1ABC9C', 'background' => 'D1F2EB'],
        ['color' => '8E44AD', 'background' => 'E8DAEF'],
        ['color' => 'C0392B', 'background' => 'FADBD8'],
        ['color' => '2980B9', 'background' => 'D6EAF8'],
        ['color' => '27AE60', 'background' => 'D5F5E3'],
        ['color' => 'F1C40F', 'background' => 'FCF3CF'],
        ['color' => 'D35400', 'background' => 'FDEBD0'],
        ['color' => '7D3C98', 'background' => 'E8DAEF'],
        ['color' => 'AAB7B8', 'background' => 'D5DBDB'],
    ];

    public const string FORMAT_PNG = 'png';

    public const string FORMAT_SVG = 'svg';

    /**
     * Генерирует аватар для пользователя
     *
     * @param  string  $username  Имя пользователя
     * @param  int  $size  Размер аватара в пикселях
     * @param  string  $format  Формат изображения (png или svg)
     * @return array ['data' => string, 'contentType' => string]
     *
     * @throws ImagickDrawException
     * @throws ImagickException
     */
    public function generate(string $username, int $size, string $format = self::FORMAT_PNG): array
    {
        $size = max($size, self::MIN_SIZE);
        $colorScheme = $this->getColorScheme($username);
        $initials = $this->extractInitials($username);

        return match ($format) {
            self::FORMAT_SVG => $this->generateSvg($initials, $colorScheme, $size, $username),
            self::FORMAT_PNG => $this->generatePng($initials, $colorScheme, $size),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
        };
    }

    /**
     * Получает цветовую схему на основе имени пользователя
     */
    private function getColorScheme(string $username): array
    {
        $index = crc32($username) % count(self::COLOR_SCHEMES);

        return self::COLOR_SCHEMES[$index];
    }

    /**
     * Извлекает инициалы из имени пользователя
     */
    private function extractInitials(string $username): string
    {
        $words = explode(' ', trim($username));

        if (count($words) > 1) {
            $initials = mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1);
        } else {
            $initials = mb_substr($words[0], 0, 1).mb_substr($words[0], 1, 1);
        }

        return Str::upper($initials);
    }

    /**
     * Генерирует SVG аватар
     */
    private function generateSvg(string $initials, array $colorScheme, int $size, string $seed): array
    {
        $svgData = radiance()
            ->saturation(0)
            ->contrast(0)
            ->fadeDistance(0)
            ->enablePixelPattern(false)
            ->seed($seed)
            ->text($initials)
            ->baseColor($colorScheme['color'])
            ->solidColor($colorScheme['background'])
            ->size($size)
            ->toSvg();

        return [
            'data' => $svgData,
            'contentType' => 'image/svg+xml',
        ];
    }

    /**
     * Генерирует PNG аватар с использованием Imagick
     *
     * @throws ImagickDrawException
     * @throws ImagickException
     */
    private function generatePng(string $initials, array $colorScheme, int $size): array
    {
        $fontColor = $this->hexToRgb($colorScheme['color']);
        $backgroundColor = $this->hexToRgb($colorScheme['background']);

        $image = new Imagick;
        $image->newImage(
            $size,
            $size,
            new ImagickPixel("rgb({$backgroundColor['r']},{$backgroundColor['g']},{$backgroundColor['b']})"),
        );
        $image->setImageFormat('png');

        $draw = new ImagickDraw;
        $draw->setFont(public_path('fonts/OpenSans.ttf'));
        $draw->setFontSize($size * self::FONT_SIZE_RATIO);
        $draw->setFillColor(new ImagickPixel("rgb({$fontColor['r']},{$fontColor['g']},{$fontColor['b']})"));
        $draw->setTextAlignment(Imagick::ALIGN_CENTER);

        $metrics = $image->queryFontMetrics($draw, $initials);
        $textHeight = $metrics['textHeight'];

        $x = $size / 2;
        $y = ($size / 2) + ($textHeight * self::TEXT_VERTICAL_OFFSET_RATIO);

        $draw->annotation($x, $y, $initials);
        $image->drawImage($draw);

        $imageData = $image->getImageBlob();

        $image->clear();
        $image->destroy();

        return [
            'data' => $imageData,
            'contentType' => 'image/png',
        ];
    }

    /**
     * Конвертирует HEX цвет в RGB массив
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }
}
