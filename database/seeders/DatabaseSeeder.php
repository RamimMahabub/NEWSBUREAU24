<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Frontpage;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Never create default administrative credentials or demo articles in production.
        if (app()->isProduction()) {
            return;
        }
        $user = User::firstOrCreate(['email' => 'demo-reporter@newsbureau24.test'], [
            'name' => 'Demonstration newsroom', 'password' => Str::random(48),
        ]);
        $stories = [
            ['crime', 'lead', 'নারায়ণগঞ্জে অস্ত্র ও মাদক চক্রের সঙ্গে স্থানীয় প্রভাবশালীর যোগসাজশের নতুন তথ্য পেয়েছে তদন্ত সংস্থা', 'Inside an investigation: following the evidence in Narayanganj', 'একাধিক সূত্র ও নথিপত্র বিশ্লেষণে দেখা গেছে, স্থানীয় প্রভাবের আড়ালে কীভাবে গড়ে ওঠে অপরাধের জাল।', 'A sample investigation demonstrates how reporters connect documents, public records and verified sources.', 'narayanganj'],
            ['districts', 'river', 'শীতলক্ষ্যা দূষণ: প্রভাবশালীদের নাম উঠে এলো নথিতে', 'Shitalakshya: tracing the river pollution records', '', '', 'narayanganj'],
            ['crime', 'night', 'রাজধানীতে ছিনতাই চক্রের নতুন কৌশল, সিসিটিভি বিশ্লেষণে চাঞ্চল্যকর তথ্য', 'Following the footage: how a visual investigation takes shape', '', '', 'dhaka'],
            ['courts', 'court', 'আদালতের রায়ে প্রকাশ হলো বড় এক দুর্নীতি চক্রের কার্যপদ্ধতি', 'Reading the records: a guide to court reporting', '', '', 'dhaka'],
            ['special-reports', 'portraits', 'নিখোঁজদের তালিকায় নতুন নাম, তদন্ত শুরু করছে বিশেষ দল', 'Missing persons: the people behind the public records', '', '', 'dhaka'],
            ['investigations', 'investigation', 'একটি সেতু, কয়েকটি কোম্পানি, আর শত কোটি টাকার প্রশ্ন', 'One bridge, several companies, and a trail of unanswered questions', 'নথি, স্যাটেলাইট ছবি ও মাঠ পর্যায়ের তথ্য বিশ্লেষণের একটি নমুনা অনুসন্ধান।', 'A demonstration of document analysis, satellite imagery and on-the-ground reporting.', 'narayanganj'],
            ['investigations', 'youth', 'কিশোর গ্যাং: কীভাবে স্কুল-কলেজ ছাড়ায় অপরাধচক্র', 'Young people and crime: asking the difficult questions', '', '', 'dhaka'],
            ['investigations', 'cyber', 'অনলাইনে অপরাধের আড়ালে আন্তর্জাতিক চক্রের সহযোগী', 'Inside the methods of a digital investigation', '', '', 'dhaka'],
            ['investigations', 'river', 'শীতলক্ষ্যা নদী দখলের পেছনের ক্ষমতার নাম', 'Who controls the riverbank? Following the paper trail', '', '', 'narayanganj'],
            ['investigations', 'document', 'আদালতের নথিতে উঠে এলো রাজনৈতিক প্রভাব ও অর্থের লেনদেন', 'What public documents can tell us about accountability', '', '', 'dhaka'],
            ['video', 'video', 'সিসিটিভি ফুটেজে ধরা পড়লো ছিনতাই চক্রের পুরো ঘটনা', 'Frame by frame: the anatomy of a visual investigation', '', '', 'dhaka'],
            ['video', 'interview', 'নিখোঁজের পরিবার: আমরা এখনো কোনো উত্তর পাইনি', 'Listening to the families still waiting for answers', '', '', 'cumilla'],
            ['video', 'raid', 'মাদক চক্রের গোপন আস্তানায় আমাদের অভিযান', 'Behind the reporting: a documentary workspace', '', '', 'chattogram'],
            ['data-maps', 'map', 'মানচিত্রে বাংলাদেশ: তথ্য দিয়ে অনুসন্ধানের নতুন দিগন্ত', 'Mapping Bangladesh: a new perspective on public data', '', '', 'dhaka'],
            ['national', 'dhaka', 'নগরের বদলে যাওয়া জীবন: তথ্য ও মানুষের গল্প', 'A changing city, told through data and people', '', '', 'dhaka'],
            ['districts', 'chattogram', 'বন্দরনগরীর গল্প: স্থানীয় সাংবাদিকতার নতুন অধ্যায়', 'From the port city: the value of local reporting', '', '', 'chattogram'],
            ['districts', 'cumilla', 'কুমিল্লার পথে: জনস্বার্থের প্রশ্নে মাঠের প্রতিবেদন', 'On the ground in Cumilla: reporting in the public interest', '', '', 'cumilla'],
        ];
        foreach ($stories as $i => [$category, $image, $bn, $en, $summaryBn, $summaryEn, $district]) {
            Article::firstOrCreate(['slug' => 'demo-'.($i + 1)], [
                'user_id' => $user->id, 'category' => $category, 'district' => $district,
                'title' => ['bn' => $bn, 'en' => $en], 'summary' => ['bn' => $summaryBn, 'en' => $summaryEn],
                'body' => ['bn' => '<p>এটি নিউজ ব্যুরো ২৪-এর নকশা ও প্রকাশনা ব্যবস্থা দেখানোর জন্য তৈরি নমুনা প্রতিবেদন। এখানে উল্লেখিত কোনো ঘটনা, অভিযোগ বা ব্যক্তির তথ্য যাচাইকৃত সংবাদ হিসেবে গ্রহণ করবেন না।</p><h2>প্রমাণ ও সূত্র</h2><p>প্রকাশের আগে প্রতিবেদককে মূল নথি, সূত্রের পরিচয়, ঘটনার সময় ও সংশ্লিষ্ট পক্ষের বক্তব্য যাচাই করতে হবে। এই নমুনায় কোনো প্রকৃত সাক্ষ্য বা গোপন নথি যুক্ত করা হয়নি।</p>',
                    'en' => '<p>This is demonstration content for the NewsBureau24 design and publishing system. It is not verified reporting. Names, allegations and imagery shown in the visual reference must not be treated as factual news.</p><h2>Evidence and sources</h2><p>Before publication, reporters must verify original documents, source attribution, dates and responses from relevant parties. No actual evidence or confidential documents are included in this sample.</p>'],
                'demo_image' => $image, 'is_demo' => true, 'status' => 'published',
                'published_bn_at' => now()->subMinutes($i), 'published_en_at' => now()->subMinutes($i),
            ]);
        }
        if (! Frontpage::where('state', 'live')->exists()) {
            Frontpage::create(['state' => 'live', 'layout' => ['lead' => [1], 'top' => [2, 3, 4, 5]], 'version' => 1]);
        }
    }
}
