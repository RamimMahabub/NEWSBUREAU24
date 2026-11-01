import { chromium } from '@playwright/test';
import { mkdir } from 'node:fs/promises';
await mkdir('artifacts', {recursive:true});
const browser = await chromium.launch({channel:'chrome', headless:true});
const page = await browser.newPage({viewport:{width:1440,height:1100}});
page.on('pageerror', error => console.error('Browser error:', error.message));
await page.goto('http://127.0.0.1:8000/bn');
await page.evaluate(() => document.fonts.ready);
await page.screenshot({path:'artifacts/homepage-desktop.png',fullPage:true});
console.log(await page.title());
console.log('Status text:', (await page.locator('body').innerText()).slice(0,200));
for (const width of [1024,768,375,320]) {
    await page.setViewportSize({width,height:1000});
    await page.screenshot({path:`artifacts/homepage-${width}.png`,fullPage:true});
    console.log(width, 'overflow:', await page.evaluate(() => document.documentElement.scrollWidth > innerWidth));
}
await browser.close();
