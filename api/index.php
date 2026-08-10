<?php

/**
 * Vercel serverless entrypoint (vercel-php runtime).
 *
 * Boots Laravel exactly like public/index.php does. Vercel rewrites every
 * request to this file (see vercel.json); static assets in public/ are still
 * served by Vercel directly.
 */
require __DIR__.'/../public/index.php';
