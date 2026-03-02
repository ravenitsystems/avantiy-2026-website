#!/usr/bin/env node
/**
 * Generate a full favicon set from resources/icons/avantiy-icon.svg
 * Outputs to public/ and public/images/
 * Requires: npm install -D sharp to-ico
 */
import { readFileSync, writeFileSync, mkdirSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const svgPath = join(root, 'resources/icons/avantiy-icon.svg');
const publicDir = join(root, 'public');
const imagesDir = join(root, 'public/images');

const SIZES = [
    { w: 16, name: 'favicon-16x16.png' },
    { w: 32, name: 'favicon-32x32.png' },
    { w: 48, name: 'favicon-48x48.png' },
    { w: 180, name: 'apple-touch-icon.png' },
    { w: 192, name: 'android-chrome-192x192.png' },
    { w: 512, name: 'android-chrome-512x512.png' },
];

const ICO_SIZES = [16, 32, 48];

async function main() {
    const sharp = (await import('sharp')).default;
    const toIco = (await import('to-ico')).default;

    const svg = readFileSync(svgPath);
    mkdirSync(imagesDir, { recursive: true });

    const pngBuffers = {};
    for (const { w, name } of SIZES) {
        const buf = await sharp(svg)
            .resize(w, w)
            .png()
            .toBuffer();
        pngBuffers[w] = buf;
        const outPath = join(imagesDir, name);
        writeFileSync(outPath, buf);
        console.log(`Wrote ${name}`);
    }

    // favicon.ico (multi-size) in public root
    const icoBuffers = ICO_SIZES.map((s) => pngBuffers[s]).filter(Boolean);
    const ico = await toIco(icoBuffers);
    writeFileSync(join(publicDir, 'favicon.ico'), ico);
    console.log('Wrote favicon.ico');

    // Single favicon.png at 32x32 for simple use
    writeFileSync(join(imagesDir, 'favicon.png'), pngBuffers[32]);
    console.log('Wrote images/favicon.png');

    // SVG favicon (modern browsers)
    const svgOut = join(imagesDir, 'favicon.svg');
    writeFileSync(svgOut, readFileSync(svgPath));
    console.log('Wrote images/favicon.svg');

    console.log('Favicon set generated.');
}

main().catch((err) => {
    console.error(err);
    process.exit(1);
});
