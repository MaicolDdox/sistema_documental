<?php

namespace App\Console\Commands;

use GdImage;
use Illuminate\Console\Command;

class GenerateFaviconCommand extends Command
{
    protected $signature = 'branding:favicon {source=images/sena-logo.png}';

    protected $description = 'Genera favicon.ico, favicon-16x16.png, favicon-32x32.png y apple-touch-icon.png a partir del logo del SENA usando GD';

    public function handle(): int
    {
        $sourcePath = public_path($this->argument('source'));

        if (! file_exists($sourcePath)) {
            $this->error("No se encontró el archivo fuente: {$sourcePath}");

            return self::FAILURE;
        }

        $sourceImage = $this->loadImage($sourcePath);

        if ($sourceImage === null) {
            $this->error('No se pudo decodificar la imagen fuente.');

            return self::FAILURE;
        }

        // ─────────────────────────────────────────────────
        // PNGs en distintos tamaños
        // ─────────────────────────────────────────────────
        $sizes = [
            'favicon-16x16.png' => 16,
            'favicon-32x32.png' => 32,
            'apple-touch-icon.png' => 180,
        ];

        $generated = [];

        foreach ($sizes as $filename => $size) {
            $resized = $this->resize($sourceImage, $size);
            imagepng($resized, public_path($filename));
            imagedestroy($resized);
            $generated[] = $filename;
        }

        // ─────────────────────────────────────────────────
        // favicon.ico embebiendo el PNG de 32x32
        // ─────────────────────────────────────────────────
        $this->buildIcoFromPng(public_path('favicon-32x32.png'), public_path('favicon.ico'));
        $generated[] = 'favicon.ico';

        imagedestroy($sourceImage);

        $this->info('Archivos generados en public/:');
        foreach ($generated as $file) {
            $this->info(" - {$file}");
        }

        return self::SUCCESS;
    }

    private function loadImage(string $path): ?GdImage
    {
        $info = getimagesize($path);

        if ($info === false) {
            return null;
        }

        return match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path) ?: null,
            IMAGETYPE_PNG => imagecreatefrompng($path) ?: null,
            IMAGETYPE_GIF => imagecreatefromgif($path) ?: null,
            default => null,
        };
    }

    private function resize(GdImage $source, int $size): GdImage
    {
        $resized = imagecreatetruecolor($size, $size);
        imagealphablending($resized, true);
        imagesavealpha($resized, true);

        imagecopyresampled(
            $resized, $source,
            0, 0, 0, 0,
            $size, $size, imagesx($source), imagesy($source)
        );

        return $resized;
    }

    private function buildIcoFromPng(string $pngPath, string $icoPath): void
    {
        $pngData = file_get_contents($pngPath);
        $pngSize = strlen($pngData);

        // ICONDIR: reserved(2) + type(2) + count(2)
        $header = pack('vvv', 0, 1, 1);

        // ICONDIRENTRY: width(1) height(1) colorCount(1) reserved(1) planes(2) bitCount(2) bytesInRes(4) imageOffset(4)
        $entry = pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, $pngSize, 22);

        $handle = fopen($icoPath, 'wb');
        fwrite($handle, $header);
        fwrite($handle, $entry);
        fwrite($handle, $pngData);
        fclose($handle);
    }
}
