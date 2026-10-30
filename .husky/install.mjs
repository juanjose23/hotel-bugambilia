import { existsSync } from 'node:fs';

if (
    process.env.HUSKY !== '0' &&
    !process.env.CI &&
    process.env.NODE_ENV !== 'production' &&
    existsSync('.git')
) {
    const husky = (await import('husky')).default;
    const error = husky();

    if (error) {
        console.error(error);
        process.exitCode = 1;
    }
}
