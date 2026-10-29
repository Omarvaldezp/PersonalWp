// Genera src/manual-claude/manual-de-claude.pdf a partir de la página del manual,
// con los mismos estilos de impresión que usa el navegador.
//
// Una sola vez:   npm install --no-save playwright && npx playwright install chromium
// Cada vez que cambie el manual:   node scripts/pdf-manual-claude.mjs
// Después sube la fecha de ?v= en los enlaces "Descargar PDF" del manual
// (grep -n "manual-de-claude.pdf" src/manual-claude/index.html).
import { createRequire } from 'node:module';
import { fileURLToPath, pathToFileURL } from 'node:url';
import path from 'node:path';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const raiz = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const pagina = path.join(raiz, 'src/manual-claude/index.html');
const salida = path.join(raiz, 'src/manual-claude/manual-de-claude.pdf');

const navegador = await chromium.launch();
const hoja = await navegador.newPage();
await hoja.goto(pathToFileURL(pagina).href, { waitUntil: 'networkidle' });
await hoja.evaluate(() => document.fonts.ready);

// Abierta desde el disco, una liga como "/" apuntaría a la computadora y no al sitio
await hoja.evaluate(() => {
  document.querySelectorAll('a[href^="/"]').forEach((a) => {
    a.href = 'https://omarvaldez.com' + a.getAttribute('href');
  });
});

await hoja.pdf({
  path: salida,
  preferCSSPageSize: true,
  printBackground: true,
  tagged: true,
  outline: true,
  displayHeaderFooter: true,
  headerTemplate: '<span></span>',
  footerTemplate:
    '<div style="width:100%; padding:0 17mm; display:flex; justify-content:space-between; font-family:Arial, sans-serif; font-size:8px; color:#64748b;">' +
    '<span>Manual de Claude para investigación de posgrado · omarvaldez.com/manual-claude</span>' +
    '<span><span class="pageNumber"></span> / <span class="totalPages"></span></span></div>',
});

await navegador.close();
console.log('PDF generado:', path.relative(raiz, salida));
