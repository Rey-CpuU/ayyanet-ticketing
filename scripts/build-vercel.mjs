// Vercel build helper.
// Copies the built `public/` directory (Laravel + Vite output) into a root
// `dist/` directory so the deployment always produces an output directory,
// no matter which one Vercel resolves (vercel.json#outputDirectory or the
// Vite framework default "dist").
import { cpSync, rmSync } from 'node:fs';

rmSync('dist', { recursive: true, force: true });
cpSync('public', 'dist', { recursive: true });
console.log('Copied public/ -> dist/');
