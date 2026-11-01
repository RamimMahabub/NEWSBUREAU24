<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(!app()->environment('local')) { throw new RuntimeException('Browser fixture reset is local-only.'); }
$reporter=App\Models\User::firstWhere('email','reporter@newsbureau24.test');
$editor=App\Models\User::firstWhere('email','editor@newsbureau24.test');
if(!$editor || !$reporter) { exit(0); }
App\Models\Article::where('user_id',$reporter->id)->where('title->bn','like','ব্রাউজার পরীক্ষা:%')->get()->each(function($article){
    $article->status='archived';$article->is_demo=true;$article->save();
});
$stories=App\Models\Article::whereIn('slug',['demo-1','demo-2','demo-3','demo-4','demo-5'])->get()->keyBy('slug');
if($stories->count()===5) {
    $service=app(App\Services\FrontpageEditor::class);
    $draft=$service->draft($editor);
    $service->save(['lead'=>[$stories['demo-1']->id],'top'=>[$stories['demo-2']->id,$stories['demo-3']->id,$stories['demo-4']->id,$stories['demo-5']->id]],$draft->version,$editor,true);
}
echo "Browser fixtures archived; original demonstration homepage restored.\n";
