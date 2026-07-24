/**
 * Script para convertir imágenes JPG/PNG a WebP usando sharp.
 * Ejecutar: node scripts/convert-images.mjs
 */
import { readdir, stat } from 'node:fs/promises';
import { join, extname, basename } from 'node:path';
import sharp from 'sharp';

const IMG_DIR = 'public/images';
const EXTENSIONS = ['.jpg', '.jpeg', '.png'];
const MAX_WIDTH = 1920;
const WEBP_QUALITY = 80;

async function convertImages() {
    const files = await readdir(IMG_DIR);
    const imageFiles = files.filter((f) =>
        EXTENSIONS.includes(extname(f).toLowerCase()),
    );

    console.log(`Found ${imageFiles.length} images to convert...\n`);

    for (const file of imageFiles) {
        const inputPath = join(IMG_DIR, file);
        const outputName = basename(file, extname(file)) + '.webp';
        const outputPath = join(IMG_DIR, outputName);

        try {
            const info = await stat(inputPath);
            const sizeMB = (info.size / 1024 / 1024).toFixed(2);

            await sharp(inputPath)
                .resize({ width: MAX_WIDTH, withoutEnlargement: true })
                .webp({ quality: WEBP_QUALITY })
                .toFile(outputPath);

            const outInfo = await stat(outputPath);
            const outSizeMB = (outInfo.size / 1024 / 1024).toFixed(2);
            const savings = (
                ((info.size - outInfo.size) / info.size) *
                100
            ).toFixed(1);

            console.log(
                `✓ ${file} (${sizeMB} MB) → ${outputName} (${outSizeMB} MB) — ${savings}% smaller`,
            );
        } catch (err) {
            console.error(`✗ Error converting ${file}:`, err.message);
        }
    }

    console.log('\nDone!');
}

convertImages();
