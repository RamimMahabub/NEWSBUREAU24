<?php
/** Local access setup only. No fixed or production demo credentials. */
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(!app()->environment('local')) { fwrite(STDERR,"Local setup requires APP_ENV=local.\n");exit(1); }
$path=base_path('.tools/local-access.json');
if(file_exists($path)) { echo "Local access already configured: .tools/local-access.txt\n";exit(0); }
$credentials=[];
foreach(['editor','reporter'] as $role) {
    $email=$role.'@newsbureau24.test';
    if(App\Models\User::where('email',$email)->exists()) { throw new RuntimeException('Account already exists; use newsroom:user to reset access.'); }
    $password=Illuminate\Support\Str::password(24);
    $user=new App\Models\User;
    $user->forceFill(['name'=>'Local '.ucfirst($role),'email'=>$email,'role'=>$role,'password'=>$password])->save();
    $credentials[$role]=['email'=>$email,'password'=>$password];
}
if(!is_dir(dirname($path))) { mkdir(dirname($path),0700,true); }
file_put_contents($path,json_encode($credentials,JSON_PRETTY_PRINT));
$text="NewsBureau24 LOCAL DEVELOPMENT ACCESS\nhttp://127.0.0.1:8000/newsroom\n\n";
foreach($credentials as $role=>$account) { $text.=ucfirst($role)."\nEmail: ".$account['email']."\nPassword: ".$account['password']."\n\n"; }
file_put_contents(base_path('.tools/local-access.txt'),$text);
echo "Local accounts created. Credentials: .tools/local-access.txt\n";
