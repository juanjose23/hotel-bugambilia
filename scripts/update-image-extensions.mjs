import { readdir, readFile, writeFile } from 'node:fs/promises';
import { join } from 'node:path';

const TARGET_DIR = 'resources/js';
const REPLACEMENTS = [
    { from: 'hero-main.jpg', to: 'hero-main.webp' },
    { from: 'pool-front-view.jpg', to: 'pool-front-view.webp' },
    { from: 'terrace.jpg', to: 'terrace.webp' },
    { from: 'group-room.jpg', to: 'group-room.webp' },
    { from: 'bathroom.jpg', to: 'bathroom.webp' },
    { from: 'facebook-cover.jpg', to: 'facebook-cover.webp' },
    { from: 'hero-secondary.jpg', to: 'hero-secondary.webp' },
    { from: 'logo-claro.png', to: 'logo-claro.webp' },
    { from: 'logo-dark.png', to: 'logo-dark.webp' },
    { from: 'main-room.jpg', to: 'main-room.webp' },
    { from: 'pool-scaled.jpg', to: 'pool-scaled.webp' },
    { from: 'room-detail.jpg', to: 'room-detail.webp' },
    { from: 'service-bartender.png', to: 'service-bartender.webp' },
    { from: 'service-events.png', to: 'service-events.webp' },
    { from: 'service-kitchen.png', to: 'service-kitchen.webp' },
    { from: 'service-pool.png', to: 'service-pool.webp' },
];

async function scanAndReplace(dir) {
    const entries = await readdir(dir, { withFileTypes: true });

    for (const entry of entries) {
        const fullPath = join(dir, entry.name);

        if (entry.isDirectory()) {
            await scanAndReplace(fullPath);
        } else if (
            entry.isFile() &&
            (entry.name.endsWith('.tsx') || entry.name.endsWith('.ts'))
        ) {
            let content = await readFile(fullPath, 'utf-8');
            let modified = false;

            for (const rep of REPLACEMENTS) {
                if (content.includes(rep.from)) {
                    content = content.replaceAll(rep.from, rep.to);
                    modified = true;
                }
            }

            if (modified) {
                await writeFile(fullPath, content, 'utf-8');
                console.log(`Updated image references in: ${fullPath}`);
            }
        }
    }
}

scanAndReplace(TARGET_DIR).then(() =>
    console.log('Done scanning resources/js!'),
);
