import { test, expect } from '@playwright/test';
import { readFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
const accounts=JSON.parse(readFileSync('.tools/local-access.json','utf8'));
test.afterAll(()=>execFileSync('.tools/php/php.exe',['scripts/reset-browser-fixtures.php']));
async function login(page,role) {
    await page.goto('/newsroom/login');
    await page.getByLabel('Email address').fill(accounts[role].email);
    await page.locator('input[autocomplete="current-password"]').fill(accounts[role].password);
    await page.getByRole('button',{name:'Sign in',exact:true}).click();
    await expect(page).toHaveURL(/\/newsroom$/);
}

test('responsive homepage, search, article and full English experience',async({page})=>{
    const errors=[];page.on('pageerror',e=>errors.push(e.message));
    await page.goto('/bn');await expect(page.locator('h1')).toBeVisible();
    for(const width of [1440,1024,768,375,320]) {
        await page.setViewportSize({width,height:900});
        expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBeTruthy();
    }
    await page.setViewportSize({width:1440,height:1000});
    await page.getByRole('link',{name:'EN',exact:true}).click();
    await expect(page.locator('h1')).toContainText('Inside an investigation');
    await page.locator('.lead-text h1 a').click();
    await expect(page.locator('.article-body')).toContainText('demonstration content');
    await expect(page.getByRole('link',{name:'বাংলায় পড়ুন'})).toBeVisible();
    await page.goto('/bn/search?q=শীতলক্ষ্যা');
    await expect(page.locator('.search-results .story')).toHaveCount(2);
    expect(errors).toEqual([]);
});

test('reporter writes and submits; editor publishes and promotes the story',async({page,browser})=>{
    const errors=[];page.on('pageerror',e=>errors.push(e.message));
    await login(page,'reporter');
    await page.goto('/newsroom/articles/create');
    await page.getByLabel('Headline').first().fill('ব্রাউজার পরীক্ষা: জনস্বার্থের নমুনা প্রতিবেদন');
    await page.getByLabel('Summary').first().fill('এটি স্বয়ংক্রিয় পরীক্ষার নমুনা।');
    const editor=page.locator('[contenteditable="true"]').first();
    await editor.fill('এটি পরীক্ষার জন্য লেখা একটি নমুনা প্রতিবেদন। প্রকৃত সংবাদ নয়।');
    await page.getByLabel('Status').selectOption('review');
    await page.getByRole('button',{name:'Upload image',exact:true}).click();
    const imageDialog=page.getByRole('dialog');
    await imageDialog.locator('input[type="file"]').setInputFiles('public/images/river.webp');
    await imageDialog.getByLabel('Bangla alternative text').fill('Illustrative river image');
    await imageDialog.getByLabel('Photo credit').count().then(async n=>{if(n) await imageDialog.getByLabel('Photo credit').fill('Supplied visual reference');});
    await imageDialog.getByRole('button',{name:'Create',exact:true}).click();
    await expect(imageDialog).not.toBeVisible();
    await page.getByRole('button',{name:'Create',exact:true}).first().click();
    await expect(page).toHaveURL(/articles\/\d+\/edit/);
    const editUrl=page.url();
    await page.getByLabel('Headline').first().fill('ব্রাউজার পরীক্ষা: সম্পাদিত নমুনা প্রতিবেদন');
    await expect(page.getByText(/Working copy autosaved at/)).toBeVisible();
    await page.reload();
    await expect(page.getByLabel('Headline').first()).toHaveValue('ব্রাউজার পরীক্ষা: জনস্বার্থের নমুনা প্রতিবেদন');
    await page.getByRole('button',{name:'Restore autosave'}).click();
    await page.getByRole('button',{name:'Confirm',exact:true}).click();
    await expect(page.getByLabel('Headline').first()).toHaveValue('ব্রাউজার পরীক্ষা: সম্পাদিত নমুনা প্রতিবেদন');
    await page.getByRole('button',{name:'Save changes',exact:true}).click();
    await expect(page.getByText('Saved',{exact:true})).toBeVisible();
    const context=await browser.newContext(); const editorPage=await context.newPage();
    editorPage.on('pageerror',e=>errors.push(e.message));
    await login(editorPage,'editor'); await editorPage.goto(editUrl);
    await editorPage.getByLabel('Status').selectOption('published');
    await editorPage.getByLabel('Publish this language at').first().fill('2026-10-24T12:00');
    await editorPage.getByRole('button',{name:'Save changes',exact:true}).click();
    await expect(editorPage.getByText('Saved',{exact:true})).toBeVisible();
    await editorPage.goto('/newsroom/frontpage-builder');
    const libraryStory=editorPage.locator('.nb-library-story').filter({hasText:'ব্রাউজার পরীক্ষা: সম্পাদিত নমুনা প্রতিবেদন'}).first();
    await libraryStory.getByRole('button',{name:'Set as lead'}).click();
    await expect(editorPage.locator('.nb-lead-slot')).toContainText('ব্রাউজার পরীক্ষা: সম্পাদিত নমুনা প্রতিবেদন');
    await editorPage.getByRole('button',{name:'Save draft',exact:true}).click();
    await expect(editorPage.getByText('Draft saved',{exact:true})).toBeVisible();
    await editorPage.getByRole('button',{name:'Toggle draft preview'}).click();
    await expect(editorPage.frameLocator('iframe').locator('h1')).toContainText('ব্রাউজার পরীক্ষা: সম্পাদিত নমুনা প্রতিবেদন');
    editorPage.once('dialog',dialog=>dialog.accept());
    await editorPage.getByRole('button',{name:'Publish now'}).click();
    await expect(editorPage.getByText('Front page published',{exact:true})).toBeVisible();
    await editorPage.screenshot({path:'artifacts/newsroom-frontpage.png',fullPage:true});
    const publicPage=await context.newPage();await publicPage.goto('/bn');
    await expect(publicPage.locator('h1')).toContainText('ব্রাউজার পরীক্ষা: সম্পাদিত নমুনা প্রতিবেদন');
    await page.goto('/newsroom/frontpage-builder');await expect(page.getByText('403',{exact:true})).toBeVisible();
    expect(errors).toEqual([]);
    await context.close();
});

test('pointer drag selects a lead and undo restores the working copy',async({page})=>{
    const errors=[];page.on('pageerror',e=>errors.push(e.message));
    await login(page,'editor');await page.goto('/newsroom/frontpage-builder');
    const initialLead=await page.locator('.nb-lead-slot > [data-id]').getAttribute('data-id');
    const source=page.locator('[data-library] > [data-id="2"] .nb-handle');
    await source.scrollIntoViewIfNeeded();
    const sourceBox=await source.boundingBox();const targetBox=await page.locator('.nb-lead-slot').boundingBox();
    await page.mouse.move(sourceBox.x+sourceBox.width/2,sourceBox.y+sourceBox.height/2);
    await page.mouse.down();
    await page.mouse.move(targetBox.x+75,targetBox.y+60,{steps:30});
    await page.mouse.up();
    await expect(page.locator('.nb-lead-slot > [data-id]')).toHaveCount(1);
    await expect(page.locator('.nb-lead-slot > [data-id]')).toHaveAttribute('data-id','2');
    await expect(page.getByText('Unsaved changes',{exact:false})).toBeVisible();
    await page.getByRole('button',{name:'Undo',exact:true}).click();
    await expect(page.locator('.nb-lead-slot > [data-id]')).toHaveAttribute('data-id',initialLead);
    expect(errors).toEqual([]);
});
