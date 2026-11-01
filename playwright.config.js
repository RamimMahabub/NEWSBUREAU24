import { defineConfig } from '@playwright/test';
export default defineConfig({
    testDir:'./tests/Browser', timeout:90000, workers:1,
    use:{actionTimeout:10000,baseURL:'http://127.0.0.1:8000', channel:'chrome', headless:true, trace:'retain-on-failure', screenshot:'only-on-failure'},
    outputDir:'artifacts/browser', reporter:[['list'],['json',{outputFile:'artifacts/browser-results.json'}]],
});
